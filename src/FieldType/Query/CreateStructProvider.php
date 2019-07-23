<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\FieldType\Query;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentCreateStruct;
use eZ\Publish\API\Repository\Values\Content\LocationCreateStruct;
use eZ\Publish\API\Repository\Values\ContentType\ContentType;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class CreateStructProvider
{
    /**
     * @var \eZ\Publish\API\Repository\ContentService
     */
    private $contentService;
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentService $contentService, ContentTypeService $contentTypeService)
    {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
    }

    public function getContentCreateStructForField(FieldDefinition $fieldDefinition, Content $content, $languageCode): ContentCreateStruct
    {
        if ($fieldDefinition->fieldTypeIdentifier !== Type::TYPE_IDENTIFIER) {
            throw new \Exception('$fieldDefinition is not a query field');
        }

        $contentType = $this->getContentType($fieldDefinition);

        return $this->contentService->newContentCreateStruct($contentType, $languageCode);
    }

    public function getLocationCreateStructForField(FieldDefinition $fieldDefinition, Content $content): LocationCreateStruct
    {
        return new LocationCreateStruct([
            'parentLocationId' => $this->getParentLocationId($fieldDefinition, $content)
        ]);
    }

    private function getContentType(FieldDefinition $fieldDefinition): ContentType
    {
        return $this->contentTypeService->loadContentTypeByIdentifier($fieldDefinition->fieldSettings['ReturnedType']);
    }

    private function getParentLocationId(FieldDefinition $fieldDefinition, Content $content): int
    {
        $parameters = json_decode($fieldDefinition->fieldSettings['Parameters'], true);
        if (isset($parameters['parent_location_id'])) {
            if (substr($parameters['parent_location_id'], 0, 2) === '@=') {
                $parameter = substr($parameters['parent_location_id'], 2);
                return (new ExpressionLanguage())->evaluate(
                    $parameter,
                    [
                        'content' => $content,
                        'contentInfo' => $content->contentInfo,
                    ]
                );
            }
        } else {
            throw new \Exception("Unable to determine a location ID");
        }
    }
}