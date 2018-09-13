<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    public function __construct(Context $context,PageFactory $resultPageFactory) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }

    /**
     * Popupp List action
     *
     * @return void
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu(
            'Magebird_Popup::popup_manager'
        )->addBreadcrumb(
            __('Popup'),
            __('Popup')
        )->addBreadcrumb(
            __('Manage Popup'),
            __('Manage Popup')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Popup'));
        return $resultPage;
    }
}