<?php

namespace spec\BD\EzPlatformQueryFieldType\GraphQL;

use BD\EzPlatformGraphQLBundle\Schema\Domain\Content\FieldValueBuilder\FieldValueBuilder;
use BD\EzPlatformGraphQLBundle\Schema\Domain\Content\NameHelper;
use BD\EzPlatformQueryFieldType\GraphQL\QueryFieldValueBuilder;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use eZ\Publish\Core\Repository\Values\ContentType\ContentType;
use eZ\Publish\Core\Repository\Values\ContentType\FieldDefinition;
use PhpSpec\ObjectBehavior;

class QueryFieldValueBuilderSpec extends ObjectBehavior
{
    const FIELD_IDENTIFIER = 'test';
    const RETURNED_CONTENT_TYPE_IDENTIFIER = 'folder';
    const GRAPHQL_TYPE = 'FolderContent';

    function let(
        QueryTypeRegistry $queryTypeRegistry,
        NameHelper $nameHelper,
        ContentTypeService $contentTypeService
    )
    {
        $contentType = new ContentType(['identifier' => self::RETURNED_CONTENT_TYPE_IDENTIFIER]);

        $contentTypeService
            ->loadContentTypeByIdentifier(self::RETURNED_CONTENT_TYPE_IDENTIFIER)
            ->willReturn($contentType);

        $nameHelper
            ->domainContentName($contentType)
            ->willReturn(self::GRAPHQL_TYPE);

        $this->beConstructedWith($queryTypeRegistry, $nameHelper, $contentTypeService);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryFieldValueBuilder::class);
        $this->shouldHaveType(FieldValueBuilder::class);
    }

    function it_builds_with_an_array_of_the_type_the_field_returns()
    {
        $this
            ->buildDefinition($this->fieldDefinition())
            ->shouldHaveGraphQLType('[' . self::GRAPHQL_TYPE . ']');
    }

    function it_resolves_with_the_QueryFieldValue_resolver()
    {
        $this
            ->buildDefinition($this->fieldDefinition())
            ->shouldBeResolvedWith('QueryFieldValue');
    }

    public function getMatchers(): array
    {
        return [
            'haveGraphQLType' => function($definition, $type) {
                return isset($definition['type'])
                    && $definition['type'] === $type;
            },
            'beResolvedWith' => function($definition, $resolver) {
                return isset($definition['resolve'])
                    && strpos($definition['resolve'], $resolver) !== false;
            }
        ];
    }

    /**
     * @return FieldDefinition
     */
    private function fieldDefinition(): FieldDefinition
    {
        $fieldDefinition = new FieldDefinition([
            'identifier' => self::FIELD_IDENTIFIER,
            'fieldSettings' => ['ReturnedType' => self::RETURNED_CONTENT_TYPE_IDENTIFIER]
        ]);
        return $fieldDefinition;
    }


}
