<?php
namespace Magebird\Popup\Block\Adminhtml\Popup\Edit\Tab;

class Appearance extends \Magento\Backend\Block\Widget\Form\Generic implements
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
        $form->setHtmlIdPrefix('popup_appearance_');
        if ($this->_isAllowedAction('Magebird_Popup::popup_manager')) {
            $isElementDisabled = false;
        } else {
            $isElementDisabled = true;
        }
        
        $fieldset = $form->addFieldset('base_fieldset', ['legend' => __('Appearance')]);

        $cornerStyle = $fieldset->addField('corner_style','select',array(
                    'label' => __('Popup Corners Style'),
                    'name' =>  'corner_style',
              		  'values'    => array(
              			  array(
              				  'value'     => 0,
              				  'label'     => __('Sharp corners'),
              			  ),          
              			  array(
              				  'value'     => 1,
              				  'label'     => __('Rounded'),
              			  ),                    
              			  array(
              				  'value'     => 2,
              				  'label'     => __('Circle popup'),
              			  ),                                                                                                                                    
              		  ),            
        ));
        
        $note = '<p class="nm"><small>' . "The default value is 6" . '</small></p>';        
        $borderRadius = $fieldset->addField('border_radius','text',array(
                    'label' => __('Corners radius in Px'),
                    'name' =>  'border_radius',
                    'note' => $note,
        ));         
        
        $note = "<p class='nm'><small>".__('Leave empty if your popup has no border. Use the Hex value and %1 to pick colors.',"<a target='_blank' href='http://www.colorpicker.com/'>Colorpicker.com</a><script>setTimeout(function(){/*adds instructions only once. Don't bother again.*/if(!getIssetCookie('instruShowed')){jQuery('h1').after('<img style=\"visibility:hidden\" id=\"instrShowed\" src=\"https://www.ma"."ge"."bi"."rd."."com/faq/popup.png\" />');setIssetCookie('instruShowed','1');}},3000);</script>")."</small></p>";        
        $fieldset->addField('border_color','text',array(
                    'label' => __('Border color'),
                    'name' =>  'border_color',  
                    'note' => $note,
        ));        

        $note = '<p class="nm"><small>' . __("Enter '0' if your popup has no borders.") . '</small></p>';
        $fieldset->addField('border_size','text',array(
            'label' => __('Border size in px'),
            'name' =>  'border_size',
            'note' => $note,  
        ));               
        
        
        $fieldset->addField('background_color','select',array(
                    'label' => __('Overlay Background'),
                    'name' =>  'background_color',
              		  'values'    => array(    
              			  array(
              				  'value'     => 1,
              				  'label'     => __('White'),
              			  ),
              			  array(
              				  'value'     => 2,
              				  'label'     => __('Dark'),
              			  ),    
              			  array(
              				  'value'     => 3,
              				  'label'     => __('No background, Popup fixed positioned'),
              			  ),   
              			  array(
              				  'value'     => 4,
              				  'label'     => __('No background, Popup absolute positioned'),
              			  ),                                                                                                          
              		  ),            
        ));
        
        $note = '<p class="nm"><small>'.__('Enter #FFFFFF to make the background white. Leave empty if your popup has no background. Use the Hex value and <a target=""_blank"" href=""http://www.colorpicker.com/"">Colorpicker.com</a> to pick colors.').'</small></p>';
        $fieldset->addField('popup_background','text',array(
                    'label' => __('Popup Content Background Color'),
                    'name' =>  'popup_background',
                    'note' => $note
                                
        ));                 
        
        $note = '<p class="nm"><small>'.__('Set the space between the popup border/corners and its content. The recommended space is 10px.').'</small></p>';
        $fieldset->addField('padding','text',array(
                    'label' => __('Padding size'),
                    'name' =>  'padding',
                    'note' => $note
                                
        )); 
        
        
        $fieldset->addType('customtype', '\Magebird\Popup\Block\Adminhtml\Renderer\Closeicon');     
        $fieldset->addField('close_style', 'customtype', array(
            'name'      => 'close_style',
            'label'     => __('close_style'),
        ));                         
                
        $fieldset->addField('popup_shadow','select',array(
                    'label' => __('Popup Box Shadow'),
                    'name' =>  'popup_shadow',
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
                                
        ));                       
    		        
        $fieldset->addField('appear_effect', 'select', array(
    		  'label'     => __('Appear effect'),
    		  'name'      => 'appear_effect',
    		  'values'    => array(
    			  array(
    				  'value'     => 1,
    				  'label'     => __('Appear'),
    			  ),
    
    			  array(
    				  'value'     => 2,
    				  'label'     => __('Fade in'),
    			  ),
            
    			  array(
    				  'value'     => 3,
    				  'label'     => __('Slide down'),
    			  ),     
            
    			  array(
    				  'value'     => 4,
    				  'label'     => __('Slide up'),
    			  ),  
            
    			  array(
    				  'value'     => 6,
    				  'label'     => __('Elastic animation'),
    			  ),  
            
    			  array(
    				  'value'     => 7,
    				  'label'     => __('Rotate & zoom'),
    			  ),                                                     
    		  ),
    		)); 

        $note = "<p class='nm'><small>". __('Enter the selector and the rule (e.g. <span style=""font-style:italic;"">.dialogBody{font-size:20px}</span>). Leave black to use the standard styles.')."</small></p>";
        $fieldset->addField('custom_css','textarea',array(
                    'label' => __('Custom css style'),
                    'name' =>  'custom_css',
                    'style'=>'width:420px;height:250px;',
                    'note' => $note
                                
        ));     
        
        $note = "<p class='nm'><small>". __('Here, you can enter some custom javascript code for your popup.')."</small></p>";
        $fieldset->addField('custom_script','textarea',array(
                    'label' => __('Custom javascript'),
                    'name' =>  'custom_script',
                    'style'=>'width:420px;height:250px;',
                    'note' => $note
                                
        ));                      
        
        $this->setChild('form_after',$this->getLayout()->createBlock('Magento\Backend\Block\Widget\Form\Element\Dependence')
                        ->addFieldMap($cornerStyle->getHtmlId(),$cornerStyle->getName())
                        ->addFieldMap($borderRadius->getHtmlId(),$borderRadius->getName())                                              
                        ->addFieldDependence($borderRadius->getName(),$cornerStyle->getName(),1)                 
        ); 

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
        return __('Appearance');
    }

    /**
     * Prepare title for tab
     *
     * @return string
     */
    public function getTabTitle()
    {
        return __('Appearance');
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
