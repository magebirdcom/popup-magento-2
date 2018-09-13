<?php
class popup_model extends customizer{

	var $pdo;
	var $dbPrefix;
	var $products = array();
	var $sessionId = null;
  
	public function __construct($helper){
		$this->sessionId = isset($_COOKIE["PHPSESSID"]) ? $_COOKIE["PHPSESSID"] : '';
    
		$file           = realpath(__DIR__) . '/../../../../app/etc/env.php';
		$file = str_replace("/pub/app/etc/env", "/app/etc/env",$file);
		$config         = file_get_contents($file);
    $config = str_replace("<?php", "",$config);
    $config = str_replace("return array", "array",$config);
    $config = str_replace("return [", "[", $config);    
    if(strpos($config, "table_prefix")===false) exit('Missing database data');
    eval('$array = ' .$config);
    $prefix = $array['db']['table_prefix'];
    $host = $array['db']['connection']['default']['host'];
    $password = $array['db']['connection']['default']['password'];
    $username = $array['db']['connection']['default']['username'];
    $dbname = $array['db']['connection']['default']['dbname'];

		if(strpos($host,".sock")!==false){
			$sock = $host;      
		}     
		$hostData       = parse_url($host);
		$host = isset($hostData['host']) ? $hostData['host'] : '';
		if(empty($host)) $host = $hostData['path'];
		$port = isset($hostData['port']) ? "port=".$hostData['port'].";" : '';    
		try{
			if(isset($sock)){
				$this->pdo = @new PDO("mysql:dbname=$dbname;charset=utf8;unix_socket=$sock","$username","$password");
			}else{
				$this->pdo = @new PDO("mysql:host=".$host.";".$port."dbname=$dbname;charset=utf8","$username","$password");
			}     
      //$this->pdo->setAttribute( PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING );
      $this->pdo->query("SET SESSION sql_mode = ''");   
		} catch(Exception $e){
			if(isset($sock)) exit('Can not connect to database using sock connection');
			exit('Can not connect to database');
		}        
		$this->dbPrefix = $prefix;
		$this->helper   = $helper;
		$this->storeId  = intval($this->helper->getParam('storeId'));  
		$this->product  = new product_model();
		$this->setTimezone();       
	}
  
	public function getPopup($popupId){  
		$popupId = intval($popupId);
		$sql = "SELECT `main_table`.*,`content`.content AS parsed_content 
		FROM `".$this->getTable('magebird_popup')."` AS `main_table`  
		LEFT JOIN ".$this->getTable('magebird_popup_content')." as `content` 
		ON `content`.popup_id=main_table.popup_id 
		AND is_template=0
		AND content.store_id=".$this->storeId."
		WHERE main_table.popup_id=$popupId";            
		$results = $this->pdo->query($sql);
		$data = $results->fetchAll(PDO::FETCH_ASSOC); 
		return $data;
	}
  
	public function getPopupTemplate($popupId){
		$popupId = intval($popupId);
		$sql = "SELECT `main_table`.*,`content`.content AS parsed_content
		FROM `".$this->getTable('magebird_popup_template')."` AS `main_table` 
		LEFT JOIN ".$this->getTable('magebird_popup_content')." as `content` 
		ON `content`.popup_id=main_table.template_id AND is_template=1 
		AND store_id=".$this->storeId."
		WHERE template_id=$popupId";
		$results = $this->pdo->query($sql);
		$data = $results->fetchAll(PDO::FETCH_ASSOC);    
		return $data;
	}   
  
	public function getCurrentProduct(){    
		$product = null;    
		if($this->helper->getParam('popup_page_id')==2){
			$productId = $this->helper->getParam('filterId');
			$product = $this->product->getProduct($productId,$this->storeId,$this->pdo,$this->dbPrefix,$this->helper); 
		}  
		return $product;    
	} 
  
