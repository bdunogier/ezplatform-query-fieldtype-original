<?php
namespace BD\EzPlatformQueryFieldType\GraphQL;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\FieldValueBuilder\FieldValueBuilder;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;

class QueryFieldValueBuilder implements FieldValueBuilder
{
    /**
     * @var QueryTypeRegistry
     */
    private $queryTypeRegistry;

    /**
     * @var NameHelper
     */
    private $nameHelper;
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(
        QueryTypeRegistry $queryTypeRegistry,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    ){
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
    }

    /**
     * @param FieldDefinition $fieldDefinition
     * @return array GraphQL definition array for the Field Value
     */
    public function buildDefinition(FieldDefinition $fieldDefinition)
    {
        $fieldSettings = $fieldDefinition->getFieldSettings();

        return [
            'type' => '[' . $this->getDomainTypeName($fieldSettings['ReturnedType']) . ']',
            'resolve' => sprintf(
                '@=resolver("QueryFieldValue", [value, "%s"])',
                $fieldDefinition->identifier
            ),
        ];
    }

    private function getDomainTypeName($typeIdentifier)
    {
        return $this->nameHelper->domainContentName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }
}