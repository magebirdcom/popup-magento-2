<?php

namespace Magebird\Popup\Block;
use Magento\Framework\View\Element\Template;

class Script extends Template
{
	protected $_storeManager;	
	protected $request;
  protected $_popuphelper;
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,		
    \Magento\Framework\App\Request\Http $request,
    \Magebird\Popup\Helper\Data $_popuphelper,    		
		array $data = []
	){		
		$this->_storeManager = $context->getStoreManager();
    $this->_popuphelper = $_popuphelper;
    $this->request = $request;		   
		parent::__construct($context, $data);
	}
  
    public function getPreviewId()
    {
      $request = $this->getRequest();
      $module = $this->request->getModuleName();
      $action = $this->request->getActionName();           
      if($action!="preview" || $module!="magebird_popup") return '';   
      $popupId = $this->getRequest()->getParam('previewId');        
      return $popupId;
    }
    
    public function getPage(){        
      return $this->_popuphelper->getPage();      
    }    
    
    public function getPubDir(){
      return $this->_popuphelper->getPubDir();
    }
    
    public function getRootUrl()
    {
      $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
      
      return $url;    
      $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
      if(strpos($url, "pub/media/")!==false){
        return substr($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),0,-10);
      }else{
        return substr($this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA),0,-6);
      }       
    }    
    
    public function getTemplateId()
    {
      $request = $this->getRequest();
      $module = $this->request->getModuleName();
      $action = $this->request->getActionName();           
      if($action!="preview" || $module!="magebird_popup") return '';   
      $id = $this->getRequest()->getParam('templateId');        
      return $id;
    }
    public function getTargetPageId()
    {
      return $this->_popuphelper->getTargetPageId();
    }    
    
    public function getFilterId()
    {
      return $this->_popuphelper->getFilterId();
    }
    
    public function getStoreId()
    {                             
      return $this->_storeManager->getStore()->getId();
    }       
                         
}
