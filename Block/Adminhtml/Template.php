<?php
/**
 * Adminhtml popup list block
 *
 */
namespace Magebird\Popup\Block\Adminhtml;

class Template extends \Magento\Backend\Block\Widget\Grid\Container
{
    protected function _construct()
    {
        $this->_controller = 'adminhtml_template';
        $this->_blockGroup = 'Magebird_Popup';
        $this->_headerText = __('Popup Templates');
        parent::_construct();

    }
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    
    protected function brightness($colourstr, $steps) {
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
