<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

class ClearCache extends \Magento\Backend\App\Action
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
        $this->popup->parsePopupContent();
        $this->messageManager->addSuccess(__('Popup cache has been cleared.')); 
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('*/*/');
    }
}
