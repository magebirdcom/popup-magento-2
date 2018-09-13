<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
 
class Sales extends AbstractRenderer
{  
  public function _getValue(\Magento\Framework\DataObject $row)
  {     
    $row = $row->getData();
    $notAvailable = false;
    if($row['show_when']!="1" && ($row['background_color']=="3" || $row['background_color']=="4")){
      $notAvailable = "<span class='popupTooltip' title='".__('Not available for popups with Background Overlay set to None and Show when other than "After page loads".')."'>?</span>";
    }     
        
    if($row['popupSalesCount']>0 && $row['views']>0){
      $conversionPopup = round($row['popupSalesCount']/$row['popupVisitors']*100,2)."%";
    }else{
      $conversionPopup = "/";
    }
  
    if($row['popupSalesCount']>0 && $row['popupCarts']>0){      
      $abondedPopup = round(($row['popupCarts']-$row['popupSalesCount'])/$row['popupCarts']*100,2)."%";
    }else{
      $abondedPopup = "/";
    }   
       
    $sales = $row['popupRevenue'] ? $row['currency'].$row['popupRevenue'] : "/";   
      
    $html = "<span style='font-size:11px;'>";      
    $html .= "<span style='min-width:71px;display:inline-block;'>Cpn Sales:</span> ".$sales."<br>";
    $html .= "<span style='min-width:71px;display:inline-block;'>Cpn Orders:</span> ".$row['couponSalesCount']."<br>";    
    if($notAvailable){
      $html .= "<span style='min-width:71px;display:inline-block;'>Conversion:</span> $notAvailable<br>";
      $html .= "<span style='min-width:71px;display:inline-block;'>Abonded cart:</span> $notAvailable<br>";    
    }else{
      $html .= "<span style='min-width:71px;display:inline-block;'>Conversion:</span> $conversionPopup<br>";
      $html .= "<span style='min-width:71px;display:inline-block;'>Abonded cart:</span> $abondedPopup<br>";
    }
    $html .= "</span>";
    return $html;
  }  
}