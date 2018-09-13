<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
 
class Percent extends AbstractRenderer
{  
  public function _getValue(\Magento\Framework\DataObject $row)
  {   
    $row = $row->getData();
    $index = $this->getColumn()->getIndex();
    if(($index == "window_closed" || $index == "page_reloaded")  && ($row['background_color']=="3" || $row['background_color']=="4")){
      return "<span class='popupTooltip' title='".__('Not available for popups with Background Overlay set to None.')."'>?</span>";
    }elseif(($index == "popup_closed" || $index == "click_inside") && ($row['background_color']=="3" || $row['background_color']=="4")){
      return "<span style='min-width:20px; display:inline-block;'>".$row[$this->getColumn()->getIndex()]."</span>";
    }    
    if($row['views']==0){
      $percent = '0 %';
    }else{
      $percent = round(($row[$this->getColumn()->getIndex()]/$row['views']*100),1).'%';
    }     
    return "<span style='min-width:20px; display:inline-block;'>".$row[$this->getColumn()->getIndex()]."</span> (".$percent.")";
  }  
}