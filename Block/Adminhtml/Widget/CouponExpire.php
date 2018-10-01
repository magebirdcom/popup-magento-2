<?php
namespace Magebird\Popup\Block\Adminhtml\Widget;
class CouponExpire extends \Magento\Backend\Block\Template
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
        $html = "<input type='checkbox' class='inheritTimer' /> Iherit from countdown timer (<a target='_blank' href='http://www.magebird.com/magento-extensions/popup-2.html?tab=faq#timelimitedCoupons'>What is that?</a>)
        <script>
        var element = jQuery(\"#widget_options input[name='parameters\[coupon_expiration\]']\");
        element.attr('style', 'width: 70px !important');
        jQuery(\".inheritTimer\").click(function(){
          if(element.attr('readonly')){
            element.attr('readonly', false);      
            element.val('');        
          }else{
            element.val('inherit');
            element.attr('readonly', true);      
          }
        });
        </script>";    
        $element->setData('after_element_html', $html);
        return $element;
                                 
    }
}