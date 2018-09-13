<?php
namespace Magebird\Popup\Block\Adminhtml\Widget;   
class EmailServices extends \Magento\Backend\Block\Template
{

    protected $_elementFactory;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Data\Form\Element\Factory $elementFactory,
        array $data = []
    ) {
        $this->_elementFactory = $elementFactory;
        parent::__construct($context, $data);
    }

    public function prepareElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
      $hideServices = '';
      $services = array();
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enableactivecampaign')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[ac_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>ActiveCampaign</strong>";  
      }
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enablecampaignmonitor')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[cm_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Campaignmonitor</strong>";  
      }
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enablegetresponse')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[gr_campaign_token\]']\").parent().parent().hide();";
        $services[] = "<strong>GetResponse</strong>";  
      }
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enablesendy')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[sendy_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Sendy</strong>";  
      }
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_phplist')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[phplist_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>phpList</strong>";  
      } 
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_klaviyo')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[klaviyo_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Klaviyo</strong>";  
      }   
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_mailjet')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[mailjet_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>Mailjet</strong>";  
      }    
      if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_emma')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[emma_group_ids\]']\").parent().parent().hide();";
        $services[] = "<strong>Emma</strong>";  
      }    
       if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_mailerlite')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[mailerlite_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>MailerLite</strong>";  
      }  
       if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_aweber')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[aweber_list_id\]']\").parent().parent().hide();";
        $services[] = "<strong>AWeber</strong>";  
      } 
       if(!$this->_scopeConfig->getValue('magebird_popup/services/enable_nuevomailer')){
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[nuevomailer_list_ids\]']\").parent().parent().hide();";
        $hideServices .= "jQuery(\"#widget_options input[name='parameters\[nuevomailer_newsletter\]']\").parent().parent().hide();";
        $services[] = "<strong>Nuevomailer</strong>";  
      }                    
      $msg = '';      
      if($services){
        $msg = __("To use ".implode($services,", ")." list ID, enable it first here: Store->Configuration->MAGEBIRD EXTENSIONS->Popup->Email services. Then, it will show up here.");
      }
              
      //$hideServices = "jQuery(\"#widget_options input[name='parameters\[sendy_list_id\]']\").parent().parent().hide();";
      $html =  "$msg<script>$hideServices</script>";         
                
    $element->setData('after_element_html', $html);
    return $element;                               
    }
}