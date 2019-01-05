<?php
namespace Magebird\Popup\Observer;

use Magento\Framework\Event\ObserverInterface;

class Clearcache implements ObserverInterface
{
    protected $popup;
    
    public function __construct(  
        \Magebird\Popup\Model\Popup $popup
    ) {
        $this->popup = $popup;      
    }
 
    public function execute(\Magento\Framework\Event\Observer $observer)
    {                           
        $this->popup->parsePopupContent();   
        $this->popup->parsePopupContent(null,true);         
    }
    
}