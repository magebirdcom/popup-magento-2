<?php
namespace Magebird\Popup\Model;
use ML_Subscribers;
use AWeberAPI;
use Mailjet;
use MCAPI;
use GetResponse;
use ActiveCampaign;
use SendyPHP;
use CS_REST_Subscribers;
use Emma;
use Dotmailer;
use restApi;
use api;
use \Magebird\MailChimp;

class Subscriber extends \Magento\Framework\Model\AbstractModel{
	protected $_transportBuilder;
	protected $scopeConfig;
	protected $dir;
	protected $request;
	protected $_storeManager;
	public function __construct(     
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
		\Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
		\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
		\Magento\Framework\App\Filesystem\DirectoryList $dir,
		\Magento\Framework\App\Request\Http $request,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
		\Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null        
	){
		parent::__construct($context,$registry);
		$this->_transportBuilder = $transportBuilder;
		$this->scopeConfig = $scopeConfig;
		$this->dir = $dir;
		$this->request = $request;
		$this->_storeManager = $storeManager;
		$this->_init('Magebird\Popup\Model\ResourceModel\Subscriber');
	}
    
    //todo: some mail newsleteer do not have cupon code functionality?
	function mailCoupon($email,$coupon){
          
		$templateParams = array();
		$templateParams['coupon_code'] = $coupon;
		$senderInfo = [
			'name' => $this->scopeConfig->getValue('trans_email/ident_general/name'),
			'email' => $this->scopeConfig->getValue('trans_email/ident_general/email'),
		];                
		$transport = $this->_transportBuilder->setTemplateIdentifier('popup_coupon_newsletter')
		->setTemplateOptions(['area' => \Magento\Framework\App\Area::AREA_FRONTEND, 
				'store' => $this->_storeManager->getStore()->getId()])        
		->setTemplateVars($templateParams)
		->setFrom($senderInfo)
		->addTo($email)
		->getTransport();
		$transport->sendMessage();                           
	}
    
	function subscribeKlaviyo($listId,$email,$firstName,$lastName){ 
		$url = "https://a.klaviyo.com/api/v1/list/$listId/members";
		$doubleOptin = $this->scopeConfig->isSetFlag('magebird_popup/services/klaviyo_double_option');
		$doubleOptin = $doubleOptin ? "true" : "false";
		$resp=null;   
		$apiKey = $this->scopeConfig->getValue('magebird_popup/services/klaviyo_key');
    $response['success'] = true;
    if(function_exists('curl_version')){
      $fields = array("api_key"=>$apiKey,
                                 "email"=>$email,
                                 "properties"=>'{ "$first_name" : "'.$firstName.'", "$last_name" : "'.$lastName.'" }',
                                 "confirm_optin"=>$doubleOptin
                                 );
      $ch = @curl_init($url);
      @curl_setopt($ch, CURLOPT_POST, true);  
      @curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
      @curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
      @curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
      $resp = @curl_exec($ch); 
      @curl_close($ch); 
      $resp = json_decode($resp,true);          
    }else{
  		$data=http_build_query(array("api_key"=>$apiKey,
  				"email"=>$email,
  				"properties"=>'{ "$first_name" : "'.$firstName.'", "$last_name" : "'.$lastName.'" }',
  				"confirm_optin"=>$doubleOptin
  			));
        
  		$headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
  		$options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
  		$context = stream_context_create($options);
  		$resp = @file_get_contents($url,false,$context,0,1000);
  		$resp = json_decode($resp,true);  		    
    }  

    if (!isset($resp['already_member']) && !isset($resp['person'])) {
        $response['success'] = false;
        $response['msg'] = "Wrong api key or list id";
    } elseif ($resp['status']==404) {
        $response['success'] = false;
        $response['msg'] = $resp['message'];        
    } elseif ($resp['already_member']) {
        $response['success'] = false;
        $response['msg'] = "You are already subscribed";
    }

		return $response; 
	}
    
