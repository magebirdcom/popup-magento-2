<?php

namespace Magebird\Popup\Model;

class Status
{
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 2;

    public static function getOptionArray()
    {
        return array(
            self::STATUS_ENABLED    => __('Enabled'),
            self::STATUS_DISABLED   => __('Disabled')
        );
    }
}
