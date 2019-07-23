<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\SPI\Persistence\Content\Field;
use eZ\Publish\SPI\Persistence\Content\VersionInfo;

class ItemsDelegator
{
    private $items = [];

    public function delegate(VersionInfo $versionInfo, Field $field, array $items)
    {
        $this->items[] = new DelegatedItem($versionInfo, $field, $items);
    }

    /**
     * @param int $contentId
     * @param int $versionNo
     *
     * @return bool
     */
    public function hasItems($contentId, $versionNo)
    {
        return !empty($this->getMatchingItems($contentId, $versionNo));
    }

    /**
     * @param int $contentId
     * @param int $versionNo
     *
     * @return \BD\EzPlatformQueryFieldType\FieldType\Query\DelegatedItem[]
     */
    public function getItems($contentId, $versionNo): \SplObjectStorage
    {
        $matchingItems = $this->getMatchingItems($contentId, $versionNo);

        if (empty($matchingItems)) {
            return $matchingItems;
        }

        $return = new \SplObjectStorage();
        foreach ($matchingItems as $item) {
            if (!isset($return[$item->getField()])) {
                $return[$item->getField()] = [];
            }
            $fieldItems = $return[$item->getField()];
            $fieldItems = array_merge($fieldItems, $item->getItems());
            $return[$item->getField()] = $fieldItems;
        }

        return $return;
    }

    /**
     * @param int $contentId
     * @param int $versionNo
     *
     * @return \BD\EzPlatformQueryFieldType\FieldType\Query\DelegatedItem[]
     */
    private function getMatchingItems($contentId, $versionNo): array
    {
        return array_filter(
            $this->items,
            function(DelegatedItem $item) use($contentId, $versionNo) {
                return $item->matches($contentId, $versionNo);
            }
        );
    }
}