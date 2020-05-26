<?php
        
namespace Magebird\Popup\Block\Adminhtml;

class Notifications extends \Magento\Framework\View\Element\Template
{
    protected $_scopeConfig;
    protected $_resource;
    protected $_resourceConfig;
    protected $_moduleList;
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        array $data = []
    ) {
        $this->_resource = $resource;
        $this->_resourceConfig = $resourceConfig; 
        $this->_moduleList  = $moduleList;
        $this->_scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }
    
    //Required for important critical bugfixes
    public function getNotifications(){
      $lastCheck = $this->_scopeConfig->getValue('magebird/notifications/last_check');
      
      if(!$lastCheck || $lastCheck<strtotime('-7 days')){        
        $this->requestNotifications($lastCheck);
        $this->_resourceConfig->saveConfig('magebird/notifications/last_check',time(),'default',0);
      }
      
      $tableName = $this->_resource->getTableName('magebird_notifications');
      $query = "SELECT * FROM $tableName WHERE dismissed <> 1;";
      $connection = $this->_resource->getConnection();
      $results = $connection->fetchAll($query);
      return $results;
    }
    
    protected function requestNotifications($lastCheck){
      $version = $this->getExtensionVersion();
      $data=http_build_query(array("extension"=>"popup2","version"=>$version,"domain"=>$_SERVER['HTTP_HOST'],"lastCheck"=>$lastCheck));
      if(function_exists('curl_version')){
        $ch = curl_init();  
        curl_setopt($ch, CURLOPT_URL, "https://www.magebird.com/notifications/check.php?".$data); 
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
        $resp = curl_exec($ch); 
        curl_close($ch);               
      } 
          
      if($resp==null){
        $headers  = "Content-type: application/x-www-form-urlencoded\r\nContent-Length: ".strlen($data)."\r\n";
        $options = array("http" => array("method"=>"POST","header"=>$headers,"content"=>$data));
        $context = stream_context_create($options); 
        $resp=file_get_contents("https://www.magebird.com/notifications/check.php",false,$context,0,10000);              
      } 
      
      
      $notifs = json_decode($resp);
      if(!$notifs) return;
      foreach($notifs as $notif){
        $values[]= "(?,?,?)";
        $binds[]= $notif->origin_id;
        $binds[]= $notif->is_critical;
        $binds[]= $notif->notification; 
      }
      
      $tableName = $this->_resource->getTableName('magebird_notifications');      
      $sql = "INSERT IGNORE INTO $tableName (origin_id,is_critical,notification) VALUES ".implode(",",$values).";";
      $connection = $this->_resource->getConnection();
      $connection->query($sql,$binds);                            
    }
    
    protected function getExtensionVersion(){
        $moduleCode = 'Magebird_Popup';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }     
}
