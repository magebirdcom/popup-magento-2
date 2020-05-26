<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * System config email field backend model
 */
namespace Magebird\Popup\Model\Config\Source;

use Magento\Framework\Exception\LocalizedException;

class Config extends \Magento\Framework\App\Config\Value
{
    protected $_messageManager;
    protected $resourceConfig; 
    protected $config;
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,                                   
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,        
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        array $data = []                
    ) {
        $this->_messageManager = $messageManager;    
        $this->config = $config;
        $this->resourceConfig = $resourceConfig;        
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }
    
    public function beforeSave()
    {        
        $extensionKey = $this->_config->getValue($this->getPath());        
        $submitedVal = $this->getValue();
        if(!$submitedVal) return;
        if(!empty($submitedVal) && empty($extensionKey)){
            $resp=null;   
            $data=http_build_query(array("licence_name"=>"Popup M2","extension"=>"Popup M2","licence_key"=>$this->getValue(),"domain"=>$_SERVER['HTTP_HOST'],"affId"=>0));
            if(function_exists('curl_version')){
              $ch = curl_init();  
              curl_setopt($ch, CURLOPT_URL, "https://www.magebird.com/licence/check.php?".$data); 
              curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
              curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
              $resp = curl_exec($ch); 
              curl_close($ch);               
            }     
            if($resp==null){
              $headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
              $options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
              $context = stream_context_create($options); 
              $resp=file_get_contents("https://www.magebird.com/licence/check.php",false,$context,0,100);              
            }   
            
            if($resp==null){
              throw new LocalizedException(__('Can not validate the licence key. Please <a href="http://www.magebird.com/contacts">contact us</a>.'));
            }elseif($resp!=1){
              throw new LocalizedException(__($resp));
            }else{
              $this->resourceConfig->saveConfig('magebird_popup/general/extension_key', $this->getValue(),'default',0);            
            }
        } 
        return $this;
    }
}
