<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

class Delete extends \Magento\Backend\App\Action
{

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('popup_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            try {
                $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');
                $model->load($id);
                $model->delete();
                $this->messageManager->addSuccess(__('The popup has been deleted.'));                
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
            return $resultRedirect->setPath('*/*/');
        }
        return $resultRedirect->setPath('*/*/');
    }
}
