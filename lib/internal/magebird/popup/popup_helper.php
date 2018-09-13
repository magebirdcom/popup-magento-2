<?php

class popup_helper{
  
  var $popupCookie;
  var $sessionId = null;
  public function __construct(){   
    $this->sessionId = isset($_COOKIE["PHPSESSID"]) ? $_COOKIE["PHPSESSID"] : '';
    $this->popupCookie = false;  
    if(!$this->getPopupCookie('magentoSessionId')){
      $this->setPopupCookie('magentoSessionId',$this->sessionId);
    }     
  }
  
  public function getIsCrawler(){  
    $userAgent = $_SERVER['HTTP_USER_AGENT'];    
    $crawlers = 'robot|spider|crawler|curl|Bloglines subscriber|Dumbot|Sosoimagespider|QihooBot|FAST-WebCrawler|Superdownloads Spiderman|LinkWalker|msnbot|ASPSeek|WebAlta Crawler|Lycos|FeedFetcher-Google|Yahoo|YoudaoBot|AdsBot-Google|Googlebot|Scooter|Gigabot|Charlotte|eStyle|AcioRobot|GeonaBot|msnbot-media|Baidu|CocoCrawler|Google|Charlotte t|Yahoo! Slurp China|Sogou web spider|YodaoBot|MSRBOT|AbachoBOT|Sogou head spider|AltaVista|IDBot|Sosospider|Yahoo! Slurp|Java VM|DotBot|LiteFinder|Yeti|Rambler|Scrubby|Baiduspider|accoona';
    $isCrawler = (preg_match("/$crawlers/i", $userAgent) > 0);
    return $isCrawler;
  }	
  
  public function getParam($param){
    if(isset($_GET[$param])){
      return $_GET[$param];
    }elseif(isset($_POST[$param])){
      return $_POST[$param];
    }else{
      return false;
    } 
  }  
   
  //If we don't need also expire part then we dont return expired cookies
  function getPopupCookie($param, $alsoExpirePart=false){
  
    $param2 = $param;    
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
    //if we don't need also expire part to check expire time later, then return
    //only cookies that are not expired 

    if(!$alsoExpirePart){
      $value = explode("=",$value);            
      if(isset($value[1])){
        $expire = $value[1]; 
        if($expire<(time())) return false;
      }                   
      $value = $value[0];
    } 
    return $value;  
  }   
  
  //$expired is in timestamp
  public function setPopupCookie($cookieName,$value, $expired=false){
    if($expired){
      $value .= "=".$expired;
    }                        
    if($this->popupCookie){
      $cookie = $this->popupCookie;
      $oldVal = $this->getPopupCookie($cookieName, true); //return also expired because we will update time with new 
      if(strpos($cookie,$cookieName)!==false){
        $cookie = str_replace($cookieName.":".$oldVal, $cookieName.":".$value, $cookie);      
      }else{
        $cookie .= "|".$cookieName.":".$value;
      }
    }else{
      $cookie = $cookieName.":".$value;      
    } 
    
    setcookie('popupData', $cookie, time() + @date('Z') + (3600*24*365), '/'); 
    $this->popupCookie = $cookie;   
  } 
  
  public function isLoggedIn(){   
    if($this->getPopupCookie('magentoSessionId')==$this->sessionId){
      return $this->getPopupCookie('loggedIn');
    }else{
      return 0;
    }    
  } 
  
  public function isSecure(){
    return substr(urldecode($this->getParam('baseUrl')),0,5)=='https';
  } 
  
  public function getIp(){
    if(isset($_SERVER["HTTP_CF_CONNECTING_IP"])){
        $ipaddress = $_SERVER["HTTP_CF_CONNECTING_IP"];
    }elseif(isset($_SERVER['HTTP_CLIENT_IP'])){
        $ipaddress = $_SERVER['HTTP_CLIENT_IP'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED_FOR'])){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED_FOR'];
    }elseif(isset($_SERVER['HTTP_X_FORWARDED'])){
        $ipaddress = $_SERVER['HTTP_X_FORWARDED'];
    }elseif(isset($_SERVER['HTTP_FORWARDED_FOR'])){
        $ipaddress = $_SERVER['HTTP_FORWARDED_FOR'];
    }elseif(isset($_SERVER['HTTP_FORWARDED'])){
        $ipaddress = $_SERVER['HTTP_FORWARDED'];
    }elseif(isset($_SERVER['REMOTE_ADDR'])){
        $ipaddress = $_SERVER['REMOTE_ADDR'];
    }else{
        $ipaddress = '';
    }       
    return $ipaddress;
  }   
      
}