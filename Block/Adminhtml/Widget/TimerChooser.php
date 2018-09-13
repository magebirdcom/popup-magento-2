<?php
namespace Magebird\Popup\Block\Adminhtml\Widget;
class TimerChooser extends \Magento\Backend\Block\Template
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
        $uniqId = $this->mathRandom->getUniqueHash($element->getId());
        $sourceUrl = $this->getUrl(
            'magebird_popup/widget/timer',  
            ['uniq_id' => $uniqId]
        );
        
        $chooser = $this->getLayout()->createBlock(
            'Magento\Widget\Block\Adminhtml\Widget\Chooser'
        )->setElement(
            $element
        )->setConfig(
            $this->getConfig()
        )->setFieldsetId(
            $this->getFieldsetId()
        )->setSourceUrl(
            $sourceUrl
        )->setUniqId(
            $uniqId
        );
        
        if ($element->getValue()) {
            $value = explode('/', $element->getValue());            
            $label = "Template ".str_replace(".phtml","",$value[2]);            
            $chooser->setLabel($label);
        }
        
        $element->setData('after_element_html', $chooser->toHtml());
        return $element;                                    
    }    

                
}