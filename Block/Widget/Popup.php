<?php
namespace Magebird\Popup\Block\Widget;
 
class Popup extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    protected function _construct()
    {
        parent::_construct();
    }   
    
    public function brightness($colourstr, $steps)
    {
      $colourstr = str_replace('#','',$colourstr);
      $rhex = substr($colourstr,0,2);
      $ghex = substr($colourstr,2,2);
      $bhex = substr($colourstr,4,2);
    
      $r = hexdec($rhex);
      $g = hexdec($ghex);
      $b = hexdec($bhex);
    
      $r = max(0,min(255,$r + $steps));
      $g = max(0,min(255,$g + $steps));  
      $b = max(0,min(255,$b + $steps));
    
      $decheyr = dechex($r);     
        
      if(strlen($decheyr)==1) $decheyr = "0".$decheyr;
    
      $decheyg = dechex($g);
      if(strlen($decheyg)==1) $decheyg = "0".$decheyg;
    
      $decheyb = dechex($b); 
      if(strlen($decheyb)==1) $decheyb = "0".$decheyb;
          
      return '#'.$decheyr.$decheyg.$decheyb;

    }     
    
    public function getButtonTextColor(){
      $buttonTextColor = $this->getData('button_text_color');
      if(!$buttonTextColor) $buttonTextColor = $this->getData('buttontext_color');
      if(!$buttonTextColor) $buttonTextColor = "#FFFFFF";
      if(strpos($buttonTextColor,'#') === false) $buttonTextColor = "#".$buttonTextColor;   
      return $buttonTextColor;  
    } 
    
    public function getButtonColor(){
      $buttonColor = $this->getData('button_color');
      if(!$buttonColor) $buttonColor = '#d83c3c';
      if(strpos($buttonColor,'#') === false) $buttonColor = "#".$buttonColor;     
      return $buttonColor;
    }
    
    public function getDelay(){
      $delay = 0;
      if($this->getData('on_success')==2){
        $delay = $this->getData('close_delay'); 
      }elseif($this->getData('on_success')==3){
        $delay = $this->getData('redirect_delay');
      }  
      return $delay;  
    }           

}