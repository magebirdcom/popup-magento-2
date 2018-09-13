<?php

/**
 * Popup Resource Collection
 */
namespace Magebird\Popup\Model\ResourceModel\Template;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Magebird\Popup\Model\Template', 'Magebird\Popup\Model\ResourceModel\Template');
    }       
}
