<?php
namespace Magebird\Popup\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\DataObject as Object;

class Updatesession implements ObserverInterface{
	protected $_request;
	protected $_layout;
	protected $_cache;
	protected $_popuphelper;
	protected $timezone;   
	protected $cart;
    
	public function __construct(
		\Magento\Framework\View\Element\Context $context,
		\Magebird\Popup\Helper\Data $popuphelper,
		\Magento\Customer\Model\Session $customerSession,  
		\Magento\Checkout\Model\Cart $cart        
	){
		$this->_layout = $context->getLayout();
		$this->_request = $context->getRequest();
		$this->_popuphelper = $popuphelper;
		$this->_customerSession = $customerSession;
		$this->timezone = $context->getLocaleDate();
		$this->cart = $cart;  
	}
 
	public function execute(\Magento\Framework\Event\Observer $observer){          
		$couponCode = $this->_customerSession->getData("coupon_code");
		if($couponCode){
			$this->cart->getQuote()->setCouponCode($couponCode)->save();
			$this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();  
			$this->cart->getQuote()->collectTotals()->save();         
		}
    
		if($observer->getData('event')->getName()!='customer_logout'){
			$productIds = $this->_popuphelper->getCartProductIds($observer);   
			$productIds = implode(",",$productIds);                   
			$cartSubtotal = $this->_popuphelper->getBaseSubtotal($observer);  
			$cartQty = $this->_popuphelper->getCartQtyQuote($observer); 
			              
		}elseif ($observer->getData('event')->getName() == 'customer_logout' ){
			$productIds = '';    
			$cartSubtotal = 0;
			$cartQty = 0;
		}	
			
 
		$cookies[] = array('cookieName'=>'cartSubtotal','value'=>$cartSubtotal,'expired'=>false);
		$cookies[] = array('cookieName'=>'cartProductIds','value'=>$productIds,'expired'=>false);
		$cookies[] = array('cookieName'=>'cartQty','value'=>$cartQty,'expired'=>false);
            
		$isSubscribed = false;     
		if($observer->getData('event')->getName()=='customer_logout'){
			$cookies[] = array('cookieName'=>'customerGroupId','value'=>0,'expired'=>false);     
			$cookies[] = array('cookieName'=>'loggedIn','value'=>0,'expired'=>false);  
			$isSubscribed = $this->_popuphelper->getPopupCookie('isSubscribed');
 		      	
		}elseif($this->_customerSession->isLoggedIn()){      
			$cookies[] = array('cookieName'=>'loggedIn','value'=>'1','expired'=>false);
			$value = $this->_customerSession->getCustomerGroupId();
			$cookies[] = array('cookieName'=>'customerGroupId','value'=>$value,'expired'=>false);      
			$email = $this->_customerSession->getCustomer()->getData('email');
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();        
			$subscriber = $objectManager->get('Magento\Newsletter\Model\Subscriber')->loadByEmail($email);   
                    
			if($subscriber->getId()){
				$isSubscribed = $subscriber->getData('subscriber_status') == \Magento\Newsletter\Model\Subscriber::STATUS_SUBSCRIBED;
			}                
		} else{
			$cookies[] = array('cookieName'=>'customerGroupId','value'=>0,'expired'=>false); 
			$cookies[] = array('cookieName'=>'loggedIn','value'=>0,'expired'=>false);    
		}  
		//we need this is show when is 8
		if($observer->getData('event')->getName()=='checkout_cart_save_after' && !$this->_popuphelper->getPopupCookie('cartAddedTime')){        
			$cookies[] = array('cookieName'=>'cartAddedTime','value'=>$this->timezone->date()->getOffset()+time(),'expired'=>time()+7200);
		} 
                
		$cookies[] = array('cookieName'=>'isSubscribed','value'=>$isSubscribed,'expired'=>false);
		//reset session because it is possible events will rename session id
		//we will update cookie with correct magentosessionid with next ajax call 
		$cookies[] = array('cookieName'=>'magentoSessionId','value'=>'','expired'=>false);
		
		$this->_popuphelper->setPopupMultiCookie($cookies);              
	}
    
}