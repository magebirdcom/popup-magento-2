<?php

namespace Magebird\Popup\Model\ResourceModel;

/**
 * Subscriber Resource Model
 */
class Subscriber extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
 
    protected function _construct()
    {
        $this->_init('magebird_popup_subscriber', 'subscriber_id');
    }        
}
