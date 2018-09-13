<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

class MassReset extends \Magento\Backend\App\Action
{
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\ResourceConnection $resource
    ) {
        $this->_resource = $resource;
        parent::__construct($context);
    }
    
    public function execute()
    {
        $ids = $this->getRequest()->getParam('popup');
        try {
          foreach($ids as $id) {  
                  $popup = $this->_objectManager->create('Magebird\Popup\Model\Popup');
                  $popup->load($id);
                  $popup->setViews(0);
                  $popup->setPopupClosed(0);
                  $popup->setClickInside(0);
                  $popup->setWindowClosed(0);
                  $popup->setTotalTime(0);
                  $popup->setPageReloaded(0);
                  $popup->setGoalComplition(0);
                  $popup->save();  
                  $this->connection = $this->_resource->getConnection('core_write');                                           
                  $query = "UPDATE ".$this->_resource->getTableName('magebird_popup_stats')." 
                          SET visitors=0,total_carts=0,popup_carts=0,purchases=0,popup_visitors=0,popup_purchases=0 
                          WHERE popup_id=".intval($id); 
                  $this->connection->query($query); 
                  $query = "DELETE FROM ".$this->_resource->getTableName('magebird_popup_orders')." 
                          WHERE popup_id=".intval($id); 
                  $this->connection->query($query);                                                                     
          }
          $this->messageManager->addSuccess(__('The popups have been deleted.'));
        } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
        } 
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
