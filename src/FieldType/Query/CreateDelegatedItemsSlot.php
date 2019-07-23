<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\LocationService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\SignalSlot\Slot as BaseSlot;
use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\SPI\Persistence\Content\Field as SPIField;

class CreateDelegatedItemsSlot extends BaseSlot
{
    /**
     * @var \BD\EzPlatformQueryFieldType\FieldType\Query\ItemsDelegator
     */
    private $delegator;
    /**
     * @var \BD\EzPlatformQueryFieldType\FieldType\Query\CreateStructProvider
     */
    private $createStructProvider;
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentService $contentService, ContentTypeService $contentTypeService, ItemsDelegator $delegator, CreateStructProvider $createStructProvider)
    {
        $this->delegator = $delegator;
        $this->createStructProvider = $createStructProvider;
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\PublishVersionSignal) {
            return;
        }

        if (!$this->delegator->hasItems($signal->contentId, $signal->versionNo)) {
            return;
        }

        $items = $this->delegator->getItems($signal->contentId, $signal->versionNo);

        foreach ($items as $field) {
            // @todo make dynamic
            $languageCode = 'eng-GB';
            $content = $this->contentService->loadContent($signal->contentId, null, $signal->versionNo);
            $fieldDefinition = $this->getFieldDefinition($content->contentInfo, $field);
            $locationCreateStruct = $this->createStructProvider->getLocationCreateStructForField($fieldDefinition, $content);
            $baseContentCreateStruct = $this->createStructProvider->getContentCreateStructForField($fieldDefinition, $content, $languageCode);

            foreach ($items[$field] as $itemFields) {
                $contentCreateStruct = clone $baseContentCreateStruct;
                foreach ($itemFields as $fieldIdentifier => $value) {
                    $contentCreateStruct->setField($fieldIdentifier, $value);
                }
                $draft = $this->contentService->createContent($contentCreateStruct, [$locationCreateStruct]);
                $createdContent = $this->contentService->publishVersion($draft->versionInfo);

                echo "Published delegated content #" . $createdContent->contentInfo->id . "\n";
            }
        }
    }

    private function getFieldDefinition(ContentInfo $contentInfo, SPIField $field): FieldDefinition
    {
        $contentType =$this->contentTypeService->loadContentType($contentInfo->contentTypeId);

        foreach ($contentType->getFieldDefinitions() as $fieldDefinition) {
            if ($fieldDefinition->id === $field->fieldDefinitionId) {
                return $fieldDefinition;
            }
        }

        throw new \Exception("Field definition with id {$field->fieldDefinitionId} not found");
    }
}