<?php

namespace Magebird\Popup\Controller\Adminhtml\Index;

use Magento\Backend\App\Action;

class Save extends \Magento\Backend\App\Action
{

    protected $dataProcessor;

    public function __construct(    
      Action\Context $context,
      \Magento\Store\Model\App\Emulation $appEmulation,
      \Magento\Cms\Model\Template\FilterProvider $filterProvider,                    
      PostDataProcessor $dataProcessor)
    {
        $this->dataProcessor = $dataProcessor;
        $this->filterProvider = $filterProvider;
        $this->appEmulation = $appEmulation;     
        parent::__construct($context);        
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magebird_Popup::popup_manager');
    }

    public function execute()
    {
        $_redirectFactory = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();        
        if ($data) {
            $data = $this->dataProcessor->filter($data);

            if(isset($_FILES['image']['name']) && !empty($_FILES['image']['name'])){
                try {
                    $uploader = $this->_objectManager->create(
                                                   'Magento\MediaStorage\Model\File\Uploader',
                                                    ['fileId' => 'image']);
          	        $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowCreateFolders(true);
                    $uploader->setAllowRenameFiles(true);
                    $_media = $this->_objectManager->get('Magento\Framework\Filesystem')
                                                        ->getDirectoryRead(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);                                                                        
                                                     
                    $result = $uploader->save($_media->getAbsolutePath().'/magebird_popup/'); 
                } catch (\Exception $e) {
                    if ($e->getCode() != \Magento\Framework\File\Uploader::TMP_NAME_EMPTY) {
                        throw new FrameworkException($e->getMessage());
                    }
                }                 
                $data['image'] = 'magebird_popup/'.$uploader->getUploadedFileName();          
            }else{
               if(isset($data['image']['delete'])){
                  $data['image'] = '';
               }elseif(!isset($data['id']) && $data["popup_type"]==1){
                  if(!$data['image']['value']){
                    //Mage::getSingleton('adminhtml/session')->addError(Mage::helper('magebird_popup')->__('Please choose your image or select "Custom Content" in "Popup Content Type" field.'));
                  }              
                  $data['image'] = $data['image']['value'];
                }else{
                  $data["image"] = '';
                }            
            }            
            
            $model = $this->_objectManager->create('Magebird\Popup\Model\Popup');            
            
            if (isset($data['id'])) {            
                $model->load($data['id']);
            }         
            if(isset($data['popup_id']) && empty($data['popup_id'])){
              unset($data['popup_id']); //otherwise save won't ber successfull when popup_id=''
            }            
            $model->setData($data);   
            try {
              $model->save();
              if (!$model->getId()) {
                  $this->messageManager->addError(__('Error saving popup'));
              }                             
              $this->messageManager->addSuccess(__('Popup was successfully saved.'));
              $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);            
                if ($this->getRequest()->getParam('back')) {
                    return $_redirectFactory->setPath('*/*/edit', array('id' => $model->getId()));
                } else {
                    return $_redirectFactory->setPath('*/*/');
                }                                           
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the popup.'));
            }
            
            $this->_getSession()->setFormData($data);
            return $_redirectFactory->setPath('*/*/edit', array('id' => $model->getId()));            
        }
        return $_redirectFactory->setPath('*/*/');
    }       
}
