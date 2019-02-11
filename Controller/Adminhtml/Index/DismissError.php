<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;
use Magento\Framework\App\Action\Context; 

class DismissError extends \Magento\Backend\App\Action
{
    protected $resourceConfig;    
    public function __construct(    
      \Magento\Backend\App\Action\Context $context,
      \Magento\Config\Model\ResourceModel\Config $resourceConfig,
      \Magebird\Popup\Model\Popup $popup
      )
    {
        $this->resourceConfig = $resourceConfig;
        $this->popup = $popup;  
        parent::__construct($context);        
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }

    public function execute()
    {
        $this->resourceConfig->saveConfig('magebird_popup/general/network_error', 0,'default',0);              
    }
}
