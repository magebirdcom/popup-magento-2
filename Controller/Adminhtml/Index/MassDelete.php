<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

class MassDelete extends \Magento\Backend\App\Action
{
    public function execute()
    {
        $ids = $this->getRequest()->getParam('popup');
        try {
          foreach($ids as $id) {  
                  $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');
                  $model->load($id);
                  $model->delete();                                                                     
          }
          $this->messageManager->addSuccess(__('The popups have been deleted.'));
        } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
        } 
        $resultRedirect = $this->resultRedirectFactory->create();

        return $resultRedirect->setPath('*/*/');
    }
}
