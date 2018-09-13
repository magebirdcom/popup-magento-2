<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

class MassStatus extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $ids = $this->getRequest()->getParam('popup');  
        $status = $this->getRequest()->getParam('status');  
        try {
          foreach($ids as $id) {  
                  $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');
                  $model->load($id);
                  $model->setStatus($status);
                  $model->save();                                                                     
          }
          $this->messageManager->addSuccess(__('The popups have been updated.'));
        } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
        } 
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
