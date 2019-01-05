<?php
namespace Magebird\Popup\Observer;

use Magento\Framework\Event\ObserverInterface;

class Applycoupon implements ObserverInterface
{
    protected $cart;
    protected $customerSession;   
    protected $catalogSession; 
    protected $_helper;
    
    public function __construct(  
        \Magento\Checkout\Model\Cart $cart,
        \Magebird\Popup\Helper\Data $helper
    ) {
        $this->cart = $cart;
        $this->helper = $helper;      
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {                           
       $couponCode = $this->_customerSession->getData("coupon_code");
                               
      if($couponCode){    
        $this->cart->getQuote()->setCouponCode($couponCode);
        $this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();  
        $this->cart->getQuote()->collectTotals()->save();         
      }               
    }
    
}