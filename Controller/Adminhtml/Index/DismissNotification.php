<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

class DismissNotification extends \Magento\Backend\App\Action
{

    public function __construct(    
      \Magento\Backend\App\Action\Context $context,
      \Magebird\Popup\Model\Popup $popup
      )
    {
        $this->popup = $popup;  
        parent::__construct($context);        
    }
    
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
                                           
        $query = "UPDATE " . $connection->getTableName('magebird_notifications') . " 
                SET dismissed=1 WHERE id=" . intval($id);
        $connection->query($query);           
    }
}
