<?php

namespace Magebird\Popup\Block;
use Magento\Framework\View\Element\Template;
use AWeberAPI;

class Popup extends Template
{
 	protected $_msgs;
 	protected $dir;
		
	public function __construct(
		\Magento\Framework\View\Element\Template\Context $context,
		\Magento\Framework\Message\ManagerInterface $messageManager,
		\Magento\Framework\App\Filesystem\DirectoryList $dir
	){
		$this->_msgs = $messageManager;
		$this->dir = $dir;
		
		parent::__construct($context);
	}
	
	public function generateTokens(){
		$consumerSecret =  $this->_scopeConfig->getValue('magebird_popup/services/aweber_consumerSecret');
		$consumerKey =  $this->_scopeConfig->getValue('magebird_popup/services/aweber_consumerKey');
			
		if($consumerSecret == "" || $consumerKey == ""){
			$this->_msgs->addError(__("Please save AWeber consumer secret and consumer key before visiting this page."));
			
		}elseif(empty($this->getRequest()->getParam('token')) and empty($this->getRequest()->getParam('secret'))){
			require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Aweber/aweber_api/aweber_api.php');
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
		
			if(empty($_COOKIE['accessToken'])){
				if(empty($this->getRequest()->getParam('oauth_token']))){
					$callbackUrl = 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
					list($requestToken, $requestTokenSecret) = $aweber->getRequestToken($callbackUrl);
					
					setcookie('requestTokenSecret', $requestTokenSecret);
					setcookie('callbackUrl', $callbackUrl);
					header("Location: {$aweber->getAuthorizeUrl()}");
					return;
				}
				
				$aweber->user->tokenSecret = $_COOKIE['requestTokenSecret'];
				$aweber->user->requestToken = $this->getRequest()->getParam('oauth_token');
				$aweber->user->verifier = $this->getRequest()->getParam('oauth_verifier');
				list($accessToken, $accessTokenSecret) = $aweber->getAccessToken();
				//setcookie('accessToken', $accessToken);
				//setcookie('accessTokenSecret', $accessTokenSecret);
				header('Location: '.$_COOKIE['callbackUrl']."?token=".$accessToken."&secret=".$accessTokenSecret);
				return;
			}
			
		}
	}

	public function getAweberToken(){
    return $this->getRequest()->getParam('token');
  }
  
	public function getAweberSecret(){
    return $this->getRequest()->getParam('secret');
  }  
  	
	public function getAweberLists(){
		$consumerSecret =  $this->_scopeConfig->getValue('magebird_popup/services/aweber_consumerSecret');
		$consumerKey =  $this->_scopeConfig->getValue('magebird_popup/services/aweber_consumerKey');
		$accToken =  $this->_scopeConfig->getValue('magebird_popup/services/aweber_token_key');
		$accSecret =  $this->_scopeConfig->getValue('magebird_popup/services/aweber_token_secret');

		if($consumerSecret == "" || $consumerKey == "" || $accToken == "" || $accSecret == ""){
			$this->_msgs->addError(__("Please enter all AWeber API keys before visiting this page as otherwise lists are not available."));
			
		
		}else{
			require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Aweber/aweber_api/aweber_api.php');
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
			
			$account = $aweber->getAccount($accToken, $accSecret);
			$account_id = $account->id;
			
			$listURL ="/accounts/{$account->id}/lists/"; 
    		return $account->loadFromUrl($listURL);
		}
	}
	
    protected function _prepareLayout()
    {
 		return parent::_prepareLayout();
    }

}