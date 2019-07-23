<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class DelegatedItem
{
    /**
     * @var \eZ\Publish\SPI\Persistence\Content\VersionInfo
     */
    private $versionInfo;

    /**
     * @var \eZ\Publish\SPI\Persistence\Content\Field
     */
    private $field;

    /**
     * @var \eZ\Publish\API\Repository\Values\Content\ContentCreateStruct[]
     */
    private $items;

    public function __construct(VersionInfo $versionInfo, Field $field, array $items)
    {
        $this->versionInfo = $versionInfo;
        $this->field = $field;
        $this->items = $items;
    }

    /**
     * @param $contentId
     * @param $versionNumber
     *
     * @return bool
     */
    public function matches($contentId, $versionNumber): bool
    {
        return $this->versionInfo->contentInfo->id === $contentId && $this->versionInfo->versionNo === $versionNumber;
    }

    /**
     * @return array
     */
    public function getItems(): array
    {
        return $this->items;
    }

    public function getField(): Field
    {
        return $this->field;
    }
}