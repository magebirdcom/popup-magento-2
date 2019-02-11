<?php
namespace Magebird\Popup\Block\Adminhtml\Popup;

/**
 * Admin Popup page
 *
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_scopeConfig;
    
    protected $_messageManager;
    
    protected $_popuphelper;
    
    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Framework\Registry $registry,
        \Magebird\Popup\Helper\Data $popuphelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->messageManager = $messageManager;
        $this->_popuphelper = $popuphelper;
        parent::__construct($context, $data);
    }

    /**
     * Initialize cms page edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'popup_id';
        $this->_blockGroup = 'Magebird_Popup';
        $this->_controller = 'adminhtml_popup';

        parent::_construct();

        if ($this->_isAllowedAction('Magebird_Popup::popup_manager')) {
            $this->buttonList->update('save', 'label', __('Save Popup'));
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }

        if ($this->_isAllowedAction('Magebird_Popup::popup_manager')) {
            $this->buttonList->update('delete', 'label', __('Delete Popup'));
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded page
     *
     * @return string
     */
    public function getHeaderText()
    {
        if ($this->_coreRegistry->registry('popup')->getId()) {
            return __("Edit Popup '%1'", $this->escapeHtml($this->_coreRegistry->registry('popup')->getTitle()));
        } else {
            return __('New Popup');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('popup/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '{{tab_id}}']);
    }

    /**
     * Prepare layout
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_formScripts[] = "
            function toggleEditor() {
                if (tinyMCE.getInstanceById('page_content') == null) {
                    tinyMCE.execCommand('mceAddControl', false, 'page_content');
                } else {
                    tinyMCE.execCommand('mceRemoveControl', false, 'page_content');
                }
            };
        "; 
        $extensionKey = $this->_scopeConfig->getValue('magebird_popup/general/extension_key'); 
        $networkError = $this->_popuphelper->checkNetworkError();
         if(empty($extensionKey) && $networkError){
          $dismissUrl = $this->getUrl('magebird_popup/index/dismissError', ['_current' => true, '_use_rewrite' => true, '_query'=>'isAjax=true']);                                      
          $this->messageManager->addError("Script magebirdpopup.php is not web-accessible and popups won't show up. Please read instructions <a target='_blank' href='https://www.magebird.com/magento-extensions/popup-2.html?tab=faq#requestType'>here</a>. If the problem has been resolved, click <a href='javascript:void(0)' data-url='".$dismissUrl."' onclick='dismissNetworkError(this);'>here</a> to remove this message.");
        }                         
        return parent::_prepareLayout();
    }
}
