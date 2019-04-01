<?php
namespace BD\EzPlatformQueryFieldType\GraphQL;

use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\ContentType\FieldDefinition;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\DecoratingFieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\Mapper\FieldDefinition\FieldDefinitionMapper;
use EzSystems\EzPlatformGraphQL\Schema\Domain\Content\NameHelper;

class QueryFieldDefinitionMapper extends DecoratingFieldDefinitionMapper implements FieldDefinitionMapper
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
        FieldDefinitionMapper $innerMapper,
        QueryTypeRegistry $queryTypeRegistry,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    ) {
        parent::__construct($innerMapper);
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->nameHelper = $nameHelper;
        $this->contentTypeService = $contentTypeService;
    }

    public function mapToFieldValueType(FieldDefinition $fieldDefinition): ?string
    {
        if (!$this->canMap($fieldDefinition)) {
            return parent::mapToFieldValueType($fieldDefinition);
        }

        $fieldSettings = $fieldDefinition->getFieldSettings();

        return '[' . $this->getDomainTypeName($fieldSettings['ReturnedType']) . ']';
    }

    protected function getFieldTypeIdentifier(): string
    {
        return 'query';
    }

    private function getDomainTypeName($typeIdentifier)
    {
        return $this->nameHelper->domainContentName(
            $this->contentTypeService->loadContentTypeByIdentifier($typeIdentifier)
        );
    }
}