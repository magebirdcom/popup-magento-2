<?php
namespace Magebird\Popup\Block\Adminhtml\Popup\Edit\Tab;

class Settings extends \Magento\Backend\Block\Widget\Form\Generic implements
    \Magento\Backend\Block\Widget\Tab\TabInterface
{

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        array $data = []
    ) {
        parent::__construct($context, $registry, $formFactory, $data);
    }

    protected function _prepareForm()
    {
        $model = $this->_coreRegistry->registry('popup');

        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('popup_settings_');
        if ($this->_isAllowedAction('Magebird_Popup::popup_manager')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Settings')]);

        $note = "<p class='nm'><small>" . __("If set to 'Display only once', the popup won't show up again until the cookie lifetime expires.") . "</small></p>";
    		$fieldset->addField('showing_frequency', 'select', array(
    		  'label'     => __("Display Frequency"),
    		  'name'      => 'showing_frequency',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => __("Display until the popup is closed"),
    			  ),
            
    			  array(
    				  'value'     => 2,
    				  'label'     => __("Display only once"),
    			  ),            
    
    			  array(
    				  'value'     => 3,
    				  'label'     => __('Display every time'),
    			  ),
            
    			  array(
    				  'value'     => 4,
    				  'label'     => __("Display until the site visitor clicks inside the popup"),
    			  ),
            
    			  array(
    				  'value'     => 5,
    				  'label'     => __("Display until the user closes the popup or clicks inside it"),
    			  ),  
    			  array(
    				  'value'     => 6,
    				  'label'     => __("Display if the goal is complete (e.g. the site visitor subscribes to the newsletter)."),
    			  ), 
    			  array(
    				  'value'     => 7,
    				  'label'     => __("Show once per session"),
    			  ),                                                
    		  ),
          'note' => $note,
    		));              
        
        $note = "<p class='nm'><small>" . __("You can also use the decimal numerals (e.g.: to set the cookies expiration time to 1 hour, enter 0.04, which means that 1 day is divided into 24 hours.))") . "</small></p>";		
        $fieldset->addField('cookie_time', 'text', array(
    		  'label'     => __('Cookie lifetime in days'),
    		  'name'      => 'cookie_time',
    		  'class'	  => 'validate-number',
    		  'required'  => true,
          'note' => $note,
    		));   
        
        $note = "<p class='nm'><small>" . __("Use the latin letters and numbers only. It is recommended to use the auto-generated values. If you are doing to run A/B testing for popups with similar content, it is advised to use the same cookie ID - once the site visitor closes the popup, the extension neither will show this popup nor any of its duplicates again.") . "</small></p>";
    		$fieldset->addField('cookie_id', 'text', array(
    		  'label'     => __('Cookie/Popup ID'),
    		  'class'     => 'required-entry',
    		  'required'  => true,
    		  'name'      => 'cookie_id',
          'note' => $note,
    		));   
        
        $note = "<p class='nm'><small>" . __("If a site visitor leaves a window with the popup open (for example, because of a phone call), this can distort the popup display statistics. That is why it is recommended to set the maximum time per popup view.") . "</small></p>";
        $fieldset->addField('max_count_time', 'text', array(
    		  'label'     => __('Max time per view to track statistics (in seconds)'),
    		  'name'      => 'max_count_time',
    		  'class'	  => 'validate-number',
    		  'required'  => true,
          'note' => $note,
    		));             
        
        $note = "<p class='nm'><small>" . __("Available for popups with background overlay.") . "</small></p>";
        $fieldset->addField('close_on_overlayclick','select',array(
                    'label' => __('Close when clicked outside of popup'),
                    'name' =>  'close_on_overlayclick',
              		  'values'    => array(
              			  array(
              				  'value'     => 0,
              				  'label'     => __('No'),
              			  ),
              
              			  array(
              				  'value'     => 1,
              				  'label'     => __('Yes'),
              			  ),
              		  ),
                    'note' => $note,                          
        )); 
        
        $note = "<p class='nm'><small>".__("Leave unchanged (empty) or '0' if you don't want the popup to be closed automatically.")."</small></p>";
        $fieldset->addField('close_on_timeout', 'text', array(
    		  'label'     => __('Close automatically after x seconds'),
    		  'name'      => 'close_on_timeout',
          'note' => $note,
    		)); 
        
        $note = "<p class='nm'><small>".__("If you have added multiple popups for a page, you can stop further display of the popups with a smaller priority by selecting 'Yes'.")."</small></p>";
        $fieldset->addField('stop_further', 'select', array(
    		  'label'     => __('Stop further popup display'),
    		  'name'      => 'stop_further',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => __('Yes'),
    			  ),
    
    			  array(
    				  'value'     => 2,
    				  'label'     => __('No'),
    			  ),
    		  ),
          'note' => $note,   
    		));
        
        $note = "<p class='nm'><small>".__("Here, you can set the display priority for multiple popups assigned to one and the same page.")."</small></p>";
        $fieldset->addField('priority', 'text', array(
    		  'label'     => __('Priority'),
    		  'name'      => 'priority',
          'note' => $note,
    		));          

        $this->_eventManager->dispatch('adminhtml_popup_edit_tab_settings_prepare_form', ['form' => $form]);

        $form->setValues($model->getData());

        $this->setForm($form);

        return parent::_prepareForm();
    }

    /**
     * Prepare label for tab
     *
     * @return string
     */
    public function getTabLabel()
    {
        return __('Settings');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Settings');
    }

    /**
     * {@inheritdoc}
     */
    public function canShowTab()
    {
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function isHidden()
    {
        return false;
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
     * Return predefined additional element types
     *
     * @return array
     */
    protected function _getAdditionalElementTypes()
    {
        return ['image' => 'Magebird\Popup\Block\Adminhtml\Form\Element\Image'];
    }
}
