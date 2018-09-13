<?php

namespace Magebird\Popup\Model;

class Popup extends \Magento\Framework\Model\AbstractModel
{   
    protected function _construct()
    {
        $this->_init('Magebird\Popup\Model\ResourceModel\Popup');
    }
    
    public function setPopupData($id,$field,$value){
      $om = \Magento\Framework\App\ObjectManager::getInstance();
      $resource = $om->get('Magento\Framework\App\ResourceConnection');
                          
      $id = intval($id);
      $tableName = $resource->getTableName('magebird_popup');      
      $query = "UPDATE `{$tableName}` SET `$field`=:value WHERE popup_id=$id";       
      $binds = array('value' => $value);
      $resource->getConnection()->query($query, $binds);    
    }    
    
    public function parsePopupContent($popupId=null,$isTemplate=false){

        $om = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $om->get('Magento\Store\Model\StoreManagerInterface');
        $appEmulation = $om->get('Magento\Store\Model\App\Emulation');
        $filterProvider = $om->get('Magento\Cms\Model\Template\FilterProvider');
        $resource = $om->get('Magento\Framework\App\ResourceConnection');
        $stores = $storeManager->getStores();
        
        if($isTemplate){
          $_popups = $om->get('Magebird\Popup\Model\Template')->getCollection();
          $idName = "template_id";
        }else{
          $idName = "popup_id";
          $_popups = $om->get('Magebird\Popup\Model\Popup')->getCollection();         
          if($popupId){                    
            $_popups->addFieldToFilter('popup_id',$popupId);                     
          }          
        }
             
        $isTemplate = intval($isTemplate);       
        foreach($stores as $store){
            $storeId = $store->getData('store_id'); 
            $appEmulation->startEnvironmentEmulation($storeId,\Magento\Framework\App\Area::AREA_FRONTEND, true);   
            foreach($_popups as $_popup){            
              $content = $_popup->getData('popup_content');
              //success_msg is encoded and filter( function doesnt work properly for that
              $successMsg = explode('success_msg="',$content);
              $isSuccessMsg = false;
              if(isset($successMsg[1])){
                $successMsg = explode('"',$successMsg[1]);
                $successMsg = $successMsg[0];
                $content = str_replace($successMsg, "temp_replace", $content);
                $isSuccessMsg = true;
              }                            
              $parsed = $filterProvider->getPageFilter()->filter($content);
              if($isSuccessMsg){
                $parsed = str_replace("temp_replace",$successMsg,$parsed);
              }
              $query = "INSERT INTO ".$resource->getTableName('magebird_popup_content')." (popup_id,store_id,content,is_template) 
                        VALUES (".$_popup->getData($idName).",$storeId,:value,$isTemplate) ON DUPLICATE KEY UPDATE content = VALUES(content)";            
              $bind = array('value'=>$parsed);       
              $resource->getConnection()->query($query, $bind);                 
            }
            
            $appEmulation->stopEnvironmentEmulation();
            if($isTemplate) break; //we need only one store  
        }                                
           
    }               

}
