<?php
namespace Magebird\Popup\Block\Widget; 
class Timer extends \Magebird\Popup\Block\Widget\Popup{
  protected $popup;
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,
    \Magebird\Popup\Model\Popup $popup,             
		array $data = []
	){    
    $this->_popup = $popup;    
		parent::__construct($context, $data);
	}


    public function getCookieInheritId(){
        $_popup     = $this->_popup->load($this->getPopupInheritId());        
        //$widgetData = Mage::helper('magebird_popup')->getWidgetData($_popup->getPopupContent(),$this->getRequest()->getParam('widgetId'));
        return $_popup->getCookieId();    
    }
    
    public function getTimerType(){
      if($this->getPopupInheritId()){                
          $widgetData = $this->getWidgetData($this->getPopupInheritId());
          if(isset($widgetData['to_date'])){          
            return 'static';
          }else{
            return 'dynamic';
          }              
      }else{
          if($this->getToDate()){
            return 'static';
          }else{
            return 'dynamic';
          }
      }    
    
    }
    public function getWidgetData($popupId){
      $_popup     = $this->_popup->load($popupId);
      $content = $_popup->getPopupContent();
      $widget = explode('widget_id="',$content);
      $widget = explode('"',$widget[1]);
      $widgetId = $widget[0];                 
      $widgetData = Mage::helper('magebird_popup')->getWidgetData($content,$widgetId);          
      return $widgetData;    
    }
    
    public function getTimer(){
      if($this->getPopupInheritId()){                
          $widgetData = $this->getWidgetData($this->getPopupInheritId());          
          if(isset($widgetData['to_date'])){          
            $popupTimer = strtotime($widgetData['to_date']);
          }else{
            $popupTimer = $widgetData['minutes']*60;
          }          
      }else{
        if($this->getToDate()){
          //$popupTimer = (strtotime($this->getToDate())-Mage::getModel('core/date')->timestamp(time()));
          $popupTimer = strtotime($this->getToDate());
        }else{
          $popupTimer = $this->getData('minutes')*60;
        }        
      }
      //var_dump($popupTimer); exit;
      return $popupTimer;  
    }  
    
    public function getFontSize(){
      return $this->getTimerSize();
    }   
    
    public function getLabelFontSize(){
      $fontSize = intval($this->getTimerSize()/2);
      if($fontSize<10) $fontSize=10;
      return $fontSize;      
    }        
  
         
}