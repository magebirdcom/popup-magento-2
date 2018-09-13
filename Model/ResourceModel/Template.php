<?php

namespace Magebird\Popup\Model\ResourceModel;

/**
 * Popup Resource Model
 */
class Template extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('magebird_popup_template', 'template_id');
    }    
        
}
