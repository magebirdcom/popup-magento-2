<?php

namespace Magebird\Popup\Controller\Index;
use Magento\Framework\App\Action\Context; 

class Error extends \Magento\Framework\App\Action\Action
{
    protected $resourceConfig;    
    public function __construct(
    Context $context, 
    \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ){
        $this->resourceConfig = $resourceConfig;
        return parent::__construct($context);
    }  
       
	
    public function execute()
    {
        $this->resourceConfig->saveConfig('magebird_popup/general/network_error', 1,'default',0);
    }
}
