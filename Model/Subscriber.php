<?php
namespace Magebird\Popup\Model;


class Subscriber extends \Magento\Framework\Model\AbstractModel{
	protected $_transportBuilder;
	protected $scopeConfig;
	protected $dir;
	protected $request;
	protected $_storeManager;
  protected $mailchimp;
	public function __construct(     
		\Magento\Framework\Model\Context $context,
		\Magento\Framework\Registry $registry,
    \Magebird\Popup\Lib\Mailchimp\MailChimp $mailchimp,
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
    $this->mailchimp = $mailchimp;
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
    

  
    
	function subscribeMailchimp($listId,$email,$firstName,$lastName,$coupon){ 
    $api = $this->mailchimp;
		$api->setApiKey($this->scopeConfig->getValue('magebird_popup/services/mailchimp_key'));		
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