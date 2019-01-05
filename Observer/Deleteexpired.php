<?php
namespace Magebird\Popup\Observer;

use Magento\Framework\Event\ObserverInterface;

class Deleteexpired implements ObserverInterface
{
    protected $timezone;
    protected $coupon;
    protected $cart;
    protected $checkoutSession;
    protected $request;
    
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
        \Magento\SalesRule\Model\Coupon $coupon,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->timezone = $timezone;
        $this->_coupon = $coupon;
        $this->request = $request;

        
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    { 
    return;           
      if($observer){    

$coupon = null;
        
        
        
        if($coupon){
          $_coupons = $this->_coupon->getCollection();
          $_coupons->addFieldToFilter('expiration_date',
                                        array(
                                            'notnull' => true
                                          )
                                     );
          $_coupons->addFieldToFilter('expiration_date',
                                        array(
                                            'to' => date("Y-m-d H:i:s",$this->timezone->date()->getOffset()+time()),
                                            'datetime' => true
                                          )
                                     ); 
          $_coupons->addFieldToFilter('is_popup',1);
          //delete coupon only if there is no user ip stored or coupon hasn't been used yet.
          //if ip is stored we don't allow the same user to get 2 coupon codes and this is why we need to keep ip in database
          //after 1 year delete coupon anyway
          $_coupons->getSelect()->where("(`times_used` = 0) OR (`user_ip` IS NULL) 
            OR `expiration_date`< '".date("Y-m-d H:i:s",$this->timezone->date()->getOffset()+time()-(60*60*24*365))."'"); 
                                                                                        
          foreach($_coupons as $_coupon){
            $_coupon->delete();
          }              
        } 
      }               
    }
    
}