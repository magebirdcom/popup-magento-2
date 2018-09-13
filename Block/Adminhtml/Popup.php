<?php
/**
 * Adminhtml popup list block
 *
 */
namespace Magebird\Popup\Block\Adminhtml;

class Popup extends \Magento\Backend\Block\Widget\Grid\Container
{
    /**
     * Constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'adminhtml_popup';
        $this->_blockGroup = 'Magebird_Popup';
        $this->_headerText = __('Popup');
        $this->_addButtonLabel = __('Add New Popup');        
        
        $this->buttonList->add('browsetemplate',[
            'label' => __('Popup templates'),
            'onclick'   => "setLocation('".$this->getUrl('*/*/template')."')"
        ]);

        $this->buttonList->add('clearcache',[
            'label' => __('Clear popup cache'),
            'onclick'   => "setLocation('".$this->getUrl('*/*/clearCache')."')",
            'class'     => "clearcache",
            'title'     => __("Popup templates are cached for better performance. When you save popup, it will automatically update the cache. Anyway only if you modify any popup related file (e.g. magebird_popup.csv), you need to clear popup cache.")
        ], -100);
        
              
         
        parent::_construct();
        if ($this->_isAllowedAction('Magebird_Popup::popup_manager')) {
            $this->buttonList->update('add', 'label', __('Add New Popup'));
        } else {
            $this->buttonList->remove('add');
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
}
