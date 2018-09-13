<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;
 
class Title extends AbstractRenderer
{
  public function _getValue(\Magento\Framework\DataObject $row){    
    return $row['title']."<br>".$row['description']; 
  }  
}