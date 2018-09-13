<?php

namespace Magebird\Popup\Controller\Index;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action\Context; 

class Preview extends \Magento\Framework\App\Action\Action
{
    protected $pageFactory;
    public function __construct(Context $context, PageFactory $pageFactory) {
        $this->pageFactory = $pageFactory;
        return parent::__construct($context);
    }
	
    public function execute()
    {
        $page_object = $this->pageFactory->create();
        $page_object->getConfig()->getTitle()->set(__('Popup Preview'));
        return $page_object;
    }
}
