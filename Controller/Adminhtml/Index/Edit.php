<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Edit extends \Magento\Backend\App\Action
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
	
	/**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Action\Context $context
	 * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(Action\Context $context, \Magento\Framework\View\Result\PageFactory $resultPageFactory, \Magento\Framework\Registry $registry)
    {
        $this->resultPageFactory = $resultPageFactory;
		    $this->_coreRegistry = $registry;
        parent::__construct($context);
    }

    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }


    protected function _initAction()
    {
        // load layout, set active menu and breadcrumbs
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
        return $resultPage;
    }

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $duplicateId = $this->getRequest()->getParam('duplicate_id');
        $templateId = $this->getRequest()->getParam('template_id');        
        if ($id) {
            $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addError(__('This popup no longer exists.'));
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }elseif($templateId){
          $model = $this->_objectManager->create('Magebird\Popup\Model\Template')->load($templateId);              
          $model->setData('title',$model->getData('title'));
          $model->setData('popup_id',null);
                  
          $randString = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(6/strlen($x)) )),1,6);


          $model->setCookieId($randString);
          $model->setCookieTime(10);
          $model->setMaxCountTime(10);
          $model->setDelaytime(0);
          $model->setPriority(1); 
          $content = $model->getData('popup_content');
          //because widget_id must be unique   
          $content = preg_replace_callback('/widget_id="_/',array(get_class($this),'renameWidgetIde'),$content); 
          $model->setData('popup_content',$content);                                   
        }elseif($duplicateId){
          $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');                  
        	$origin = $model->load($duplicateId);   
          $duplicate  = $origin;
          $duplicate->setData('title',$duplicate->getData('title').' copy');
          $content = $duplicate->getData('popup_content');
          //because widget_id must be unique   
          $content = preg_replace_callback('/widget_id="_/',array(get_class($this),'renameWidgetIde'),$content); 
          $duplicate->setData('popup_content',$content);   
          $randString = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(6/strlen($x)) )),1,6);
          $duplicate->setData('cookie_id',$randString);                  
          $duplicate->setData('popup_id',0);        
        }else{
            $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');
            $randString = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(6/strlen($x)) )),1,6);
            $model->setCookieId($randString);
            $model->setWidth(400);
            $model->setMaxWidth(400);
            $model->setCookieTime(10);
            $model->setMaxCountTime(10);
            $model->setDelaytime(0);
            $model->setBorderRadius(6);
            $model->setPopupBackground('#FFFFFF');
            $model->setPadding(10);
            $model->setScrollPx(50);
            $model->setVerticalPositionPx(100);
            $model->setHorizontalPositionPx(100);
            $model->setCloseOnOverlayclick(1);
            $model->setPriority(1);              
        }


        // 3. Set entered data if was error when we do save
        $data = $this->_objectManager->get('Magento\Backend\Model\Session')->getFormData(true);
        if (!empty($data)) {
            $model->setData($data);
        }

        // 4. Register model to use later in blocks
        $this->_coreRegistry->register('popup', $model);

        // 5. Build edit form
		/** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->_initAction();
        $resultPage->addBreadcrumb(
            $id ? __('Edit Popup') : __('New Popup'),
            $id ? __('Edit Popup') : __('New Popup')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Popup'));
        $resultPage->getConfig()->getTitle()
            ->prepend($model->getId() ? $model->getTitle() : __('New Popup'));
        return $resultPage;
    }
    
    static function renameWidgetIde($matches){
      $rand = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil(2/strlen($x)) )),1,2); 
      return $matches[0].$rand;
    }      
}
