<?php
/**
 * Magebird.com
 *
 * @category   Magebird
 * @package    Magebird_Popup
 * @copyright  Copyright (c) 2018 Magebird (http://www.Magebird.com)
 * @license    http://www.magebird.com/licence
 * Any form of ditribution, sell, transfer forbidden see licence above 
 */
namespace Magebird\Popup\Controller\Index;

class Newsletter extends \Magento\Framework\App\Action\Action{   
	protected $request;    
	protected $_popup;   
	protected $_helper;  
	protected $_cpnHelper; 
	protected $_config;  
	protected $_subscriber;   
	protected $_popupSubscriber;   
	protected $resultRawFactory;   
	protected $messageManager;    
	protected $cart;                
	protected $timezone;    
	protected $customer;    
	protected $store;
	protected $customerSession;
    	
	public function __construct(
		\Magento\Framework\App\Action\Context $context,
		\Magento\Framework\App\Request\Http $request,
		\Magebird\Popup\Model\Popup $popup,
		\Magebird\Popup\Helper\Data $helper,
		\Magebird\Popup\Helper\Coupon $cpnHelper,
		\Magento\Framework\App\Config\ScopeConfigInterface $config,
		\Magento\Newsletter\Model\Subscriber $subscriber,
		\Magento\Framework\Controller\Result\RawFactory $resultRawFactory,
		\Magebird\Popup\Model\Subscriber $popupSubscriber,
		\Magento\Checkout\Model\Cart $cart,
		\Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone,
		\Magento\Customer\Model\CustomerFactory $customer,
		\Magento\Store\Model\StoreManagerInterface $store,
		\Magento\Customer\Model\Session $customerSession      
	){
		parent::__construct($context);
		$this->request = $request;
		$this->_popup = $popup;
		$this->_helper = $helper;
		$this->_cpnHelper = $cpnHelper;
		$this->_config = $config;
		$this->_subscriber = $subscriber;
		$this->_popupSubscriber = $popupSubscriber;
		$this->resultRawFactory = $resultRawFactory;  
		$this->messageManager = $context->getMessageManager();
		$this->cart = $cart;
		$this->timezone = $timezone;
		$this->customer = $customer;
		$this->store = $store;
		$this->customerSession = $customerSession;
	}
	
