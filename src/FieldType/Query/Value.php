<?php

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\Core\FieldType\Value as BaseValue;

class Value extends BaseValue
{
    public $items = [];

    public function __construct($items = [])
    {
        if (!is_array($items)) {
            $items = [];
        }

        $this->items = $items;
    }

    /**
     * @see \eZ\Publish\Core\FieldType\Value
     */
    public function __toString()
    {
        return '';
    }
}
