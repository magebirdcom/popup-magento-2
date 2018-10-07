<?php

namespace Magebird\Popup\Controller\Coupon;

class Newcoupon extends \Magento\Framework\App\Action\Action
{
    protected $request;
    
    protected $_popup;
    
    protected $_helper;         
    
    protected $resultRawFactory;
    
    protected $cart;        
    
    protected $customerSession;
        
    protected $_cpnHelper;  
    
    
    public function __construct(
      \Magento\Framework\App\Action\Context $context,
      \Magento\Framework\App\Request\Http $request,
      \Magebird\Popup\Model\Popup $popup,
      \Magebird\Popup\Helper\Data $helper,
      \Magebird\Popup\Helper\Coupon $cpnHelper,
      \Magento\Framework\App\Config\ScopeConfigInterface $config,
      \Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
      \Magento\Checkout\Model\Cart $cart,
      \Magento\Checkout\Model\Session $session,
      \Magento\SalesRule\Model\Rule $salesrule,
      \Magento\Customer\Model\Session $customerSession      
    )
    {
        parent::__construct($context);
        $this->request = $request;
        $this->_popup = $popup;
        $this->_helper = $helper;
        $this->_cpnHelper = $cpnHelper;
        $this->resultRawFactory = $resultRawFactory;
        $this->cart = $cart;
        $this->checkoutSession = $session;
        $this->salesrule = $salesrule; 
        $this->customerSession = $customerSession;                
    }
	
    public function execute()
    {
        $coupon = '';        
        $_popup = $this->_popup->load($this->request->getParam('popupId'));
        $widgetValues = $this->_helper->getWidgetData($_popup->getPopupContent(),$this->request->getParam('widgetId'));
        if(isset($widgetValues['coupon_code']) && $widgetValues['coupon_code']){
          $coupon = $widgetValues['coupon_code'];
        }elseif(isset($widgetValues['rule_id'])){                   
          $rule = $this->salesrule->load($widgetValues['rule_id']);
          if($rule->getData('rule_id')){
            $data = $widgetValues;
            $data['cpnExpInherit'] = $this->request->getParam('cpnExpInherit');
            $coupon = $this->_cpnHelper->generateCoupon($data);
          }                                 
        }
        if($coupon && isset($widgetValues['apply_coupon']) && $widgetValues['apply_coupon']==1){
          $this->customerSession->setData("coupon_code",$coupon);
          $this->cart->getQuote()->setCouponCode($coupon)->save();  
          $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();  
          $this->cart->getQuote()->collectTotals()->save();                    
        } 
        
        $_popup->setGoalComplition($_popup->getData('goal_complition')+1);
        $_popup->save(); 
                  
        $response = json_encode(array('success' => 'success', 'coupon' => $coupon));        
        
        $resultRaw = $this->resultRawFactory->create();
        return $resultRaw->setContents($response);                 
    }
}