	function subscribeMailjet($listId,$email,$firstName,$lastName){ 
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Mailjet/php-mailjet-v3-simple.class.php');
		$apiKey = $this->scopeConfig->getValue('magebird_popup/services/mailjet_key');
		$secretKey = $this->scopeConfig->getValue('magebird_popup/services/mailjet_secret_key');

		$mj = new Mailjet( $apiKey, $secretKey );
		$params = array(
			"method" => "POST",
			"ID" => $listId
		);
		$name = $lastName ? $firstName." ".$lastName : $firstName;
		$contact = array(
			"Email"         =>  $email,   
			"Name"          =>  $name,
			"Action"        =>  "addforce"
		); 
		$params = array_merge($params, $contact);
		$result = $mj->contactslistManageContact($params);
    $response['success'] = true;
    if (!$result || $result->Count<1) {
      $response['success'] = false;
      $response['msg'] = __("Wrong api/secret key or list id");
    }

		return $response; 
	} 
  
  function subscribeNuevomailer($email, $listIds, $firstName, $lastName, $templateId) {
      require_once($this->dir->getPath('lib_internal') . '/magebird/popup/nuevomailer/api.php');
      $url = $this->scopeConfig->getValue('magebird_popup/services/nuevomailer_url');
      $apiKey = $this->scopeConfig->getValue('magebird_popup/services/nuevomailer_api_key');
      $api = new api($url, $apiKey);
      $optin = $this->scopeConfig->getValue('magebird_popup/services/nuevomailer_optin');
      $optin = $optin ? -1 : 0;
      $response = $api->subscribe($email, $listIds, $firstName, $lastName, $optin, $templateId);
      return true;
  }     
    
	function subscribeMailchimp($listId,$email,$firstName,$lastName,$coupon){ 
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Mailchimp/MailChimp.php');
		$api = new MailChimp($this->scopeConfig->getValue('magebird_popup/services/mailchimp_key'));
		$doubleOptin = $this->scopeConfig->isSetFlag('magebird_popup/services/mailchimp_double_option');
        
		$groups = false;
		$groupName = $this->request->getParam('groupName');
		$groupValue = $this->request->getParam('groupValue');
    
    $groupIds = array();
    if ($groupName) {
        $result = $api->get("/lists/$listId/interest-categories");
        if(isset($result['categories'])){
        foreach($result['categories'] as $groupCat){
          if($groupCat['title']==$groupName){
            $groupCatId = $groupCat['id'];
            $groups = $api->get("/lists/$listId/interest-categories/$groupCatId/interests");
            foreach($groups['interests'] as $group){
              if($group['name']==$groupValue || (is_array($groupValue) && in_array($group['name'], $groupValue))){
                $groupIds[$group['id']] = true;
              }
            } 
            break;
          }
        }
        }        
    }
        
    $status = $doubleOptin ? 'pending' : 'subscribed';
    $groups = false;
    if(!$firstName) $firstName = '';
    if(!$lastName) $lastName = '';
    $mergeVar = array(
        'FNAME' => $firstName,
        'LNAME' => $lastName
    );
        
        
    $extraFields = $this->request->getParam('extra_fields');
    if (is_array($extraFields)) {
        foreach ($extraFields as $field => $value) {
            $mergeVar[$field] = $value;
        }
    }
    $mergeVar['POPUP_COUP'] = $coupon;
    
    $params = array(
        				'email_address' => $email,
        				'merge_fields' => $mergeVar,
        				'status' => $status
        			);
    if($groupIds){
      $params['interests'] = $groupIds;
    }
    $result = $api->post("/lists/$listId/members", $params);
    return $result; 
	}
    
	function subscribeGetResponse($listId,$email,$firstName,$lastName,$coupon){
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/GetResponse/GetResponseAPI.class.php');
		$api = new GetResponse($this->scopeConfig->getValue('magebird_popup/services/getresponse_key')); 
		if($coupon){
			$add = $api->addContact($listId,$firstName." ".$lastName,$email,'standard',0,array('POPUP_COUPON'=>$coupon));
		}else{
			$add = $api->addContact($listId,$firstName." ".$lastName,$email,'standard',0);
		}           
		return $add;     
	}
    
