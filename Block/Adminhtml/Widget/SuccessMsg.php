<?php
namespace Magebird\Popup\Block\Adminhtml\Widget;
class SuccessMsg extends \Magento\Backend\Block\Template{
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
    $input = $this->_elementFactory->create("textarea", ['data' => $element->getData()]);
    $input->setId($element->getId());
    $input->setForm($element->getForm());
    $input->setClass("widget-option input-textarea admin__control-text");
    if ($element->getRequired()) {
        $input->addClass('required-entry');
    }        
    $html = urldecode($input->getElementHtml());
    $html .= '<script>jQuery("#widget_options textarea[name=\'parameters\[success_msg\]\']").prev().remove()</script>';
    $element->setData('after_element_html', $html);
    return $element;
}



}