<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Grid extends \Magento\Backend\App\Action{


    protected $resultPageFactory;

    public function __construct(Context $context,PageFactory $resultPageFactory) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }

    public function execute()
    {
        $resultLayout = $this->resultPageFactory->create();

        return $resultLayout;
    }
}