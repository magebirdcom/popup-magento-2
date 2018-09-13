<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;
class Templateaction extends AbstractRenderer
{
    protected $registry;

    protected $attributeFactory;

    protected $request;
    
    protected $storeManager; 
    
    public function __construct(
        Registry $registry,
        AttributeFactory $attributeFactory,
        Context $context,        
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder $actionUrlBuilder,
        array $data = array()
    )
    {
        $this->attributeFactory = $attributeFactory;
        $this->registry = $registry;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->actionUrlBuilder = $actionUrlBuilder;
        parent::__construct($context, $data);
    }
 
    public function _getValue(\Magento\Framework\DataObject $row){
        $stores = $this->storeManager->getStores(false);
        $storeId = current($stores)->getId();   
        $code = current($stores)->getCode();
        $url1 = $this->actionUrlBuilder->getUrl(
            'magebird_popup/index/preview',
            $storeId,
            $code          
        );
        $url1 .= strpos($url1, "/?")===false ? "/?" : "&";
        $url1 .= 'templateId='.$row->getId();          

        $url2=$this->getUrl('*/*/edit', array('template_id'=>$row->getId()));
        return sprintf("<a class='popupAction' target='_blank' href='%s'>%s</a> <a class='popupAction' href='%s'>%s</a>", 
                        $url1, __('Preview'),
                        $url2, __('Copy & Edit'));  
    }
}