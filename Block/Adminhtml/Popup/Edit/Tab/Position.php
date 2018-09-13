<?php
namespace Magebird\Popup\Block\Adminhtml\Popup\Edit\Tab;

class Position extends \Magento\Backend\Block\Widget\Form\Generic implements
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
        $form->setHtmlIdPrefix('popup_position_');
        if ($this->_isAllowedAction('Magebird_Popup::popup_manager')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Position')]);

        $note = "<p class='nm'><small>".__("If you have selected the percentage value for the 'Popup width unit', you can only select the central position.")."</small></p>";              
        $fieldset->addField('horizontal_position','select',array(
                    'label' => __('Horizontal position'),
                    'name' =>  'horizontal_position',
                    'required'=>true,
              		  'values'    => array(
              			  array(
              				  'value'     => 1,
              				  'label'     => __('Center'),
              			  ),            
              			  array(
              				  'value'     => 2,
              				  'label'     => __('Define px from left of the screen'),
              			  ),
              			  array(
              				  'value'     => 3,
              				  'label'     => __('Define px from right of the screen'),
              			  ),  
              			  array(
              				  'value'     => 4,
              				  'label'     => __('Define px from center to left'),
              			  ), 
              			  array(
              				  'value'     => 5,
              				  'label'     => __('Define px from center to right'),
              			  ),       
              			  array(
              				  'value'     => 6,
              				  'label'     => __('Left px absolute to defined element'),
              			  ),                                                                                                                                 
              		  ),
                    'note' => $note            
        ));
              
        
        $note = '<p class="nm"><small>' .  __("If you selected horizontal position other than 'Center', define how many px from defined position you want popup to appear.") . '</small></p>';
        $fieldset->addField('horizontal_position_px','text',array(
                    'label' => __('Px from defined position'),
                    'name' =>  'horizontal_position_px',
                    'required'=>true,
                    'note' => $note,                                                                                                      
        ));
        
        
        $fieldset->addField('vertical_position','select',array(
                    'label' => __('Vertical position'),
                    'name' =>  'vertical_position',
                    'required'=>true, 
              		  'values'    => array(
              			  array(
              				  'value'     => 1,
              				  'label'     => __('Px from the top of the page'),
              			  ),            
              			  array(
              				  'value'     => 2,
              				  'label'     => __('Px from the bottom of the page'),
              			  ),     
              			  array(
              				  'value'     => 3,
              				  'label'     => __('Top Px absolute to the defined element'),
              			  ),  
              			  array(
              				  'value'     => 4,
              				  'label'     => __('Show on the top of the page and push page content down'),
              			  ),                                                                                                                                       
              		  ),            
        ));
        
        $fieldset->addField('vertical_position_px','text',array(
                    'label' => __('Number of Px'),
                    'name' =>  'vertical_position_px',
                    'required'=>true,                                                                                                
        ));   
        
        $note = '<p class="nm"><small>' .  __("If you have selected the 'Left Px absolute to the defined element' and 'Top Px absolute to the defined element', you need to define the element the popup position will be calculated from. Enter the element selector here. E.g. #idName, .className, div input#idName. More info about the selectors can be found <a href='http://www.w3schools.com/jquery/jquery_ref_selectors.asp' target='_blank'>here</a>. Otherwise, leave empty.") . '</small></p>';
        $fieldset->addField('element_id_position','text',array(
                    'label' => __('Define element ID'),
                    'name' =>  'element_id_position',
                    'note' => $note,                                                                                                      
        )); 

        $this->_eventManager->dispatch('adminhtml_popup_edit_tab_position_prepare_form', ['form' => $form]);

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
        return __('Position');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Position');
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
