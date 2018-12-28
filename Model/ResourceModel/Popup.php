<?php

namespace Magebird\Popup\Model\ResourceModel;
use GetResponse;
use CS_REST_Lists;
use MCAPI;
use \Magebird\MailChimp;

/**
 * Popup Resource Model
 */
class Popup extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $dir; 
    protected $scopeConfig;
    protected $_messageManager;
    protected $request;
    public function __construct(     
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\App\Filesystem\DirectoryList $dir,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\Request\Http $request,
        $resourcePrefix = null    
    ) {
        parent::__construct($context, $resourcePrefix);
        $this->dir = $dir;
        $this->messageManager = $messageManager;
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }
    
    protected function _construct()
    {
        $this->_init('magebird_popup', 'popup_id');
    }
    
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
          $action     = $this->request->getActionName();
          if($action=='massStatus' || $action=='massReset') return;              
          $this->mailchimpVars($object->getData('popup_content'));
          $this->getResponseCustoms($object->getData('popup_content'));
          $this->campaignMonitorCustoms($object->getData('popup_content'));
          $pages = $object->getData('pages');            
          if(!$pages || in_array(6,$pages)===FALSE){ 
            $object->setSpecifiedUrl('');
          }else{
            $url = $object->getSpecifiedUrl();
            $url = str_replace(array("http://","https://","index.php/"),'',$url);            
            $object->setSpecifiedUrl($url);
          }
          
          $url = $object->getSpecifiedNotUrl();          
          $url = str_replace(array("http://","https://","index.php/"),'',$url);
          $object->setSpecifiedNotUrl($url);    

          $url = $object->getIfReferral();          
          $url = str_replace(array("http://","https://","index.php/"),'',$url);
          $object->setIfReferral($url);            

          $url = $object->getIfNotReferral();          
          $url = str_replace(array("http://","https://","index.php/"),'',$url);
          $object->setIfNotReferral($url);  
                              
          if(!$pages || in_array(2,$pages)===FALSE){
            $object->setProductIds('');
          }
          
          if(!$pages || in_array(3,$pages)===FALSE){
            $object->setCategoryIds('');
          }                      
          $object->setCookieId(str_replace(array("|","=",",",":"),"",$object->getCookieId()));                  
          $content = $object->getPopupContent();
          //to make widgets nicely centered
          if(strpos($content,'<p style="text-align: center;">{{')!==false){
            $content = str_replace('<p style="text-align: center;">{{','<div style="text-align: center;">{{',$content);
            $content = str_replace('}}</p>','}}</div>',$content);
            $object->setPopupContent($content);          
          }           
          return parent::_beforeSave($object);
    }
    
      protected function _afterSave(\Magento\Framework\Model\AbstractModel $object) {
          $action     = $this->request->getActionName();
          if($action=='massStatus' || $action=='massReset') return;
                 
          $condition = ['popup_id = ?' => (int)$object->getId()];
          $updateTables = array(
            array('name'=>'magebird_popup_day','dataName'=>'day','field'=>'day'),
            array('name'=>'magebird_popup_store','dataName'=>'stores','field'=>'store_id'),
            array('name'=>'magebird_popup_page','dataName'=>'pages','field'=>'page_id'),
            array('name'=>'magebird_popup_product','dataName'=>'product_ids','field'=>'product_id'),
            array('name'=>'magebird_popup_category','dataName'=>'category_ids','field'=>'category_id'),
            array('name'=>'magebird_popup_customer_group','dataName'=>'customer_group','field'=>'customer_group_id'),
            array('name'=>'magebird_popup_country','dataName'=>'country_ids','field'=>'country_id'),
            array('name'=>'magebird_popup_notcountry','dataName'=>'not_country_ids','field'=>'country_id'),
            array('name'=>'magebird_popup_referral','dataName'=>'if_referral','field'=>'referral'),
            array('name'=>'magebird_popup_notreferral','dataName'=>'if_not_referral','field'=>'not_referral')
          );

          foreach($updateTables as $key => $table){
            $this->getConnection()->delete($this->getTable($table['name']), $condition);
                         
            if (!$object->getData($table['dataName'])) {
                $object->setData($table['dataName'], array(0));
                $objData = array();
            }elseif($table['dataName']=='product_ids' || $table['dataName']=='category_ids' 
              || $table['dataName']=='country_ids' || $table['dataName']=='not_country_ids'){
              $objData = explode(",",$object->getData($table['dataName']));
            }elseif($table['dataName']=='if_referral' || $table['dataName']=='if_not_referral'){
              $objData = explode(",,",$object->getData($table['dataName']));
            }else{
              $objData = $object->getData($table['dataName']);
            }
            
            //we need to insert row with value 0 because inner join is used in where sql sentence
            if($table['dataName']=='stores' || $table['dataName']=='product_ids' || $table['dataName']=='category_ids' || $table['dataName']=='pages'){
              if(empty($objData)){
                $objData[] = 0;
              }
            } 
                          
            $this->getConnection()->delete($this->getTable($table['name']), $condition);  
            foreach ((array) $objData as $value) {          
                $valueArray = array();
                $valueArray['popup_id'] = $object->getId();
                $valueArray[$table['field']] = $value;
                $this->getConnection()->insert($this->getTable($table['name']), $valueArray); 
            }                                           
          } 
          
          $tableName = $this->getTable('magebird_popup_stats');        
          $popupId = intval($object->getId());
          $query = "INSERT IGNORE INTO `{$tableName}` (popup_id) VALUE ($popupId)";       
          $this->getConnection()->query($query);           

          $object->parsePopupContent($object->getId()); 
          return parent::_afterSave($object);
      }  
      
      protected function _beforeDelete(\Magento\Framework\Model\AbstractModel $object) {
  		    $storeTable = $this->getTable('magebird_popup_store');
          $this->getConnection()->delete($storeTable, 'popup_id=' . $object->getId());
          
  		    $storeTable = $this->getTable('magebird_popup_content');
          $this->getConnection()->delete($storeTable, 'popup_id='.$object->getId().' AND is_template=0');          
      }
            
      protected function _afterLoad(\Magento\Framework\Model\AbstractModel $object) {    
          $fields = array(
            array('table'=>'magebird_popup_day','dataName'=>'day','field'=>'day'),
            array('table'=>'magebird_popup_store','dataName'=>'store_id','field'=>'store_id'),
            array('table'=>'magebird_popup_page','dataName'=>'page_id','field'=>'page_id'),
            array('table'=>'magebird_popup_product','dataName'=>'product_ids','field'=>'product_id'),
            array('table'=>'magebird_popup_category','dataName'=>'category_ids','field'=>'category_id'),
            array('table'=>'magebird_popup_customer_group','dataName'=>'customer_group','field'=>'customer_group_id'),
            array('table'=>'magebird_popup_country','dataName'=>'country_ids','field'=>'country_id'),
            array('table'=>'magebird_popup_notcountry','dataName'=>'not_country_ids','field'=>'country_id'),
            array('table'=>'magebird_popup_referral','dataName'=>'if_referral','field'=>'referral'),
            array('table'=>'magebird_popup_notreferral','dataName'=>'if_not_referral','field'=>'not_referral')
          );
        foreach($fields as $field){
            $select = $this->getConnection()->select()
                              ->from($this->getTable($field['table']),$field['field'])
                              ->where('popup_id = ?',$object->getId()
            );
            $stores = $this->getConnection()->fetchCol($select);
            if($stores){
                  $values = array();
                  foreach ($stores as $row) {
                      $values[] = $row;
                  }
                  if($field['dataName']=='product_ids' || $field['dataName']=='category_ids' 
                    || $field['dataName']=='country_ids' || $field['dataName']=='not_country_ids'){
                    $values = implode(",",$values);
                  }elseif($field['dataName']=='if_referral' || $field['dataName']=='if_not_referral'){
                    $values = implode(",,",$values);
                  }       
       
                  $object->setData($field['dataName'], $values);        
            }        
          }

          //$object->setData('store_id', array(1,3));
          return parent::_afterLoad($object);
      }         
    
      function mailchimpVars($content){                
          $explode = explode('mailchimp_list_id="',$content);
          if(!isset($explode[1])) return;
         
          require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Mailchimp/MailChimp.php');
          $api = new MailChimp($this->scopeConfig->getValue('magebird_popup/services/mailchimp_key'));            
          $mailchimpListId = explode('"',$explode[1]);
          $mailchimpListId = $mailchimpListId[0];          
          if($mailchimpListId){
            $result = $api->get("/lists/$mailchimpListId/merge-fields");
            if(!$result){
              throw new \Exception("Wrong Mailchimp Api Key");
            }  
            if(isset($result['status'])){
             if($result['title']=='Resource Not Found'){
               throw new \Exception("Wrong Mailchimp List id");
             }else{
               throw new \Exception("Mailchimp api error ".$result['detail']);
             } 
             return;            
            }
                         
            $tagExists = false;
            foreach ($result['merge_fields'] as $res) {
                if ($res['tag'] == 'POPUP_COUP') {
                    $tagExists = true;
                    return;
                }
            }
            
            if(!$tagExists){
              $res = $api->post("/lists/$mailchimpListId/merge-fields", array(
                    "tag" => "POPUP_COUP",
                    "required" => false, // or true to set is as required 
                    "name" => "Popup Coupon Code",
                    "type" => "text", // text, number, address, phone, email, date, url, imageurl, radio, dropdown, checkboxes, birthday, zip
                    "default_value" => "", // anything
                    "public" => true, // or false to set it as not 
                ));                          
            }                   
          }              
      }
      
      function getResponseCustoms($content){                
          $explode = explode('gr_campaign_token="',$content);
          if(!isset($explode[1])) return;
          
          require_once($this->dir->getPath('lib_internal') . '/magebird/popup/GetResponse/GetResponseAPI.class.php');
          $api = new GetResponse($this->scopeConfig->getValue('magebird_popup/services/getresponse_key'));            
          $getResponseToken = explode('"',$explode[1]);
          $getResponseToken = $getResponseToken[0];          
          if($getResponseToken){
            $predefines = get_object_vars($api->getCampaignPredefines($getResponseToken));
            if(array_key_exists('code',$predefines)){
              throw new \Exception("GetResponse api error: ".$predefines['message']." or wrong campaign token.");
            }elseif(!in_array('POPUP_COUPON',$predefines)){
              $add = $api->addCampaignPredefine($getResponseToken,'POPUP_COUPON','Popup coupon');       
            }        
          }              
      }  
      
      function campaignMonitorCustoms($content){
          $explode = explode('cm_list_id="',$content);
          if(!isset($explode[1])) return;
                
          require_once($this->dir->getPath('lib_internal') . '/magebird/popup/Campaignmonitor/csrest_lists.php');
          $auth = array('api_key' => $this->scopeConfig->getValue('magebird_popup/services/campaignmonitor_key'));
          $listId = explode('"',$explode[1]);
          $listId = $listId[0];                      
          $wrap = new CS_REST_Lists($listId, $auth);
          
          $result = $wrap->get_custom_fields();
          if(!$result->was_successful()) {          
              throw new \Exception("Campaign Monitor error: ".$result->http_status_code);
          }           
          $customFields = $result->response;
          
          $tagExists = false;           
          foreach($customFields as $field){ 
            if($field->Key=='[POPUP_COUPON]'){
              $tagExists = true;
              return;
            }
          } 
     
          if(!$tagExists){
            $result = $wrap->create_custom_field(array(
                'FieldName' => 'POPUP_COUPON',
                'DataType' => CS_REST_CUSTOM_FIELD_TYPE_TEXT
            ));
          }
          
          if(!$result->was_successful()) {          
              throw new \Exception("Campaign Monitor error: ".$result->http_status_code);
          }      
      }     
        
}
