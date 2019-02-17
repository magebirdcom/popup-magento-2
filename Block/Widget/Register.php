<?php
namespace Magebird\Popup\Block\Widget;
 
class Register extends \Magebird\Popup\Block\Widget\Popup{
	protected $_storeManager;    
	protected $actionUrlBuilder;
	public function __construct(
		\Magento\Backend\Block\Template\Context $context,        
		\Magento\Cms\Block\Adminhtml\Page\Grid\Renderer\Action\UrlBuilder $actionUrlBuilder,        
		array $data = []
	){        
		$this->_storeManager = $context->getStoreManager();    
		$this->actionUrlBuilder = $actionUrlBuilder;     
		parent::__construct($context, $data);
	}
  
    public function getRegisterUrl(){
    
  		$url = $this->actionUrlBuilder->getUrl(
  			'magebird_popup/index/register',
  			$this->_storeManager->getStore()->getId(),
  			$this->_storeManager->getStore()->getCode()          
  		);
    
        	//remove sid - session id from url
  		list($file, $parameters) = explode('?', $url);
  		parse_str($parameters, $output);
  		unset($output['SID']);
  
  		$url = $file . '?' . http_build_query($output);
  		return $url;

    }  
    
    public function getLoginUrl(){
  		$url = $this->actionUrlBuilder->getUrl(
  			'magebird_popup/index/login',
  			$this->_storeManager->getStore()->getId(),
  			$this->_storeManager->getStore()->getCode()          
  		);
   
        	//remove sid - session id from url
  		list($file, $parameters) = explode('?', $url);
  		parse_str($parameters, $output);
  		unset($output['SID']);
  
  		$url = $file . '?' . http_build_query($output);
  		return $url;
    }  
    
    public function getForgotUrl(){
  		$url = $this->actionUrlBuilder->getUrl(
  			'customer/account/forgotpassword',
  			$this->_storeManager->getStore()->getId(),
  			$this->_storeManager->getStore()->getCode()          
  		);
      
        	//remove sid - session id from url
  		list($file, $parameters) = explode('?', $url);
  		parse_str($parameters, $output);
  		unset($output['SID']);
  
  		$url = $file . '?' . http_build_query($output);
  		return $url;
    }        
         
}