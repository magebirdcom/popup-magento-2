<?php
namespace Magebird\Popup\Model;

class Plugin{
	
	protected $_subscriberFactory;
	protected $_popupSubscriber;
	protected $_cpnHelper;
	protected $_customerSession;
	protected $_cart;
	protected $_msgs;
	
	public function __construct(
		\Magento\Newsletter\Model\Subscriber $subscriber,
		\Magebird\Popup\Model\Subscriber $popupSubscriber,
		\Magebird\Popup\Helper\Coupon $cpnHelper,
		\Magento\Checkout\Model\Cart $cart,
    	\Magento\Customer\Model\Session $customerSession,
    	\Magento\Framework\Message\ManagerInterface $messageManager
	){
		$this->_subscriberFactory = $subscriber;
		$this->_popupSubscriber = $popupSubscriber;
		$this->_cpnHelper = $cpnHelper;
		$this->_customerSession = $customerSession;
		$this->_cart = $cart;
		$this->_msgs = $messageManager;
	}
	
	public function afterExecute(\Magento\Newsletter\Controller\Subscriber\Confirm $subject, $result){
  
		$subscriber = $this->_subscriberFactory;
		$subscriber->load($subject->getRequest()->getParam("id"));
		
		//user confirmed - check for coupon
		if($subscriber->getStatus() == 1){		
			$email = $subscriber->getEmail();
			$coll = $this->_popupSubscriber->getCollection();
			$coll->addFieldToFilter('subscriber_email', $email);
			
			$popupSubscriber = $coll->getLastItem();
			$popupSubscriberData = $popupSubscriber->getData();
      $coupon = false;
      if(isset($popupSubscriberData["coupon_code"])){
        $coupon = $popupSubscriberData["coupon_code"];
      }      			
			if(!$coupon && isset($popupSubscriberData['rule_id']) && $popupSubscriberData['rule_id']){
				$coupon = $this->_cpnHelper->generateCoupon($popupSubscriberData);
			}
      
      if($coupon){
    			if($popupSubscriberData['apply_coupon']==1){
    				$this->_customerSession->setData("coupon_code",$coupon);         
    				$this->_cart->getQuote()->setCouponCode($coupon);
    				$this->_cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();  
    				$this->_cart->getQuote()->collectTotals()->save();                
    			}   
    			if($popupSubscriberData['send_coupon']==1){              
    				$this->_popupSubscriber->mailCoupon($email,$coupon);
    			}  
          
    			$this->_msgs->addSuccess('Your coupon code is: '.$coupon);
    			        
    			$this->_popupSubscriber->cleanOldEmails();
    			$this->_popupSubscriber->deleteTempSubscriber($email);
      }
		}
	} 
}