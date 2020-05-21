<?php
namespace Magebird\Popup\Block\Widget;
 
class Newsletter extends \Magebird\Popup\Block\Widget\Popup{
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
    
	public function getScript($template){           
		$html = "<script type=\"text/javascript\">\n"; 
		$html.= "if(!jQuery(\"link[href*='/css/widget/newsletter/".$template.".css']\").length){\n";   
		$html.= "jQuery('head').append('<link rel=\"preload\" as=\"style\" href=\"".$this->getViewFileUrl('Magebird_Popup::css/widget/newsletter/'.$template.'.css')."\" type=\"text/css\" />');";
		$html.= "jQuery('head').append('<link rel=\"stylesheet\" href=\"http://dev.magento2b.com/pub/csstest.php\" type=\"text/css\" />');";
		$html.= "}\n";
		$html.= "newslPopup['".$this->getWidgetId()."'] = {};\n";      
      
		if($this->getData('success_msg') == null){
			//default luma magento 2 theme style success message if not defined by user
			$msg = urlencode('<div class="message-success success message">
				<div>'.__('Thank you for your subscription.').'</div>
				</div>');
		}else{
			$msg = $this->getData('success_msg');
		}
 
		$html.= "newslPopup['".$this->getWidgetId()."'].successMsg = decodeURIComponent(('".$msg."'+'').replace(/\+/g, '%20'));";
     
		$onSuccess = $this->getData('on_success') ? $this->getData('on_success') : 1;
		$html.= "newslPopup['".$this->getWidgetId()."'].successAction = '".$onSuccess."';";
		$html.= "newslPopup['".$this->getWidgetId()."'].successUrl = '".$this->getData('success_url')."';";
		$html.= "newslPopup['".$this->getWidgetId()."'].errorText = '".__('Write a valid Email address')."';";
    $html.= "newslPopup['" . $this->getWidgetId() . "'].checkboxErrorText = '" . __('Please check the checkbox') . "';";           
		$delay = $this->getDelay()*1000;
		$html.= "newslPopup['".$this->getWidgetId()."'].actionDelay = '".$delay."';";
		$html.= "</script>\n";       
		return $html;
	}   
    
	public function getAjaxUrl(){       
		$url = $this->actionUrlBuilder->getUrl(
			'magebird_popup/index/newsletter',
			$this->_storeManager->getStore()->getId(),
			$this->_storeManager->getStore()->getCode()          
		);
      
      	//remove sid - session id from url in order to load correct customer session on frontend
		list($file, $parameters) = explode('?', $url);
		parse_str($parameters, $output);
		unset($output['SID']);

		$url = $file . '?' . http_build_query($output);

		return $url;
	}
         
}