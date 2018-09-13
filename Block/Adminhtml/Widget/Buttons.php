<?php
namespace Magebird\Popup\Block\Adminhtml\Widget;
class Buttons extends \Magento\Framework\View\Element\Template
{  
    
    public function brightness($colourstr, $steps) {
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
                
}