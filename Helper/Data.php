<?php

/**
* Popup data helper
*/
namespace Magebird\Popup\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class Data extends \Magento\Framework\App\Helper\AbstractHelper{
	protected $popupCookie;
	protected $_product = null;
	protected $_request;
	protected $_storeManager;
	protected $_scopeConfig; 
	protected $_registry;
    
	public function __construct(
		\Magento\Framework\App\Helper\Context $context,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Registry $registry
	){
		$this->popupCookie = false; 
		if(!$this->getPopupCookie('magentoSessionId')){
			$this->setPopupCookie('magentoSessionId',session_id());
		}     
		$this->_request = $request;
		$this->_registry = $registry;
		$this->_storeManager = $storeManager; 
		$this->_scopeConfig = $context->getScopeConfig(); 
		parent::__construct($context);
	}
    
	public function getIsCrawler(){      
		//if($_SERVER['REMOTE_ADDR']=='127.0.0.1') return true;
		$userAgent = $_SERVER['HTTP_USER_AGENT'];    
		$crawlers = 'robot|spider|crawler|curl|Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';
		$isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);
		return $isCrawler;
	}	
      
	public function getTargetPageId(){
		//if popup loaded with ajax
		if($this->_request->getParam('popup_page_id')) return $this->_request->getParam('popup_page_id');      
		$module = $this->_request->getModuleName();
		$controller = $this->_request->getControllerName();
		$action = $this->_request->getActionName();
      
		if($action=="template" || $action=="preview") return '';
		$filterId = '';        
		if($this->_request->getFullActionName() == 'cms_index_index'){
			$targetPageId = 1;   
		}elseif($this->_request->getFullActionName() == 'catalog_product_view'){
			$targetPageId = 2;
		}elseif($this->_request->getFullActionName() == 'catalog_category_view'){
			$targetPageId = 3;
		}elseif($module == 'checkout' && $controller == 'cart' && $action == 'index'){
			$targetPageId = 5;
		}elseif($module == 'onestepcheckout' || ($module == 'checkout' && $controller == 'index' && $action == 'index')){
			$targetPageId = 4;
		} else{        
			$targetPageId = 7;      
		}     
		return $targetPageId;
	}
    
	public function getFilterId(){
		if($this->_request->getParam('filterId')) return $this->_request->getParam('filterId');
		$filterId = null;
		if($this->_request->getFullActionName() == 'catalog_product_view'){
			$filterId = $this->_registry->registry('current_product')->getId();
		}elseif($this->_request->getFullActionName() == 'catalog_category_view'){
			$filterId = $this->_registry->registry('current_category')->getId();
		}  
		return $filterId; 
	}
    
	public function getRandString(){
		return substr(str_shuffle("abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ"), 0, 7);
	}
    
	public function getTrialStart(){
		return $this->_scopeConfig->getValue('magebird_popup/general/trial_start'); 
	} 

	public function getPubDir(){
		if($this->_scopeConfig->getValue('magebird_popup/general/pub_dir')!==null){
      return $this->_scopeConfig->getValue('magebird_popup/general/pub_dir');
    }else{
      return 'pub/';
    } 
	}     
    
	public function showPopup(){
		$extensionKey = $this->_scopeConfig->getValue('magebird_popup/general/extension_key');
		$trialStart = $this->_scopeConfig->getValue('magebird_popup/general/trial_start');
		if(empty($extensionKey) && ($trialStart<strtotime('-7 days'))){
			return false;
		}     
		return true;  
	}
    
	public function getWidgetData($content,$widgetId){
		$widgetId = 'widget_id="'.$widgetId.'"';
		$widgetArray = explode($widgetId,$content);
		$widget = end($widgetArray);
		$widget = explode('}}',$widget);
		$attribs = $widget[0];
		$pattern = '/(\\w+)\s*=\\s*("[^"]*"|\'[^\']*\'|[^"\'\\s>]*)/';
		preg_match_all($pattern, $attribs, $matches, PREG_SET_ORDER);
		$attrs = array();
		foreach($matches as $match){
			if(($match[2][0] == '"' || $match[2][0] == "'") && $match[2][0] == $match[2][strlen($match[2])-1]){
				$match[2] = substr($match[2], 1, -1);
			}
			$name = strtolower($match[1]);
			$value = html_entity_decode($match[2]);
			$attrs[$name] = $value;
		}
		return $attrs;
	}  
    
	//If we don't need also expire part then we dont return expired cookies
	function getPopupCookie($param, $alsoExpirePart=false){ 
		if($this->popupCookie){
			$cookie = $this->popupCookie;
		}else{
			$cookie = isset($_COOKIE['popupData']) ? $_COOKIE['popupData'] : '';
			$this->popupCookie = $cookie;
		}            
		$param = explode($param.":",$cookie);
		if(!isset($param[1])){
			//fix for cookies from old version because they werent stored inside popupData
			if($param == 'lastSession' && isset($_COOKIE['lastPopupSession'])){
				$value = $_COOKIE['lastPopupSession'];
			}elseif($param == 'lastRandId' && isset($_COOKIE['lastRandId'])){
				$value = $_COOKIE['lastRandId'];
			}else{
				return false;
			}
		}else{
			$value = explode("|",$param[1]);
			$value = $value[0];
		}  
		if(!$alsoExpirePart){
			$value = explode("=",$value);      
			if(isset($value[1])){
				$expire = $value[1]; 
				if($expire<time()) return false;
			}                   
			$value = $value[0];
		} 
		return $value;  
	}   
    
	public function setPopupMultiCookie($cookies){
		foreach($cookies as $cookie){
			if($cookie['expired']){
				$cookie['value'] .= "=".$cookie['expired'];
			}        
            
			if($this->popupCookie){
				$_cookie = $this->popupCookie;
				$oldVal = $this->getPopupCookie($cookie['cookieName'], true); 
				if(strpos($_cookie,$cookie['cookieName'])!==false){
					$_cookie = str_replace($cookie['cookieName'].":".$oldVal, $cookie['cookieName'].":".$cookie['value'], $_cookie);      
				}else{
					$_cookie .= "|".$cookie['cookieName'].":".$cookie['value'];
				}                 
			}else{
				$_cookie = $cookie['cookieName'].":".$cookie['value'];            
			}            
			$this->popupCookie = $_cookie;    
		}
		
		setcookie('popupData', $this->popupCookie, time() + (3600*24*365), '/');
	}
    
	public function setPopupCookie($cookieName,$value, $expired=false){
		if($expired){
			$value .= "=".$expired;
		}        
        
		if($this->popupCookie){
			$cookie = $this->popupCookie;
			$oldVal = $this->getPopupCookie($cookieName, true); 
			if(strpos($cookie,$cookieName)!==false){
				$cookie = str_replace($cookieName.":".$oldVal, $cookieName.":".$value, $cookie);      
			}else{
				$cookie .= "|".$cookieName.":".$value;
			}                 
		}else{
			$cookie = $cookieName.":".$value;            
		}  
		setcookie('popupData', $cookie, time() + (3600*24*365), '/');
      
		$this->popupCookie = $cookie;        
	} 
    
	public function getProduct($productId=null){            
		//if user want to show product information outside product page, he needs to append popupProductId into url
		if($this->_request->getParam('url') && strpos($this->_request->getParam('url'), 'popupProductId')!==false){
			$url = $this->_request->getParam('url');
			$query_str = parse_url($url, PHP_URL_QUERY);
			parse_str($query_str, $query_params);
			$productId = $query_params['popupProductId'];
		}elseif(!$productId){
			if($this->getTargetPageId()==2){
				$productId = $this->getFilterId();
			}
		} 
		if(!$productId) return false;
		if(isset($this->_product[$productId])) return $this->_product[$productId];
        
		$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
		$this->_product[$productId] = $objectManager->get('Magento\Catalog\Model\Product')->load($productId);

		return $this->_product[$productId];
	} 
    
	function getCartProductIds($observer){
		if(is_object($observer->getData("customer"))){
			$cust = $observer->getData("customer");
			
			$om =   \Magento\Framework\App\ObjectManager::getInstance();
			$_cart = $om->create('Magento\Quote\Model\Quote'); 
			$_cart->loadByCustomer($cust->getId());
			
		}else{
			$om =   \Magento\Framework\App\ObjectManager::getInstance();
			$_cart = $om->create('Magento\Checkout\Model\Cart')->getQuote(); 
		}
		$productIds = array();                      
		$count = 0;                    
		foreach($_cart->getAllItems() as $item){             
			if($count>20) break;
			$productIds[] = $item->getProduct()->getId();
			$count++;
		} 
		return $productIds;     
	}
    
	function getPage(){
		$id = $this->_scopeConfig->getValue('magebird_popup/gen'.'eral/exte'.'nsion_k'.'ey');
		$time = $this->getTrialStart();
      
		if((empty($id) || strlen($id)!=10) && ($time<strtotime('-7 days') || $time>strtotime('+35 days'))){
			return '0';
		}     
		return '1';      
	}
    
	function getCartQtyQuote($observer){
		if(is_object($observer->getData("customer"))){
			
			$cust = $observer->getData("customer");
			
			$om =   \Magento\Framework\App\ObjectManager::getInstance();
			$_cart = $om->create('Magento\Quote\Model\Quote'); 
			$_cart->loadByCustomer($cust->getId());
			
			return (int)$_cart->getItemsQty();
		}else{
			$objectManager = \Magento\Framework\App\ObjectManager::getInstance();
			$cart = $objectManager->get('\Magento\Checkout\Model\Session');
			return (int)$cart->getQuote()->getItemsQty();
		}
	}
    
	function getBaseSubtotal($observer){
		if(is_object($observer->getData("customer"))){
			$cust = $observer->getData("customer");
			
			$om =   \Magento\Framework\App\ObjectManager::getInstance();
			$quote = $om->create('Magento\Quote\Model\Quote'); 
			$quote->loadByCustomer($cust->getId());
			
		}else{
			$om =   \Magento\Framework\App\ObjectManager::getInstance();
			$_cart = $om->create('Magento\Checkout\Model\Cart');           
			$quote =$_cart->getQuote();
		}
			
		if($this->_scopeConfig->getValue('tax/cart_display/subtotal')==2){     
			$totals = $quote->getTotals();
			$rate = $quote->getData('base_to_quote_rate');
			if(!$rate || $rate==0) $rate=1;
			$subtotalIncTax = $totals["subtotal"]->getValue()/$rate;        
			return round($subtotalIncTax,2);
		}else{
			return $quote->getBaseSubtotal();
		}    
		   
	}    

  function checkNetworkError(){
    //direct query to prevent cache
    $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
    $_resource = $objectManager->get('\Magento\Framework\App\ResourceConnection');
    $tableName = $_resource->getTableName('core_config_data');
    $query = "SELECT value FROM $tableName WHERE path='magebird_popup/general/network_error';";
    $connection = $_resource->getConnection();
    $results = $connection->fetchOne($query);
    if($results){
      return $results;
		}else{
      return 0;
    }  
  }   
      
}