	public function execute(){
		$ajaxExceptions  = array(); 
		$popupId         = $this->request->getParam('popupId');
		$widgetId        = $this->request->getParam('widgetId');
		$_popup          = $this->_popup->load($popupId);     
           
		$widgetData      = $this->_helper->getWidgetData($_popup->getPopupContent(),$widgetId);
		$widgetData['cpnExpInherit']     = $this->request->getParam('cpnExpInherit');
		$widgetData['apply_coupon']      = isset($widgetData['apply_coupon']) ? $widgetData['apply_coupon'] : false;
		$widgetData['coupon_expiration'] = isset($widgetData['coupon_expiration']) ? $widgetData['coupon_expiration'] : '';
		$widgetData['rule_id']           = isset($widgetData['rule_id']) ? $widgetData['rule_id'] : '';
		$widgetData['coupon_code']       = isset($widgetData['coupon_code']) ? $widgetData['coupon_code'] : '';
		$widgetData['coupon_length']     = isset($widgetData['coupon_length']) ? $widgetData['coupon_length'] : '';
		$widgetData['coupon_prefix']     = isset($widgetData['coupon_prefix']) ? $widgetData['coupon_prefix'] : '';
		$widgetData['coupon_limit_ip']   = isset($widgetData['coupon_limit_ip']) ? $widgetData['coupon_limit_ip'] : '';
		$widgetData['popup_cookie_id']   = $_popup->getData('cookie_id');
		$coupon          = ''; 
		$confirmNeed     = isset($widgetData['confirm_need']) ? $widgetData['confirm_need'] : '';
		$ruleId          = isset($widgetData['rule_id']) ? $widgetData['rule_id'] : '';
		$couponType      = isset($widgetData['coupon_type']) ? $widgetData['coupon_type'] : '';
		$mailchimp       = $this->_config->getValue('magebird_popup/services/enablemailchimp');
		$klaviyo         = $this->_config->getValue('magebird_popup/services/enable_klaviyo');
		$mailjet         = $this->_config->getValue('magebird_popup/services/enable_mailjet');
		$emma            = $this->_config->getValue('magebird_popup/services/enable_emma');
    $dotmailer       = $this->_config->getValue('magebird_popup/services/enable_dotmailer');             
		$activeCampaign  = $this->_config->getValue('magebird_popup/services/enableactivecampaign');        
		$campaignMonitor = $this->_config->getValue('magebird_popup/services/enablecampaignmonitor');
		$getResponse     = $this->_config->getValue('magebird_popup/services/enablegetresponse');
		$magentoNative   = $this->_config->getValue('magebird_popup/services/enablemagento');
    $nuevomailer     = $this->_config->getValue('magebird_popup/services/enable_nuevomailer');
		$aweber   = $this->_config->getValue('magebird_popup/services/enable_aweber');
		$mailerLite   = $this->_config->getValue('magebird_popup/services/enable_mailerlite');
		$email           = (string) $this->request->getParam('email');         
		$firstName       = $this->request->getParam('first_name');
		$lastName        = $this->request->getParam('last_name');
		$mailchimpListId = isset($widgetData['mailchimp_list_id']) ? $widgetData['mailchimp_list_id'] : '';
		$klaviyoListId   = isset($widgetData['klaviyo_list_id']) ? $widgetData['klaviyo_list_id'] : '';
		$mailjetListId   = isset($widgetData['mailjet_list_id']) ? $widgetData['mailjet_list_id'] : '';        
		$activeCampaignListId = isset($widgetData['ac_list_id']) ? $widgetData['ac_list_id'] : '';
		$getResponseListToken = isset($widgetData['gr_campaign_token']) ? $widgetData['gr_campaign_token'] : '';
		$campaignMonitorId    = isset($widgetData['cm_list_id']) ? $widgetData['cm_list_id'] : '';
		$sendyListId          = isset($widgetData['sendy_list_id']) ? $widgetData['sendy_list_id'] : '';
		$phplistId            = isset($widgetData['phplist_list_id']) ? $widgetData['phplist_list_id'] : '';
    $nuevomailerListIds   = isset($widgetData['nuevomailer_list_ids']) ? $widgetData['nuevomailer_list_ids'] : '';
    $nuevomailerTemplateId = isset($widgetData['nuevomailer_newsletter']) ? $widgetData['nuevomailer_newsletter'] : '';    
		$anSegmentCode        = isset($widgetData['an_segment_code']) ? $widgetData['an_segment_code'] : '';
		$emmaGroupIds         = isset($widgetData['emma_group_ids']) ? $widgetData['emma_group_ids'] : '';
    $dotmailerListId      = isset($widgetData['dotmailer_list_id']) ? $widgetData['dotmailer_list_id'] : '';
		$aweberListId         = isset($widgetData['aweber_list_id']) ? $widgetData['aweber_list_id'] : '';
		$mailerLiteListId     = isset($widgetData['mailerlite_list_id']) ? $widgetData['mailerlite_list_id'] : '';
        	   
		$alreadyConfirmed     = false;
		$websiteId  = $this->store->getWebsite()->getWebsiteId();
		$customer             = $this->customer->create()->setWebsiteId($websiteId)->loadByEmail($email);
		$isSubscribeOwnEmail  = $this->customerSession->isLoggedIn() && $customer->getData('entity_id') == $this->customerSession->getId();
		$validUTF8 = ! (false === mb_detect_encoding(__('You are already subscribed to our newsletter'), 'UTF-8', true));
    
		if($customer->getData('entity_id') && !$customer->getData('confirmation') && $isSubscribeOwnEmail!==false){
			$alreadyConfirmed = true;
		}  
                        
		//If $confirmNeed is 1, coupon will be generated on confirm             
		if($couponType==2 && $ruleId && ($confirmNeed!=1 || $mailchimp || $campaignMonitor || $getResponse)){ 
			$coupon = $this->_cpnHelper->generateCoupon($widgetData,$popupId);                                                    
		}elseif(isset($widgetData['coupon_code']) && $widgetData['coupon_code']){
			$coupon = $widgetData['coupon_code'];
		} 

		//Magento native subscription    
		if($magentoNative){
			$isSubscribed = $this->_subscriber->loadByEmail($email);  
			if($isSubscribed->getData('subscriber_status')!=1){
				$status = $this->_subscriber->subscribe($email);    
			}else{
				if($validUTF8){
					$ajaxExceptions['exceptions'][] = __('You are already subscribed to our newsletter');
				}else{
					$ajaxExceptions['exceptions'][] = utf8_encode(__('You are already subscribed to our newsletter'));
				}                             
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;              
			}                                                               
		}                                              
        
		//Mailchimp subscription
		if($mailchimpListId && $mailchimp){
    
      $result = $this->_popupSubscriber->subscribeMailchimp($mailchimpListId, $email, $firstName, $lastName, $coupon);
      if (!isset($result['unique_email_id'])) {
          if(isset($result['status']) && $result['title']=='Member Exists'){
            $ajaxExceptions['exceptions'][] = utf8_encode(__('You are already subscribed to our newsletter'));
          }elseif(isset($result['status']) && $result['title']=='Resource Not Found'){
            $ajaxExceptions['exceptions'][] = "Wrong Mailchimp List id";  
          }else{
            if(isset($result['detail'])){
              $ajaxExceptions['exceptions'][] = $result['detail'];
            }else{
              $ajaxExceptions['exceptions'][] = "Unknown error. Check your api key.";
            } 
          } 
          $response = json_encode($ajaxExceptions);
          $this->getResponse()->setBody($response);
          return;
      }                                                                                                   
		} 
        
		//Klaviyo subscription
		if($klaviyoListId && $klaviyo){
			$api = $this->_popupSubscriber->subscribeKlaviyo($klaviyoListId,$email,$firstName,$lastName);
      if(!$api['success']){
        $ajaxExceptions['exceptions'][] = $api['msg'];
        $response = json_encode($ajaxExceptions);
        $this->getResponse()->setBody($response);  
        return;           
      }                                                                                                    
		}   
		//Mailjet subscription
		if($mailjetListId && $mailjet){
			$api = $this->_popupSubscriber->subscribeMailjet($mailjetListId,$email,$firstName,$lastName);
			if($api->errorCode){
				$ajaxExceptions['exceptions'][] = $api->errorMessage;
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;           
			}                                                                                                    
		}                
        
		//ActiveCampaign subscription
		if($activeCampaignListId && $activeCampaign){
			$api = $this->_popupSubscriber->subscribeActiveCampaign($activeCampaignListId,$email,$firstName,$lastName,$coupon);
			if(!$api['success']){
				$ajaxExceptions['exceptions'][] = $api['msg'];
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;           
			}                                                                                                    
		}        
        
		//Campaign monitor subscription
		if($campaignMonitorId && $campaignMonitor){         
			$result = $this->_popupSubscriber->subscribeCampaignMonitor($campaignMonitorId,$email,$firstName,$lastName,$coupon);
			//echo "Result of POST /api/v3.1/subscribers/{list id}.{format}\n<br />";
			if(!$result->was_successful()){
				$ajaxExceptions['exceptions'][] = 'Failed with code '.$result->response->Message;
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;   
			}                                                                                                     
		} 
        
		//GetResponse subscription
		if($getResponseListToken && $getResponse){
			$api = $this->_popupSubscriber->subscribeGetResponse($getResponseListToken,$email,$firstName,$lastName,$coupon);
			if(isset($api->message) || !isset($api->queued)){
				$ajaxExceptions['exceptions'][] = "getResponse error: ".$api->message;
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;           
			}                                                                                                    
		} 
          
		//Sendy subscription
		if($sendyListId){
			$response = $this->_popupSubscriber->subscribeSendy($sendyListId,$email,$firstName,$coupon);
			if($response['status']==false){
				$ajaxExceptions['exceptions'][] = "Sendy error: ".$response['message'];
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;           
			}
		} 
                        
		//mailerLite subscription
		if($mailerLite && $mailerLiteListId){
			$api = $this->_popupSubscriber->subscribeMailerLite($mailerLiteListId,$email);
            			  
			if(isset($api["isSubsribed"])){
				$ajaxExceptions['exceptions'][] = $api["isSubsribed"];
				
			}elseif(!isset($api["email"])){
				if(isset($api["error"])){
					$ajaxExceptions['exceptions'][] = $api["error"]["message"];
				}else{
					$ajaxExceptions['exceptions'][] = $api["message"];
				}       
			}  
			if(isset($ajaxExceptions['exceptions'])){
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;
			}
		}elseif($mailerLite && $mailerLiteListId == ""){
			$ajaxExceptions['exceptions'][] = 'Missing MailerLite list id inside Newsletter widget';
			$response = json_encode($ajaxExceptions);
			$this->getResponse()->setBody($response);  
			return;  
		}  
        
		//aweber subscription
		if($aweberListId && $aweber){
      $api = $this->_popupSubscriber->subscribeAweber($aweberListId,$email,$firstName,$lastName);
        	
			if(isset($api["error"])){
				$ajaxExceptions['exceptions'][] = $api["msg"];
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;  
			}
		}elseif($aweberListId=="" && $aweber){
			$ajaxExceptions['exceptions'][] = 'Missing AWeber list id inside Newsletter widget';
			$response = json_encode($ajaxExceptions);
			$this->getResponse()->setBody($response);  
			return;  
		}
    
    //nuevomailer subscription
    if($nuevomailer){            
        if(!$nuevomailerListIds){
          $ajaxExceptions['exceptions'][] = 'Missing Nuevomailer list ids inside Newsletter widget';
          $response = json_encode($ajaxExceptions);
          $this->getResponse()->setBody($response);  
          return;             
        }

        $api = $this->_popupSubscriber->subscribeNuevomailer($email,$nuevomailerListIds,$firstName,$lastName,$nuevomailerTemplateId);
        if(!$api){
          $ajaxExceptions['exceptions'][] = 'An unknown problem occurred';
          $response = json_encode($ajaxExceptions);
          $this->getResponse()->setBody($response);  
          return;           
        }                                                                                                  
    }     
        
		//phplist subscription
		if($phplistId){
			$response = $this->_popupSubscriber->subscribePhplist($email,$phplistId);
			if($response['status']==2){
				$ajaxExceptions['exceptions'][] = "Phplist error: ".$response['error'];
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;           
			}                                                                                                    
		}  
        
		//phplist subscription
		if($emmaGroupIds && $emma){
			$response = $this->_popupSubscriber->subscribeEmma($email,$emmaGroupIds,$firstName,$lastName);
			if($response['status']==2){
				$ajaxExceptions['exceptions'][] = "Emma error: ".$response['error'];
				$response = json_encode($ajaxExceptions);
				$this->getResponse()->setBody($response);  
				return;           
			}                                                                                                    
		}
    
    //dotmailer subscription
    if($dotmailerListId && $dotmailer){                          
        $response = $this->_popupSubscriber->subscribeDotmailer($email,$dotmailerListId,$firstName,$lastName);
        if(!isset($response->status)){
          $ajaxExceptions['exceptions'][] = "Dotmailer error: ".$response->message;
          $response = json_encode($ajaxExceptions);
          $this->getResponse()->setBody($response);  
          return;           
        }                                                                                                    
    }                                                                  
        
		if((!$confirmNeed || $alreadyConfirmed) && $coupon){
			if(isset($widgetData['send_coupon']) && $widgetData['send_coupon']==1){ 
				$this->_popupSubscriber->mailCoupon($email,$coupon);         
			}
				  
			//if apply coupon to cart automatically
			if($widgetData['apply_coupon']==1 || $alreadyConfirmed){            
				$this->customerSession->setData("coupon_code",$coupon);                  
				$this->cart->getQuote()->setCouponCode($coupon)->save();
				$this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();  
				$this->cart->getQuote()->collectTotals()->save();      
			} 
			
			if($alreadyConfirmed){
				$this->messageManager->addSuccess('Your coupon code is: '.$coupon);        
			}  
			      
			//save coupon to database, we will display it after user confirms subscription
		}elseif($confirmNeed && !$alreadyConfirmed){                    
			$model = $this->_popupSubscriber;
			$model->setSubscriberEmail($email);   
			$model->setDateCreated(time());
			if(isset($widgetData['send_coupon']) && $widgetData['send_coupon']){
				$model->setSendCoupon(1);            
			}     
          
			if($coupon){
				$model->setCouponCode($coupon);
			}elseif($ruleId = $widgetData['rule_id']){
				$model->setRuleId($ruleId);
				$model->setCartRuleId($ruleId); //old versions had this field instead ruleId                           
			}elseif($widgetData['coupon_code']){
				$model->setCouponCode($coupon);
			}     
			if($widgetData['apply_coupon']==1){        
				$model->setApplyCoupon(1);                      
			} 
          
			$expiration = null;                      
                  
			if($widgetData['coupon_expiration']=='inherit' && $widgetData['cpnExpInherit']){
				$expiration = date("Y-m-d H:i:s",$this->timezone->date()->getOffset()+time()+$widgetData['cpnExpInherit']);
			}elseif($widgetData['coupon_expiration']){
				$expiration = date("Y-m-d H:i:s",$this->timezone->date()->getOffset()+time()+($widgetData['coupon_expiration']*60));
			}
			if($widgetData['coupon_limit_ip']==1){
				$model->setUserIp($_SERVER['REMOTE_ADDR']);            
				$model->setPopupCookieId($_popup->getData('cookie_id'));
			}          
			$model->setExpirationDate($expiration);
			$model->setCouponLength($widgetData['coupon_length']);
			$model->setCouponPrefix($widgetData['coupon_prefix']);                        
                                                
			$model->save();
                           
		}                 
                                         
    $_popup->setPopupData($_popup->getData('popup_id'),'goal_complition',$_popup->getData('goal_complition')+1);
		//dont show coupon if user needs to confirm subscription first
		if($confirmNeed==1 && !$alreadyConfirmed) 
			$coupon = ''; 
		else 
			$this->_helper->setPopupCookie('isSubscribed',1);
		
		$response = json_encode(array('success' => 'success', 'coupon' => $coupon));
 
        
		$resultRaw = $this->resultRawFactory->create();
		return $resultRaw->setContents($response);        
                     
	}
}