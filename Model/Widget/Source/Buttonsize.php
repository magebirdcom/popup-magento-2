<?php
namespace Magebird\Popup\Model\Widget\Source;
 
class Buttonsize implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        $options[] = array('value' => 6, 'label'=>6);
        for($n=1;$n<=10;$n++){
          if($n!=6){
            $options[] = array('value' => $n, 'label'=>$n);
          }  
        }       
        return $options;
    }
}
 