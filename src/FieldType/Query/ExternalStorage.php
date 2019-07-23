<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\Core\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\SPI\FieldType\FieldStorage;
use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class ExternalStorage implements FieldStorage
{
    /**
     * @var \BD\EzPlatformQueryFieldType\FieldType\Query\ItemsDelegator
     */
    private $itemsDelegator;

    public function __construct(ItemsDelegator $itemsDelegator)
    {
        $this->itemsDelegator = $itemsDelegator;
    }

    public function storeFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
        $this->itemsDelegator->delegate($versionInfo, $field, $field->value->externalData);

        return false;
    }

    public function getFieldData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }

    public function deleteFieldData(VersionInfo $versionInfo, array $fieldIds, array $context)
    {
    }

    public function hasFieldData()
    {
        return false;
    }

    public function getIndexData(VersionInfo $versionInfo, Field $field, array $context)
    {
    }
}