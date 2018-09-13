<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;
 
class Image extends AbstractRenderer
{                                                                 
  public function _getValue(\Magento\Framework\DataObject $row){  
    $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $storeManager = $_objectManager->get('Magento\Store\Model\StoreManagerInterface'); 
    $currentStore = $storeManager->getStore();
    $mediaUrl = $currentStore->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);  
    return "<img class='templatePreview' style='margin:10px' src='".$mediaUrl.$row['preview_image']."' />";
  }  
}