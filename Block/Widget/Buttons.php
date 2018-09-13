<?php
namespace Magebird\Popup\Block\Widget;
 
class Buttons extends \Magebird\Popup\Block\Widget\Popup{
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

	public function getAjaxUrl(){ 
                                           
		$url = $this->actionUrlBuilder->getUrl(
			'magebird_popup/coupon/newcoupon',
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
    
	public function getColor(){
		$color = $this->getData('button_color');
		if(strpos($color,'#') === false) $color = "#".$color;
		return $color;
	} 
    
	public function buttonSize(){
		$size = $this->getData('button_size');
		if($size=="big" || $size=="normall") $size=5;
		if($size=="small" || $size=="middle") $size=3;
		if($size=="tiny") $size=2;
		if(!$size) $size = 4;
		return $size;
	}         

	public function getJsHtml(){
		$html = "<script type=\"text/javascript\">"; 
		$html.= "popupButton['".$this->getWidgetId()."'] = {};\n";
		$html.= "popupButton['".$this->getWidgetId()."'].successMsg = decodeURIComponent(('".__($this->getData('success_msg'))."'+'').replace(/\+/g, '%20'));";   
		$html.= "popupButton['".$this->getWidgetId()."'].successAction = '".$this->getData('on_click')."'\n";
		$html.= "popupButton['".$this->getWidgetId()."'].couponType = '".$this->getData('coupon_type')."'\n";
		$html.= "</script>\n"; 
		return $html; 
	}    
         
}