<?php
namespace Magebird\Popup\Block\Adminhtml\Renderer;
 
use Magento\Backend\Block\Context;
use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\Framework\Registry;
 
class Actionlink extends AbstractRenderer
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

    public function _getValue(\Magento\Framework\DataObject $row)
    {           
        $storeId = (int)$this->request->getParam('store');
        if ($storeId == 0) {            
            $stores = $this->storeManager->getStores(false);
            $storeId = current($stores)->getId();
            $code = current($stores)->getCode();
        }else{
          $store = $this->storeManager->getStore($storeId);
          $code = $store->getCode();
        } 
        
        $url=$this->getUrl('*/*/edit', array('id'=>$row->getId()));    
        $url2 = $this->storeManager->getStore($storeId)
          ->getUrl('magebird_popup/index/preview',
            array(
              '_current' => false,
              '_query' => 'previewId='.$row->getId()
            )
          );          

        $url2 = $this->actionUrlBuilder->getUrl(
            'magebird_popup/index/preview',
            $storeId,
            $code          
        );
        $url2 .= strpos($url2, "/?")===false ? "/?" : "&";
        $url2 .= 'previewId='.$row->getId();
          
          //var_dump($url2);        
        $url3=$this->getUrl('*/*/edit', array('duplicate_id'=>$row->getId()));
        $url4=$this->getUrl('*/*/mousetracking', array('id'=>$row->getId()));
                
        if($row->getData('background_color')!=3){
          if($this->_scopeConfig->getValue('magebird_popup/statistics/mousetracking')){
            return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a>", 
                            $url, __('Edit'), 
                            $url2, __('Preview'),
                            $url3, __('Duplicate'),
                            $url4, __('Mousetracking')
                            );      
          }else{
            return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a>", 
                            $url, __('Edit'), 
                            $url2, __('Preview'),
                            $url3, __('Duplicate')
                            );         
          }     
        }else{
          if($this->_scopeConfig->getValue('magebird_popup/statistics/mousetracking')){
            return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a> " . "Mousetracking <span class='popupTooltip' title='".__("Mousetracking is not available to fixed positioned popups. You can change position in popup editor inside Appearance & css->Overlay Background settings.")."'>(?)</span>", 
                          $url, __('Edit'), 
                          $url2, __('Preview'),
                          $url3, __('Duplicate')
                          );     
          }else{
            return sprintf("<style>.popupAction:hover{color:black;}</style><a class='popupAction' href='%s'>%s</a><br /><a class='popupAction' target='_blank' href='%s'>%s</a><br /><a class='popupAction' href='%s'>%s</a>", 
                          $url, __('Edit'), 
                          $url2, __('Preview'),
                          $url3, __('Duplicate')
                          );          
          }        
        }
    }
}