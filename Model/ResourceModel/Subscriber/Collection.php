<?php

/**
 * Popup Resource Collection
 */
namespace Magebird\Popup\Model\ResourceModel\Subscriber;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected function _construct()
    {
        $this->_init('Magebird\Popup\Model\Subscriber', 'Magebird\Popup\Model\ResourceModel\Subscriber');
    } 
}