	public function getCartProduct(){
		$product = null;

		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){    
			$ids = $this->helper->getPopupCookie('cartProductIds');
			$ids = explode(",",$ids);  
			if($productId = $ids[0]){
				$product = $this->product->getProduct($productId,$this->storeId,$this->pdo,$this->dbPrefix,$this->helper);
			}         
		}         
		return $product;    
	}   
  
	public function getCurrentProductCat(){    
		$productCats = null;    
		if($this->helper->getParam('popup_page_id')==2){  
			$productId = $this->helper->getParam('filterId');
			$productCats = $this->product->getProductCategories($productId,$this->storeId,$this->pdo,$this->dbPrefix); 
		}  
		return $productCats;    
	}   
  
	public function getCurrentCartProductCat(){    
		$productCats = null;
		$ids = null;    
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			$ids = $this->helper->getPopupCookie('cartProductIds');
		}         
		if(!$ids) return null;
    
		$ids = explode(",",$ids); 
		$cats = array();     
		foreach($ids as $productId){
			$productCats = $this->product->getProductCategories($productId,$this->storeId,$this->pdo,$this->dbPrefix);
			$cats = array_merge($cats,$productCats);
		}       
		return $cats;    
	}  
  
	public function getTable($table){
		return $this->dbPrefix.$table;
	}
  
	public function setPopupData($id,$field,$value){
		$id = intval($id);
		$tableName = $this->getTable('magebird_popup');    
		$query = "UPDATE `{$tableName}` SET `$field`=:value WHERE popup_id=$id";
		$statement = $this->pdo->prepare($query); 
		$statement->bindParam(':value', $value, PDO::PARAM_STR);        
		$statement->execute();                                      
	}    

	public function getPopups(){                          
		//sometimes Magento doesn't complete installation, in this case this code is needed  
		$table = $this->checkTableStatus('magebird_popup');
		if(!$table){        
			return array();
		}
          
		$detect = new Mobile_Detect3;
		$deviceType = ($detect->isMobile() ? ($detect->isTablet() ? 'tablet' : 'mobile') : 'desktop');         
		//var_dump($this->helper->getPopupCookie('lastPageviewId'));        
		if($lastPageviewId = $this->helper->getPopupCookie('lastPageviewId')){     
			$this->helper->setPopupCookie('lastPageviewId',''); //remove cookie                         
			$this->checkIfPageRefreshed($lastPageviewId);
			//var_dump($lastPageviewId); exit;
		}                                                       

		if(!isset($_SESSION['numVisitedPages'])) $_SESSION['numVisitedPages'] = 0;
		$numVisitedPages = intval($_SESSION['numVisitedPages'])+1;
				
		$_SESSION['numVisitedPages'] = $numVisitedPages;

		$deniedIds    = $this->getDeniedIds();                 
		$pageId       = $this->helper->getParam('popup_page_id');
		$filterId     = $this->helper->getParam('filterId');  
		$loggedIn     = $this->helper->isLoggedIn();
		$reader       = new Reader2(dirname ( __FILE__ ).'/MaxMind/GeoLite2-Country.mmdb' );
		$ipData       = $reader->get($this->helper->getIp());       
		$productId    = null;             
		$sql          = "SELECT `main_table`.*,
		`content`.content AS parsed_content,
		GROUP_CONCAT(page_id) as page_ids,
		GROUP_CONCAT(products.product_id) as product_ids,
		GROUP_CONCAT(categories.category_id) as category_ids
		FROM `".$this->getTable('magebird_popup')."` AS `main_table`";
		$this->join[] = "LEFT JOIN ".$this->getTable('magebird_popup_content')." as `content` 
		ON `content`.popup_id=main_table.popup_id AND is_template=0 AND content.store_id=".$this->storeId.""; 
		switch($deviceType){
			case 'tablet':
			$this->where[] = "(devices IN(1,4,5,6))"; 
			break;
			case 'mobile':
			$this->where[] = "(devices IN(1,3,5,7))";
			break;
			default:
			$this->where[] = "(devices IN(6,7,2,1))";                                                
		}   
		if(!$this->checkLicence()){
			$this->where[] = "(status = 4)";
		}else{
			$this->where[] = "(status=1)";
		}                           

		$firstPopupSession = $this->helper->getPopupCookie('lastSession');    
		if(!$firstPopupSession){   
			$this->helper->setPopupCookie('lastSession',$this->sessionId);
		}     
		if($firstPopupSession && $firstPopupSession!=$this->sessionId){
			//returning visitor
			$this->where[] = "(if_returning IN(1,2))";
		}else{
			//new visitor
			$this->where[] = "(if_returning IN(1,3))";
		}     
		//0 če je pustil prazno
		$this->where[] = "(num_visited_pages = 0 OR num_visited_pages<=".intval($numVisitedPages).")";    
		$this->where[] = "(main_table.user_ip = :remoteAddr OR main_table.user_ip='')";
		$this->binds[':remoteAddr'] = $_SERVER['REMOTE_ADDR'];
		$this->where[] = "(product_in_cart = ".$this->anyProductInCart()." OR product_in_cart=0)";
		$this->where[] = "(cart_subtotal_min > ".$this->getSubtotal()." OR cart_subtotal_min=0)";
		$this->where[] = "(cart_subtotal_max < ".$this->getSubtotal()." OR cart_subtotal_max=0)";     
    
		$this->where[] = "(cart_qty_min > ".$this->getCartQty()." OR cart_qty_min=0)";
		$this->where[] = "(cart_qty_max < ".$this->getCartQty()." OR cart_qty_max=0)"; 
 
		$this->addStoreFilter($this->helper->getParam('storeId'));
		$this->addNowFilter();
		$this->addCookieFilter($deniedIds);  
		$this->addIpFilter();   
		$this->addIfRefferalFilter();
		$this->addDaysFilter();
		$this->addCustomerGroupsFilter();
		if(isset($ipData['country'])){
			$this->addCountryFilter($ipData['country']['iso_code']);
			$this->addNotCountryFilter($ipData['country']['iso_code']);       
		}         
		if($pageId==2){
			$productId = $filterId;
		}   
		$this->addProductFilter($productId);
		$this->addCategoryFilter($filterId);
    $this->hoursFilter();
  
		if($pageId){
			$this->addPageFilter($pageId);
		}       
                  
		if($loggedIn){
			$this->where[] = "(user_login IN(1,2))";
		}else{
			$this->where[] = "(user_login IN(1,3))";
		} 
       
		if($this->helper->getPopupCookie('isSubscribed') != false){
			$this->where[] = "(user_not_subscribed = 2)";	
		}

		if($this->helper->getParam('cEnabled')=="false"){
			$this->where[] = "(cookies_enabled IN(1,3))";
		}else{
			$this->where[] = "(cookies_enabled IN(1,2))";
		}       
		$sql .= " ".implode("\n ",$this->join);
		$sql .= " WHERE ".implode("\n AND ",$this->where);                                      
		$sql .= "\nGROUP BY `main_table`.`popup_id` 
		\nORDER BY `priority` ASC, `stop_further` ASC, RAND()";
		//echo $sql; exit;       
		$statement = $this->pdo->prepare($sql);
	         
		$statement->execute($this->binds);  
		$collection = $statement->fetchAll(PDO::FETCH_ASSOC);

		$stopFurther = false;  
		$pendingOrder = false;
		$checkPendingOrderChecked = false;
    
		if(!isset($_SESSION['loadedPopups'])) $_SESSION['loadedPopups'] = array();  
		foreach($collection as $key => $popup){ 
    
      if(isset($_SESSION['loadedPopups'])){
        $popupId = array_search($popup['cookie_id'], $_SESSION['loadedPopups'], true);
        if($popupId && intval($popupId)!=intval($popup['popup_id'])){
          unset($collection[$key]);
          continue;        
        }
      }               
      if($stopFurther == true){
        unset($collection[$key]);
        continue;
      }  
                                
			$pageIds = explode(",",$popup['page_ids']);
			$productIds = explode(",",$popup['product_ids']);
			$categoryIds = explode(",",$popup['category_ids']);
			//if user selected more 'Show at' options, uset collection only if none of option matches 
			if(!$this->specifiedUrlFilter($popup['specified_url']) 
				&& !($pageId==1 && in_array(1, $pageIds))
				&& !($pageId==4 && in_array(4, $pageIds))
				&& !($pageId==5 && in_array(5, $pageIds))
				&& !($pageId==7 && in_array(7, $pageIds))
				&& !($pageId==2 && in_array(2, $pageIds) && (in_array(0, $productIds) || in_array($filterId, $productIds)))
				&& !($pageId==3 && in_array(3, $pageIds) && (in_array(0, $categoryIds) || in_array($filterId, $categoryIds)))
			){ 
				unset($collection[$key]);
				continue;             
			}      
			if(!$this->productCatFilter($popup['product_categories'],false)){ 
				unset($collection[$key]);
				continue;             
			}     
      
			if(!$this->productCatFilter($popup['cart_product_categories'],true)){ 
				unset($collection[$key]);
				continue;             
			}              
      
			if($this->specifiedUrlFilter($popup['specified_not_url'],true)){ 
				unset($collection[$key]);
				continue;             
			}   
      
			if(!$this->productCartAttrFilter($popup['product_cart_attr'])){       
				unset($collection[$key]);
				continue;             
			} 
      
			if(!$this->notCartProductsFilter($popup['not_product_cart_attr'])){ 
				unset($collection[$key]);
				continue;             
			}              
      
			if(!$this->addProductAttrFilter($popup['product_attribute'],$productId)){
				unset($collection[$key]);
			}
      
			if($popup['if_pending_order']){
				if(!$checkPendingOrderChecked){
					$pendingOrder = $this->checkPendingOrder();
					$checkPendingOrderChecked = true;
				}
				if(!$pendingOrder){
					unset($collection[$key]);
					continue;  
				}                
			}                                                        

			if($popup['showing_frequency']==7){        
				if(isset($_SESSION['popupIds'][$popup['cookie_id']])){
					unset($collection[$key]);
					continue;         
				}else{
					$_SESSION['popupIds'][$popup['cookie_id']] = true;
				}       
			}
      
			if($popup['stop_further']==1){
				$stopFurther = true;
			}  
      
			//for A B testing
			if(!array_key_exists($popup['popup_id'],$_SESSION['loadedPopups'])){
				$_SESSION['loadedPopups'][$popup['popup_id']] = $popup['cookie_id'];
			}    
                                             
		}            
                                          
		return parent::getPopupsCustomizer($collection);
	} 

	public function addDaysFilter(){
		$day = @date('w');
		$this->join[]  = "LEFT JOIN `".$this->getTable('magebird_popup_day')."` AS `days` ON main_table.popup_id = days.popup_id";
		$this->where[] = "(days.day IN ($day) OR days.day IS NULL)";
	}  

	public function checkTableStatus($tableName){
		$tableName = $this->getTable($tableName);    
		$query = "SHOW TABLES LIKE :table";
		$statement = $this->pdo->prepare($query); 
		$statement->bindParam(':table', $tableName, PDO::PARAM_STR);        
		$statement->execute();  
		$table = $statement->fetchColumn();
		return $table;          
	}     
  
	public function addStoreFilter($storeId){  
		$this->join[] = "INNER JOIN `".$this->getTable('magebird_popup_store')."` AS `stores` ON main_table.popup_id = stores.popup_id";
		$this->where[] = "(stores.store_id IN (0,".intval($storeId)."))";
	}    
  
	public function addCookieFilter($deniedIds){
		$binds = array();
		foreach($deniedIds as $key => $cookieId){
			$binds[] = ':cookie'.$key;  
			$this->binds[':cookie'.$key] = $cookieId;
		}
		$this->where[] = "(cookie_id NOT IN(".implode(",",$binds)."))"; 
	}
  
	public function addIpFilter(){  
		$this->join[] = "LEFT JOIN `".$this->getTable('salesrule_coupon')."` AS `coupons` 
		ON main_table.cookie_id = coupons.popup_cookie_id AND :userIp = coupons.user_ip";
		$this->where[] = "(coupons.user_ip IS NULL OR coupons.user_ip='')";
		$this->binds[':userIp'] = $_SERVER['REMOTE_ADDR'];
	}   
  
	public function addPageFilter($page){                    
		$this->join[]  = "INNER JOIN `".$this->getTable('magebird_popup_page')."` AS `pages` ON main_table.popup_id = pages.popup_id";
		$this->where[] = "(pages.page_id IN (".intval($page).",0,6))";                                                                
	}  
  
	public function addCategoryFilter($categoryId){
		$categoryId = intval($categoryId);
		$pageId     = $this->helper->getParam('popup_page_id');
		$this->join[]  = "INNER JOIN `".$this->getTable('magebird_popup_category')."` AS `categories` ON main_table.popup_id = categories.popup_id";
		if($pageId==3){                             
			$this->where[] = "(categories.category_id IN ($categoryId,0))";
		}
	}  
  
	public function productCatFilter($productCatsFilter,$isCart){
		if(empty($productCatsFilter)) return true;
		if($isCart){
			$prodCats = $this->getCurrentCartProductCat();
		}else{
			$prodCats = $this->getCurrentProductCat();
		}    
		if(!$prodCats) return false;
    $categories = explode(",",$productCatsFilter);
    if(strpos($productCatsFilter, "!")===false){
      if(!$prodCats) return false;
      foreach($categories as $cat){
        if(in_array($cat, $prodCats)){
          return true;
        }
      }   
      return false;   
    }else{
      $categories = str_replace("!","",$categories);
      foreach($categories as $cat){
        if(in_array($cat, $prodCats)){
          return false;
        }
      }
      return true;      
    } 
	}     
  
	public function addProductFilter($productId){
		$productId = intval($productId);
		$pageId    = $this->helper->getParam('popup_page_id');
		$this->join[]  = "INNER JOIN `".$this->getTable('magebird_popup_product')."` AS `products` ON main_table.popup_id = products.popup_id";
		if($pageId==2){
			$this->where[] = "(products.product_id IN($productId,0))";
		}
	} 
  
	public function addCustomerGroupsFilter(){
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			$customerGroupId = intval($this->helper->getPopupCookie('customerGroupId'));
		}else{
			$customerGroupId  = 0;
		}   
      
		$this->join[]  = "LEFT JOIN `".$this->getTable('magebird_popup_customer_group')."` AS `groups` ON main_table.popup_id = groups.popup_id";
		$this->where[] = "(groups.customer_group_id IN ($customerGroupId) OR groups.customer_group_id IS NULL)";
	}  
  
	public function addIfRefferalFilter(){
		$url = null;             
		if($this->helper->getParam('ref')){
			$url = str_replace(array("index.php/","http://","https://"),"",urldecode($this->helper->getParam('ref')));
		} 
		if(!isset($_SESSION['popupReferer'])) $_SESSION['popupReferer'] = $url;
      
		$this->binds[':referalUrl'] = $_SESSION['popupReferer'];
      
		$this->join[]  = "LEFT JOIN `".$this->getTable('magebird_popup_notreferral')."` AS `notreferrals` 
		ON main_table.popup_id = notreferrals.popup_id AND :referalUrl LIKE notreferrals.not_referral";
		$this->where[] = "(notreferrals.not_referral IS NULL)";
   
		$this->join[]  = "LEFT JOIN `".$this->getTable('magebird_popup_referral')."` AS `referrals` ON main_table.popup_id = referrals.popup_id";
		$this->where[] = "(referrals.referral = '' OR referrals.referral IS NULL OR :referalUrl LIKE referrals.referral)";      
    
	}         
  
	public function specifiedUrlFilter($url,$isNegative=false){
		if($isNegative){
			if(!$url) return false; //no condition applied
		}else{
			if(!$url) return true; //no condition applied
		}
		$urls = explode(",,",$url);    
		$currentUrl = str_replace(array("index.php/","http://","https://"),"",urldecode($this->helper->getParam('url')));

		foreach($urls as $url){   
			if(substr($url, -1)=="%" && substr($url, 0,1)=="%"){            
				if(strpos($currentUrl,trim($url,'%'))!==false) return true;
			}elseif(substr($url, 0,1)=="%"){
				if(ltrim($url,'%') == substr($currentUrl, -(strlen($url)-1))) return true;
			}elseif(substr($url, -1)=="%"){
				if(rtrim($url,'%') == substr($currentUrl, 0, strlen($url)-1)) return true;
			}else{
				if($url == $currentUrl) return true;
			}
		}
		return false;
	}       
      
	public function addNowFilter(){
		$now = @date('Y-m-d H:i:s');    
		$this->where[] = "((from_date < '" . $now . "') OR (from_date IS NULL)) AND ((to_date > '" . $now . "') OR (to_date IS NULL))";
	}
  
  public function hoursFilter() {
      $now = @date('G');  
      $this->where[] = "((from_hour <= '" . $now . "') OR (from_hour IS NULL)) AND ((to_hour >= '" . $now . "') OR (to_hour IS NULL))";
  }   
  
	public function addCountryFilter($countryId){
		$this->binds[':countryId'] = $countryId;
		$this->join[]  = "LEFT JOIN `".$this->getTable('magebird_popup_country')."` AS `countries` ON main_table.popup_id = countries.popup_id";
		$this->where[] = "(countries.country_id IS NULL OR countries.country_id IN ('',:countryId))";
	}   
  
	public function addNotCountryFilter($countryId){
		$countryId = substr($countryId,0,5);     
		$this->binds[':notCountryId'] = $countryId;
		$this->join[]  = "LEFT JOIN `".$this->getTable('magebird_popup_notcountry')."` AS `notcountries` ON main_table.popup_id = notcountries.popup_id AND notcountries.country_id=:notCountryId";
		$this->where[] = "(notcountries.country_id IS null)";
	}   
  
	function anyProductInCart(){    
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			if($this->helper->getPopupCookie('cartProductIds')){
				return 1;
			}    
		} 
		return 2;
	}     
  
	function checkPendingOrder(){    
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			return $this->helper->getPopupCookie('pendingOrder');   
		} 
		return 0;
	}   
  
    
	function getCartQty(){  	
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			return intval($this->helper->getPopupCookie('cartQty'));    
		}  
		return '0';
	} 
  
	function getSubtotal(){
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			return floatval($this->helper->getPopupCookie('cartSubtotal'));    
		}  
		return '0';
	}   

	public function getDeniedIds(){
		$popupIds = isset($_COOKIE['popupIds']) ? $_COOKIE['popupIds'] : '';
		//cookies created with native Magento function
		$popupIds = unserialize($popupIds); 
		$deniedIds[] = '';
		if($popupIds){
			foreach($popupIds as $popupId => $expire){
				if($expire>=time() && !in_array(strval($popupId),$deniedIds)){       
					$deniedIds[] = strval($popupId);
				}
			}       
		}
		//cookies created with native javascript
		$popupIds = isset($_COOKIE['popup_ids']) ? $_COOKIE['popup_ids'] : '';
		$popupIds = explode("|",$popupIds);      
		if($popupIds){
			foreach($popupIds as $key => $popupId){
				$explode = explode("=",$popupId);
				if(!isset($explode[1])) continue;
				$expire = $explode[1];
				$popupId = $explode[0]; 
				if($expire>=$this->getLocalTime() && !in_array(strval($popupId),$deniedIds)){       
					$deniedIds[] = strval($popupId);
				}
			}       
		}
		return $deniedIds;         
	}

	public function checkIfPageRefreshed($lastPageviewId){
		$lastPageviewId = substr($lastPageviewId,0,10);
		$tableName = $this->getTable('magebird_popup');
		$query = "UPDATE `{$tableName}` SET `page_reloaded`=`page_reloaded`+1,`window_closed`=`window_closed`-1 WHERE last_rand_id=:lastPageviewId";
		$statement = $this->pdo->prepare($query); 
		$binds = array('lastPageviewId' => $lastPageviewId);       
		$statement->execute($binds);  
	}
  
  public function changePubDir(){
      
		$sql = "INSERT IGNORE INTO `".$this->getTable('core_config_data')."`
            (path,value) VALUES ('magebird_popup/general/pub_dir','')";        
		$this->pdo->query($sql);
  } 

	public function checkLicence(){
		$sql = "SELECT path,value FROM `".$this->getTable('core_config_data')."` 
		WHERE path='magebird_popup/general/extension_key' 
		OR path='magebird_popup/general/trial_start'";        
		$results = $this->pdo->query($sql);
		$data = $results->fetchAll(PDO::FETCH_ASSOC);
		$extensionKey = '';
		$trialStart = null;
		foreach($data as $d){
			if($d['path']=='magebird_popup/general/extension_key'){
				$extensionKey = $d['value'];
			}elseif($d['path']=='magebird_popup/general/trial_start'){
				$trialStart = $d['value'];
			}
		}  
		if(empty($extensionKey) && ($trialStart<strtotime('-7 days'))){
			return false;
		}     
		return true;
	}
  
	public function checkAttributes($productId,$attributes){
		$globalFalse = false;
		if(!$productId) return false;                      
		$product = $this->product->getProduct($productId,$this->storeId,$this->pdo,$this->dbPrefix,$this->helper);
		$attrs = explode(",,",$attributes);                 
		foreach($attrs as $attr){
			$orCond = false;
			if(strpos($attr,"OR ")!==false){
				$attr = str_replace("OR ", '', $attr);
				$orCond = true;  
			}          
			$operators = array('!=EMPTY','=EMPTY','<','>','=','!=','>=','<=');
			foreach($operators as $opr){
				if(strpos($attr,$opr)!==false){
					//because some operators look similar (!=EMPTY AND =EMPTY,= AND !=, ...)
					if($opr=="=" && 
						(strpos($attr,"!=")!==false 
							|| strpos($attr,">=")!==false 
							|| strpos($attr,"!=EMPTY")!==false
							|| strpos($attr,"=EMPTY")!==false
							|| strpos($attr,"<=")!==false)
					) continue;     
					if($opr=="!=" && strpos($attr,"!=EMPTY")!==false) continue;
					if($opr=="=EMPTY" && strpos($attr,"!=EMPTY")!==false) continue;
					if($opr=="<" && strpos($attr,"<=")!==false) continue;
					if($opr==">" && strpos($attr,">=")!==false) continue;                       
					$attrData = explode($opr,$attr);
					$code = $attrData[0];
					$value = $attrData[1];
                           
					if($productId && isset($product[$code])){                  
						$prodVal = $product[$code];                             
					}else{
						$prodVal = '';
					}    
             
					$currentFalse = false; 
					switch($opr){
						case '!=EMPTY':
						if(empty($prodVal)){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;
						case '=EMPTY':
						if(empty($prodVal)){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;
						case '<':
						if($prodVal>=$value){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;   
						case '>':
						if($prodVal<=$value){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;   
						case '=':
						$value = explode(",", $value);
						$matches = false;
						foreach($value as $val){
							if(substr_count($val,"%")==2){
								$val = trim($val,"%");  
								if(strpos($prodVal, $val)!==false){
									$matches = true;
								}
							}else{
								if($prodVal==$val){
									$matches = true;
								}
							}                     
						}
						if(!$matches){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}                                  
						break;  
						case '!=':                    
						if($prodVal==$value){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;
						case '>=':
						if($prodVal<$value){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;        
						case '<=':
						if($prodVal>$value){
							if(!$orCond) $globalFalse = true;
							$currentFalse = true;
						}
						break;                                
					}     
					if($orCond && !$currentFalse){
						return true;
					}                                               
				}                       
			}                     
		} 
		if($globalFalse){
			return false;
		} 
		return true;
	}
  
	public function productCartAttrFilter($attr){
		if(empty($attr)) return true;    
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){    
			$ids = $this->helper->getPopupCookie('cartProductIds');
			$ids = explode(",",$ids);  
			      
			foreach($ids as $productId){              
				if($this->checkAttributes($productId,$attr)){
					return true;
				} 
			}      
		}     
		return false; //no product in cart  
	}
  
	public function notCartProductsFilter($notAttr){
		if(empty($notAttr)) return true;    
		if($this->helper->getPopupCookie('magentoSessionId')==$this->sessionId){
			$ids = $this->helper->getPopupCookie('cartProductIds');
			$ids = explode(",",$ids);   
			foreach($ids as $productId){  
				if($this->checkAttributes($productId,$notAttr)){
					return false;
				} 
			} 
		} 
		return true; //no product in cart  
	}  
  
	public function addProductAttrFilter($productAttribute,$productId){                              
		if($productAttribute){                                                         
			if(!$this->checkAttributes($productId,$productAttribute)){
				return false;
			}                   
		}
		return true;
	} 
  
	public function getLocalTime(){  
		$time = time()+@date('Z');
		return $time;  
	}
  
	public function setTimezone(){ 
		$websiteId = intval($this->helper->getParam('storeId'));          
		$sql = "SELECT value FROM `".$this->getTable('core_config_data')."` WHERE path='general/locale/timezone' AND scope='websites' AND scope_id=$websiteId";        
		$results = $this->pdo->query($sql);
		$timezone = $results->fetchColumn();          
		if(!$timezone){
			$sql = "SELECT value FROM `".$this->getTable('core_config_data')."` WHERE path='general/locale/timezone' AND scope='default' AND scope_id=0";
			$results2 = $this->pdo->query($sql);
			$timezone = $results2->fetchColumn();              
		}       
		if(function_exists('timezone_open')){  
			if(!@timezone_open($timezone)){        
				exit('Wrong timezone');
			}
		}                
		date_default_timezone_set($timezone);   
	}


	//MOUSETRACKING 
	public function handleMousetracking(){
  
		$mousetracking = $this->helper->getParam('mousetracking');
		$mousetracking = json_decode($mousetracking);  
        
		$tableName = $this->getTable('magebird_mousetracking');
		$date = @date('Y-m-d H:i:s');
		$query = "INSERT INTO `{$tableName}` (window_width,window_height,mousetracking,user_ip,device,date_created)
		VALUES (:width,:height,:cursor,:userIp,:device,'$date')";
  
		$statement = $this->pdo->prepare($query); 
		$deviceType = $mousetracking->isMobile ? 2 : 1;
		$binds = array('width' => $mousetracking->width,'height'=>$mousetracking->height,'cursor'=>$mousetracking->cursor,
			'device'=>$deviceType,'userIp'=>$_SERVER['REMOTE_ADDR']);       
		$statement->execute($binds);  
		$mousetrackingId = $this->pdo->lastInsertId(); 
      
		$this->deleteOldMousetracking();    
        
		$mousetrackingPopups = $this->helper->getParam('mousetrackingPopups');
		$mousetrackingPopups = json_decode($mousetrackingPopups);
		$tableName = $this->getTable('magebird_mousetracking_popup');                                            
		foreach($mousetrackingPopups as $id => $popup){
			$query = "INSERT INTO `{$tableName}` (mousetracking_id,popup_id,popup_width,popup_left_position,
			popup_top_position,start_seconds,total_ms,behaviour)
			VALUES ($mousetrackingId,:popupId,:width,:left,:top,:startS,:totalMs,:behaviour)";
			$statement = $this->pdo->prepare($query); 
			$binds = array('popupId' => $id,'width' => $popup->width,'left'=>$popup->left,
				'top'=>$popup->top,'startS'=>$popup->startDelayMs,'totalMs'=>$popup->totalMiliSeconds,'behaviour'=>$popup->ca);                        
			$statement->execute($binds);                                     
		}  
	}
  
	public function deleteMousetracking($strtotimeXAgo){
		$tableName = $this->getTable('magebird_mousetracking');
		$tableName2 = $this->getTable('magebird_mousetracking_popup');
		$query = "DELETE $tableName,$tableName2 FROM $tableName
		INNER JOIN $tableName2 ON $tableName.mousetracking_id=$tableName2.mousetracking_id
		WHERE date_created < '".@date('Y-m-d H:i:s', $strtotimeXAgo)."'";                               
		$statement = $this->pdo->prepare($query);                         
		$statement->execute();            
	}
  
	public function deleteOldMousetracking(){
		$sql = "SELECT value FROM `".$this->getTable('core_config_data')."` WHERE path='magebird_popup/statistics/delete_mousetracking'";
		$results = $this->pdo->query($sql);
		$deleteOld = $results->fetchColumn();                  
		switch($deleteOld){
			case 1:
			$this->deleteMousetracking(strtotime("-1 month"));
			break;
			case 2:
			$this->deleteMousetracking(strtotime("-6 month"));        
			break;
			case 3:
			$this->deleteMousetracking(strtotime("-7 day"));          
			break;
			case 4:
			//dont delete data  
			break;          
			default:
			$this->deleteMousetracking(strtotime("-6 month"));                                              
		}  
	}
	//END MOUSETRACKING    
  
	public function addNewView(){
		if(!$this->helper->getPopupCookie('newVisit')){    
			$this->helper->setPopupCookie('newVisit',1,time()+(3600*48));
			$table = $this->getTable('magebird_popup_stats');
			$sql = "UPDATE $table SET visitors=visitors+1";  
			$this->pdo->query($sql);    
		}
	}
                  
	public function uniqueViewStats($popupId){ 
		$popupId = intval($popupId);
		$lastPopups = $this->helper->getPopupCookie('lastPopups');
		$idExists = false;
		$explode = explode(",",$lastPopups);
		foreach($explode as $popupId2){
			if($popupId2 == $popupId) $idExists = true;       
		}   
		//make sure cookies are read. In some cases if ajax call is closed too fast cookies are not read.               
		if(!$idExists && $this->helper->getPopupCookie('magentoSessionId')){
			$this->helper->setPopupCookie('lastPopups',$lastPopups.",".$popupId,time()+(3600*48));
			//because popup was shown after user already added product to cart
			if($this->helper->getPopupCookie('cartProductIds')){
				$sql = "UPDATE ".$this->getTable('magebird_popup_stats')." 
				SET popup_visitors=popup_visitors+1,popup_carts=popup_carts+1 WHERE popup_id=".$popupId;       
			}else{
				$sql = "UPDATE ".$this->getTable('magebird_popup_stats')." 
				SET popup_visitors=popup_visitors+1 WHERE popup_id=".$popupId;       
			}
			$this->pdo->query($sql);
		}            
	}      
            
}