<?php
namespace Magebird\Popup\Model\Widget\Source;
 
class Rules implements \Magento\Framework\Option\ArrayInterface
{
    protected $_salesRule;
    public function __construct(
        \Magento\SalesRule\Model\Rule $_salesRule
    ) {
        $this->_salesRule = $_salesRule;
    }
    public function toOptionArray()
    {
        $options = array();
        $rules = $this->_salesRule->getCollection();
        $rules->addFieldToFilter('coupon_type',2);
        $rules->addFieldToFilter('use_auto_generation',1);  
        foreach($rules as $rule){
          $options[] = array('value' => $rule['rule_id'], 'label'=>$rule['name']." (id ".$rule['rule_id'].")"); 
        }      
        if(count($options)==0){
          $options[] = array('value' => '', 'label'=>__("No matching rules found"));
        }
        return $options;
    }
}
 