<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magebird\Popup\Controller\Index;

use Magento\Customer\Model\Account\Redirect as AccountRedirect;
use Magento\Customer\Api\Data\AddressInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\App\Action\Context;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Helper\Address;
use Magento\Framework\UrlFactory;
use Magento\Customer\Model\Metadata\FormFactory;
use Magento\Newsletter\Model\SubscriberFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Model\Url as CustomerUrl;
use Magento\Customer\Model\Registration;
use Magento\Framework\Escaper;
use Magento\Customer\Model\CustomerExtractor;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Exception\InputException;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Register extends \Magento\Framework\App\Action\Action
{
    protected $_popup;
    protected $_popupSubscriber; 
    protected $_helper;
    protected $cart;
    /** @var AccountManagementInterface */
    protected $accountManagement;

    /** @var Address */
    protected $addressHelper;

    /** @var FormFactory */
    protected $formFactory;

    /** @var SubscriberFactory */
    protected $subscriberFactory;

    /** @var RegionInterfaceFactory */
    protected $regionDataFactory;

    /** @var AddressInterfaceFactory */
    protected $addressDataFactory;

    /** @var Registration */
    protected $registration;

    /** @var CustomerInterfaceFactory */
    protected $customerDataFactory;

    /** @var CustomerUrl */
    protected $customerUrl;

    /** @var Escaper */
    protected $escaper;

    /** @var CustomerExtractor */
    protected $customerExtractor;

    /** @var \Magento\Framework\UrlInterface */
    protected $urlModel;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /**
     * @var Session
     */
    protected $session;

    /**
     * @var AccountRedirect
     */
    private $accountRedirect;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private $cookieMetadataFactory;

    /**
     * @var \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private $cookieMetadataManager;

    /**
     * @param Context $context
     * @param Session $customerSession
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     * @param AccountManagementInterface $accountManagement
     * @param Address $addressHelper
     * @param UrlFactory $urlFactory
     * @param FormFactory $formFactory
     * @param SubscriberFactory $subscriberFactory
     * @param RegionInterfaceFactory $regionDataFactory
     * @param AddressInterfaceFactory $addressDataFactory
     * @param CustomerInterfaceFactory $customerDataFactory
     * @param CustomerUrl $customerUrl
     * @param Registration $registration
     * @param Escaper $escaper
     * @param CustomerExtractor $customerExtractor
     * @param DataObjectHelper $dataObjectHelper
     * @param AccountRedirect $accountRedirect
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magebird\Popup\Model\Subscriber $popupSubscriber,
        \Magebird\Popup\Model\Popup $popup,
        \Magebird\Popup\Helper\Data $helper,
        \Magebird\Popup\Helper\Coupon $cpnHelper,
        \Magento\Framework\Registry $registry,   
        \Magento\Checkout\Model\Cart $cart,
        Context $context,
        Session $customerSession,
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager,
        AccountManagementInterface $accountManagement,
        Address $addressHelper,
        UrlFactory $urlFactory,
        FormFactory $formFactory,
        SubscriberFactory $subscriberFactory,
        RegionInterfaceFactory $regionDataFactory,
        AddressInterfaceFactory $addressDataFactory,
        CustomerInterfaceFactory $customerDataFactory,
        CustomerUrl $customerUrl,
        Registration $registration,
        Escaper $escaper,
        CustomerExtractor $customerExtractor,
        DataObjectHelper $dataObjectHelper,
        AccountRedirect $accountRedirect
    ) {
        $this->_popup = $popup;
        $this->_popupSubscriber = $popupSubscriber;
        $this->_helper = $helper;
        $this->_cpnHelper = $cpnHelper;
        $this->cart = $cart;
        $registry->register('isSecureArea', true);
        $this->session = $customerSession;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->accountManagement = $accountManagement;
        $this->addressHelper = $addressHelper;
        $this->formFactory = $formFactory;
        $this->subscriberFactory = $subscriberFactory;
        $this->regionDataFactory = $regionDataFactory;
        $this->addressDataFactory = $addressDataFactory;
        $this->customerDataFactory = $customerDataFactory;
        $this->customerUrl = $customerUrl;
        $this->registration = $registration;
        $this->escaper = $escaper;
        $this->customerExtractor = $customerExtractor;
        $this->urlModel = $urlFactory->create();
        $this->dataObjectHelper = $dataObjectHelper;
        $this->accountRedirect = $accountRedirect;
        parent::__construct($context);
    }

    /**
     * Retrieve cookie manager
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\PhpCookieManager
     */
    private function getCookieManager()
    {  
        if (!$this->cookieMetadataManager) {
            $this->cookieMetadataManager = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\PhpCookieManager::class
            );
        }
        return $this->cookieMetadataManager;
    }

    /**
     * Retrieve cookie metadata factory
     *
     * @deprecated
     * @return \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory
     */
    private function getCookieMetadataFactory()
    {
        if (!$this->cookieMetadataFactory) {
            $this->cookieMetadataFactory = \Magento\Framework\App\ObjectManager::getInstance()->get(
                \Magento\Framework\Stdlib\Cookie\CookieMetadataFactory::class
            );
        }
        return $this->cookieMetadataFactory;
    }

    /**
     * Add address to customer during create account
     *
     * @return AddressInterface|null
     */
    protected function extractAddress()
    {
         

        $addressForm = $this->formFactory->create('customer_address', 'customer_register_address');
        $allowedAttributes = $addressForm->getAllowedAttributes();

        $addressData = [];

        $regionDataObject = $this->regionDataFactory->create();
        foreach ($allowedAttributes as $attribute) {
            $attributeCode = $attribute->getAttributeCode();
            $value = $this->getRequest()->getParam($attributeCode);
            if ($value === null) {
                continue;
            }
            switch ($attributeCode) {
                case 'region_id':
                    $regionDataObject->setRegionId($value);
                    break;
                case 'region':
                    $regionDataObject->setRegion($value);
                    break;
                default:
                    $addressData[$attributeCode] = $value;
            }
        }
        $addressDataObject = $this->addressDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $addressDataObject,
            $addressData,
            '\Magento\Customer\Api\Data\AddressInterface'
        );
        $addressDataObject->setRegion($regionDataObject);

        $addressDataObject->setIsDefaultBilling(
            $this->getRequest()->getParam('default_billing', false)
        )->setIsDefaultShipping(
            $this->getRequest()->getParam('default_shipping', false)
        );
        return $addressDataObject;
    }

    /**
     * Create customer account action
     *
     * @return void
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {

        if($this->getRequest()->getParam('emailCheck')!='') return;
        if($this->getRequest()->getParam('emailCheck2')!='') return;
        
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($this->session->isLoggedIn()) {
            $ajaxExceptions['exceptions'][] = __("You are already logged in.");
            $response = json_encode($ajaxExceptions);
            $this->getResponse()->setBody($response); 
            return;   
        }

        $this->session->regenerateId();

        try {
            $popupId = $this->getRequest()->getParam('popupId');
            $_popup = $this->_popup->load($popupId);    
            $data1  = $this->_helper->getWidgetData($_popup->getPopupContent(),$this->getRequest()->getParam('widgetId'));
            $data2  = $this->getRequest()->getParams();
            $data   = array_merge($data1,$data2);
            
            $data['cpnExpInherit'] = $this->getRequest()->getParam('cpnExpInherit');
            $confirmNeed     = false;    
            $magentoNative   = $this->scopeConfig->getValue('magebird_popup/services/enablemagento');
            if(isset($data['newsletter_option']) && $data['newsletter_option']==1) $data['is_subscribed']=1;          
        
            $address = $this->extractAddress();
            $addresses = $address === null ? [] : [$address];

            $customer = $this->customerExtractor->extract('customer_account_create', $this->_request);
            //$customer->setAddresses($addresses);

            $password = $this->getRequest()->getParam('password');
            $confirmation = $this->getRequest()->getParam('password');
            $redirectUrl = $this->session->getBeforeAuthUrl();

            $this->checkPasswordConfirmation($password, $confirmation);

            $customer = $this->accountManagement
                ->createAccount($customer, $password, $redirectUrl);

            if ($this->getRequest()->getParam('is_subscribed', false) && $magentoNative) {
                $this->subscriberFactory->create()->subscribeCustomerById($customer->getId());
            }

            $this->_eventManager->dispatch(
                'customer_register_success',
                ['account_controller' => $this, 'customer' => $customer]
            );

            $confirmationStatus = $this->accountManagement->getConfirmationStatus($customer->getId());
            if ($confirmationStatus === AccountManagementInterface::ACCOUNT_CONFIRMATION_REQUIRED) {
                $email = $this->customerUrl->getEmailConfirmationUrl($customer->getEmail());
                    $ajaxExceptions['exceptions'][] = __(
                        'You must confirm your account. Please check your email for the confirmation link or <a href="%1">click here</a> for a new link.',
                        $email
                    );
                    $response = json_encode($ajaxExceptions);
                    $this->getResponse()->setBody($response); 
                    return;   
            } else {
                $this->session->setCustomerDataAsLoggedIn($customer);
                //$this->messageManager->addSuccess($this->getSuccessMessage());
            }
          
            if ($this->getCookieManager()->getCookie('mage-cache-sessid')) {
                $metadata = $this->getCookieMetadataFactory()->createCookieMetadata();
                $metadata->setPath('/');
                $this->getCookieManager()->deleteCookie('mage-cache-sessid', $metadata);
            }

            $coupon = '';
            $email = (string) $this->getRequest()->getParam('email');
            $data['coupon_option'] = isset($data['coupon_option']) ? $data['coupon_option'] : null;
            if($data['coupon_option']==1 || ($data['coupon_option']==2 && isset($data['is_subscribed']) && $data['is_subscribed'])){
              if(isset($data['coupon_code']) && $data['coupon_code']){
                $coupon = $data['coupon_code'];
              }elseif(isset($data['rule_id']) && $data['rule_id']){
                $coupon = $this->_cpnHelper->generateCoupon($data,$popupId);                                   
              }                      
            }
            
            if(isset($data['send_coupon']) && $data['send_coupon']==1 && $coupon){
              $this->_popupSubscriber->mailCoupon($email,$coupon);                                                                 
            }                            
           
            //if apply coupon to cart automatically
            if(isset($data['apply_coupon']) && $data['apply_coupon']==1){        
        				$this->session->setData("coupon_code",$coupon);                  
        				$this->cart->getQuote()->setCouponCode($coupon)->save();
        				$this->cart->getQuote()->setTotalsCollectedFlag(false)->collectTotals()->save();  
        				$this->cart->getQuote()->collectTotals()->save();                              
            }       
                                 
            $this->subscribeNewsletter($data,$coupon);
           

            $_popup->setPopupData($_popup->getData('popup_id'),'goal_complition',$_popup->getData('goal_complition')+1);
            $response = json_encode(array('success' => 'success', 'coupon' => $coupon));
            $this->getResponse()->setBody($response); 
            return;
        } catch (StateException $e) {
            $url = $this->urlModel->getUrl('customer/account/forgotpassword');
            // @codingStandardsIgnoreStart
            $message = __(
                'There is already an account with this email address. If you are sure that it is your email address, <a href="%1">click here</a> to get your password and access your account.',
                $url
            );
            $ajaxExceptions['exceptions'][] = $message;
            $response = json_encode($ajaxExceptions);
            $this->getResponse()->setBody($response); 
            return;   
        } catch (InputException $e) {
            $message = $this->escaper->escapeHtml($e->getMessage());
            foreach ($e->getErrors() as $error) {
                $message.=$this->escaper->escapeHtml($error->getMessage());
            }
            $ajaxExceptions['exceptions'][] = $message;
            $response = json_encode($ajaxExceptions);
            $this->getResponse()->setBody($response); 
            return;               
        } catch (LocalizedException $e) {
            $ajaxExceptions['exceptions'][] = $e->getMessage();
            $response = json_encode($ajaxExceptions);
            $this->getResponse()->setBody($response); 
            return;           
        } catch (\Exception $e) {
            $ajaxExceptions['exceptions'][] = $e->getMessage();
            $response = json_encode($ajaxExceptions);
            $this->getResponse()->setBody($response); 
            return;           
        }
    }

    /**
     * Make sure that password and password confirmation matched
     *
     * @param string $password
     * @param string $confirmation
     * @return void
     * @throws InputException
     */
    protected function checkPasswordConfirmation($password, $confirmation)
    {
        if ($password != $confirmation) {
            throw new InputException(__('Please make sure your passwords match.'));
        }
    }

    /**
     * Retrieve success message
     *
     * @return string
     */
    protected function getSuccessMessage()
    {
        if ($this->addressHelper->isVatValidationEnabled()) {
            if ($this->addressHelper->getTaxCalculationAddressType() == Address::TYPE_SHIPPING) {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your shipping address for proper VAT calculation.',
                    $this->urlModel->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            } else {
                // @codingStandardsIgnoreStart
                $message = __(
                    'If you are a registered VAT customer, please <a href="%1">click here</a> to enter your billing address for proper VAT calculation.',
                    $this->urlModel->getUrl('customer/address/edit')
                );
                // @codingStandardsIgnoreEnd
            }
        } else {
            $message = __('Thank you for registering with %1.', $this->storeManager->getStore()->getFrontendName());
        }
        return $message;
    }

    public function subscribeNewsletter($data,$coupon){
  
        $mailchimpListId = isset($data['mailchimp_list_id']) ? $data['mailchimp_list_id'] : '';
        $getResponseListToken = isset($data['gr_campaign_token']) ? $data['gr_campaign_token'] : '';
        $campaignMonitorId = isset($data['cm_list_id']) ? $data['cm_list_id'] : '';    
        $mailChimpOption = $this->scopeConfig->getValue('magebird_popup/services/mailchimp_option');
        $mailchimp       = $this->scopeConfig->getValue('magebird_popup/services/enablemailchimp');
        $campaignMonitor = $this->scopeConfig->getValue('magebird_popup/services/enablecampaignmonitor');
        $getResponse     = $this->scopeConfig->getValue('magebird_popup/services/enablegetresponse');   
        //Mailchimp subscription
        if($mailchimpListId && $mailchimp){
            $api = $this->_popupSubscriber->subscribeMailchimp($mailchimpListId,$data['email'],$data['firstname'],$data['lastname'],$coupon);            
            if($api->errorCode){
              //$ajaxExceptions['exceptions'][] = $api->errorMessage;
              //$response = json_encode($ajaxExceptions);                
              //return $response;           
            }                                                                                                    
        } 
        
        //Campaign monitor subscription
        if($campaignMonitorId && $campaignMonitor){
            $result = $this->_popupSubscriber->subscribeCampaignMonitor($campaignMonitorId,$data['email'],$data['firstname'],$data['lastname'],$coupon);
            //echo "Result of POST /api/v3.1/subscribers/{list id}.{format}\n<br />";
            if(!$result->was_successful()) {
                $ajaxExceptions['exceptions'][] = 'Failed with code '.$result->http_status_code;
                //$response = json_encode($ajaxExceptions);
                //$this->getResponse()->setBody($response);  
                //return $response;   
            }                                                                                                     
        } 
        
        //GetResponse subscription
        if($getResponseListToken && $getResponse){
            $api = $this->_popupSubscriber->subscribeGetResponse($getResponseListToken,$data['email'],$data['firstname'],$data['lastname'],$coupon);
            if(isset($api->errorCode) && $api->errorCode){
              $ajaxExceptions['exceptions'][] = $api->errorMessage;
              //$response = json_encode($ajaxExceptions);
              //$this->getResponse()->setBody($response);  
              //return $response;           
            }                                                                                                    
        }
        return '';     
    }  
    
}
