<?php
namespace Magebird\Popup\Block\Adminhtml\Widget;
class Rules extends \Magento\Backend\Block\Template
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
        $html = '<p class="note"><span>'.__('Choose your shoping cart rule you want to be used to generate coupons. Only rules with field &quot;<a target="_blank" href="%1">Use Auto Generation</a>&quot; checked on can be used. If you want to use static coupon code change &quot;Coupon type&quot; above.','http://www.magebird.com/magento-extensions/popup.html?tab=faq#dynamicCoupon').'</span></p>';
        $element->setData('after_element_html', $html);
        return $element;                                    
    }
}