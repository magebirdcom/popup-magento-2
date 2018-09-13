<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
 
class Avgtime extends AbstractRenderer
{  
  public function _getValue(\Magento\Framework\DataObject $row)
  {
    $row = $row->getData();
    if($row['background_color']=="3" || $row['background_color']=="4"){
      return "<span class='popupTooltip' title='".__('Not available for popups with Background Overlay set to None.')."'>?</span>";
    }        
    if($row['views']==0){
      $seconds = '0 s';
    }else{
      $seconds = round(($row['total_time']/1000/$row['views']),1).' s';
    }       
    return $seconds;
  }  
}