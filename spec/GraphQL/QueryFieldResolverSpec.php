<?php

namespace spec\BD\EzPlatformQueryFieldType\GraphQL;

use BD\EzPlatformQueryFieldType\FieldType\Query;
use BD\EzPlatformQueryFieldType\GraphQL\QueryFieldResolver;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Query as ApiQuery;
use eZ\Publish\Core\QueryType\QueryType;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use eZ\Publish\Core\Repository\Values;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class QueryFieldResolverSpec extends ObjectBehavior
{
    const CONTENT_TYPE_ID = 1;
    const QUERY_TYPE_IDENTIFIER = 'query_type_identifier';
    const FIELD_DEFINITION_IDENTIFIER = 'test';
    const RESULTS = [];

    private $contentType;

    function let(
        ContentLoader $contentLoader,
        ContentTypeLoader $contentTypeLoader,
        QueryTypeRegistry $queryTypeRegistry,
        QueryType $queryType
    ) {
        $parameters = json_encode([
            'param1' => 'value1',
            'param2' => 'value2',
        ]);

        $contentType = new Values\ContentType\ContentType([
            'fieldDefinitions' => [
                new Values\ContentType\FieldDefinition([
                    'identifier' => 'test',
                    'fieldTypeIdentifier' => 'query',
                    'fieldSettings' => [
                        'ReturnedType' => 'folder',
                        'QueryType' => self::QUERY_TYPE_IDENTIFIER,
                        'Parameters' => $parameters,
                    ]
                ]),
            ],
        ]);

        $contentTypeLoader->load(self::CONTENT_TYPE_ID)->willReturn($contentType);
        $queryTypeRegistry->getQueryType(self::QUERY_TYPE_IDENTIFIER)->willReturn($queryType);
        $queryType->getQuery(Argument::any())->willReturn(new ApiQuery());
        $contentLoader->find(Argument::any())->willReturn(self::RESULTS);
        $this->beConstructedWith($contentLoader, $contentTypeLoader, $queryTypeRegistry);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType(QueryFieldResolver::class);
    }

    function it_resolves_the_value_using_the_configured_query()
    {
        $this->resolveQueryField($this->getField(), $this->getContent())->shouldBe(self::RESULTS);
    }

    /**
     * @return \EzSystems\EzPlatformGraphQL\GraphQL\Value\Field
     */
    private function getField(): Field
    {
        return new Field([
            'fieldDefIdentifier' => self::FIELD_DEFINITION_IDENTIFIER,
            'value' => new Query\Value(),
        ]);
    }

    /**
     * @return \eZ\Publish\Core\Repository\Values\Content\Content
     */
    private function getContent(): Values\Content\Content
    {
        return new Values\Content\Content([
            'versionInfo' => new Values\Content\VersionInfo([
                'contentInfo' => new ContentInfo(['contentTypeId' => self::CONTENT_TYPE_ID]),
            ])
        ]);
    }
}