	function subscribeActiveCampaign($listId,$email,$firstName,$lastName,$coupon){
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/ActiveCampaign/ActiveCampaign.class.php');
		$key = $this->scopeConfig->getValue('magebird_popup/services/activecampaign_key');
		$url = $this->scopeConfig->getValue('magebird_popup/services/activecampaign_url');
		$customField = $this->request->getParam('custom_field_name');
		$customFieldValue = $this->request->getParam('custom_field_value');                        
		$formId = $this->request->getParam('form_id');
		$ac = new ActiveCampaign($url, $key);
		$contact = array(
			"email"             => $email,
			"first_name"        => $firstName,
			"last_name"         => $lastName,
			"p[{$listId}]"      => $listId,
			"status[{$listId}]" => 1, // "Active" status
		);
        
		if($customField && $customFieldValue){
			$contact["field[".$customField.",0]"] = $customFieldValue; 
		} 
		if($formId) $contact['form'] = $formId;       
        
		$contact_sync = $ac->api("contact/add", $contact);
		$response['success'] = true;
		if(!(int)$contact_sync->success){     
      if($contact_sync->error){
        $response['msg'] = __($contact_sync->error);
      }else{
        $response['msg'] = "An unexpected problem occurred with the API request.";
      }                
			$response['success'] = false;
		}        
     
		return $response;     
	}    
    
	function subscribeSendy($listId,$email,$firstName,$coupon){      
		$sendy = $this->scopeConfig->getValue('magebird_popup/services/enablesendy');
		if($sendy){
			require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Sendy/SendyPHP.php');
			$apiKey = $this->scopeConfig->getValue('magebird_popup/services/sendy_key');
			$url = $this->scopeConfig->getValue('magebird_popup/services/sendy_url'); 
			$config = array(
				'api_key' => $apiKey, //your API key is available in Settings
				'installation_url' => $url,  //Your Sendy installation
				'list_id' => $listId
			);      
   
			$sendy = new SendyPHP($config);                        
			if($coupon){
				$results = $sendy->subscribe(array(
						'name'=> $firstName,
						'email' => $email,
						'POPUP_COUPON' => $coupon
					));            
			}else{
				$results = $sendy->subscribe(array(
						'name'=> $firstName,
						'email' => $email
					));           
			}
		}else{
			return array('status'=>false,'message'=>'Sendy is not enabled. Go to Store->Configuration->Popup->Newsletter services to enable it or remove Sendy List Id from Newsletter widget.');
		}
		return $results;    
	}
    
	function subscribeAweber($listId,$email,$firstName,$lastName){
		try{
			require_once($this->dir->getPath('lib_internal') . '/magebird/popup/AWeber/aweber_api/aweber_api.php');
			$consumerSecret =  $this->scopeConfig->getValue('magebird_popup/services/aweber_consumerSecret');
			$consumerKey =  $this->scopeConfig->getValue('magebird_popup/services/aweber_consumerKey');
			$accToken =  $this->scopeConfig->getValue('magebird_popup/services/aweber_token_key');
			$accSecret =  $this->scopeConfig->getValue('magebird_popup/services/aweber_token_secret');
				      
			$aweber = new AWeberAPI($consumerKey, $consumerSecret);
			$account = $aweber->getAccount($accToken, $accSecret);
				
			$isSubsribed = $account->findSubscribers(array('email' => $email));
			if($isSubsribed[0] != null){
				return array("msg"=>__("You are already subscribed"),"error"=>true);
			}
			
			$account_id = $account->id;
			$listURL = "/accounts/{$account_id}/lists/{$listId}"; 
			$list = $account->loadFromUrl($listURL);
			$params = array( 
				"email" => $email,
				"name" => $firstName." ".$lastName
			); 
			$subscribers = $list->subscribers; 
			$subscribers->create($params);
					
			return true;
		} catch(\Exception $exc){
			return array("error"=>true,"msg"=>$exc->message);
		}
	}
    
	function subscribeMailerLite($listId,$email){	
   		
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/MailerLite/ML_Subscribers.php');
		$auth = $this->scopeConfig->getValue('magebird_popup/services/mailerlite_apiKey');
       	
		$wrap = new ML_Subscribers($auth);
		$isSubsribed = $wrap->get($email);
		$isSubsribed = json_decode($isSubsribed,true);
       
		if(isset($isSubsribed["message"])){
			$wrap->setId($listId);
					
			$result = $wrap->add(array(
					'email' => $email,
				));
			  
			return json_decode($result,true);
	        
		}elseif(isset($isSubsribed["error"])){
			return $isSubsribed;
	        
		}else{
			$response['isSubsribed'] = __("You are already subscribed");
			return $response;
		}
	}
   
	function subscribeCampaignMonitor($listId,$email,$firstName,$lastName,$coupon){
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Campaignmonitor/csrest_subscribers.php');
		$auth = array('api_key' => $this->scopeConfig->getValue('magebird_popup/services/campaignmonitor_key'));
		$wrap = new CS_REST_Subscribers($listId, $auth);      
		$result = $wrap->add(array(
				'EmailAddress' => $email,
				'Name' => $firstName." ".$lastName,
				'CustomFields' => array(
					array(
						'Key' => 'POPUP_COUPON',
						'Value' => $coupon
					)
				),
				'Resubscribe' => true
			));       
            
		return $result; 
	}
    
	function subscribeEmma($email,$emmaGroupIds,$firstName,$lastName){
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Emma/Emma.php');
		$publicApiKey = $this->scopeConfig->getValue('magebird_popup/services/emma_public_key');
		$privateApiKey = $this->scopeConfig->getValue('magebird_popup/services/emma_private_key');
		$sendOptin = $this->scopeConfig->getValue('magebird_popup/services/emma_send_optin');
		$sendOptin = $sendOptin ? true : false;
		$accountId = $this->scopeConfig->getValue('magebird_popup/services/emma_account_id');
		$emma = new Emma($accountId,$publicApiKey,$privateApiKey);
		$response = $emma->subscribe($emmaGroupIds, $email, array("first_name"=>$firstName,"last_name"=>$lastName),$sendOptin);
		return $response;
	}
  
  function subscribeDotmailer($email,$addressId,$firstName,$lastName){
    require_once($this->dir->getPath('lib_internal') . '/magebird/popup/dotmailer/Dotmailer.php');
    
    $apiEmail = $this->scopeConfig->getValue('magebird_popup/services/dotmailer_email');
    $password = $this->scopeConfig->getValue('magebird_popup/services/dotmailer_password');
    $apiEndpoint = $this->scopeConfig->getValue('magebird_popup/services/dotmailer_api_endpoint');
    $data = array(
        'Email' => $email,
        'EmailType' => 'Html',
        'dataFields' => array(
        array(
        'Key' => 'FIRSTNAME',
        'Value' => $firstName),
        array(
        'Key' => 'FULLNAME',
        'Value' => $firstName." ".$lastName ),
        array(
        'Key' => 'LASTNAME',
        'Value' => $lastName),
    
        )
    );
    $dotmailer = new Dotmailer($apiEmail,$password,$apiEndpoint);
    $response = $dotmailer->subscribe($addressId,$data);
    return $response;
  }   
    
	function subscribePhplist($email,$listId){
		require_once($this->dir->getPath('lib_internal') . '/magebird/popup/phplist/restApi.php');
		$confirmed = $this->scopeConfig->getValue('magebird_popup/services/phplist_confirmed');
        
		if(!$adminUrl = $this->scopeConfig->getValue('magebird_popup/services/phplist_url')){
			return array('status'=>2,'error'=>"Missing phpList url");
		}
		if(!$username = $this->scopeConfig->getValue('magebird_popup/services/phplist_username')){
			return array('status'=>2,'error'=>"Missing phpList username");
		}
		if(!$password = $this->scopeConfig->getValue('magebird_popup/services/phplist_password')){
			return array('status'=>2,'error'=>"Missing phpList password");
		}              
		$config = array('adminUrl'=>$adminUrl,
			'username'=>$username,
			'password'=>$password
		);
		$api = new restApi($config);
		$response = $api->subscribe($email,$listId,$confirmed);        
		return $response;     
	}      
    
	//delete old emails to prevent table overgrowth
	function cleanOldEmails(){                  
		$coll = $this->getCollection();
		$ago2months = strtotime("-4 month");
		$coll->addFieldToFilter("date_created", array("lt" => $ago2months));
		$coll->walk('delete');
	}
    
	//delete subscriber from table to not get another coupon code again
	function deleteTempSubscriber($email){
		$coll = $this->getCollection();
		$coll->addFieldToFilter('subscriber_email', $email);
		$coll->walk('delete');
	}
}