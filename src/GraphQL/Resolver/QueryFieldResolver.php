<?php
namespace BD\EzPlatformQueryFieldType\GraphQL\Resolver;

use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class QueryFieldResolver
{
    /**
     * @var QueryTypeRegistry
     */
    private $queryTypeRegistry;

    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader
     */
    private $contentLoader;

    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader
     */
    private $contentTypeLoader;

    public function __construct(
        ContentLoader $contentLoader,
        ContentTypeLoader $contentTypeLoader,
        QueryTypeRegistry $queryTypeRegistry
    ) {
        $this->contentLoader = $contentLoader;
        $this->contentTypeLoader = $contentTypeLoader;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function resolveQueryField(Field $field, Content $content)
    {
        $queryFieldDefinition =
            $this
                ->contentTypeLoader->load($content->contentInfo->contentTypeId)
                ->getFieldDefinition($field->fieldDefIdentifier);

        $queryType = $this->queryTypeRegistry->getQueryType($queryFieldDefinition->fieldSettings['QueryType']);

        $parameters = $this->resolveParameters(
            json_decode($queryFieldDefinition->fieldSettings['Parameters'], true),
            $content
        );

        return $this->contentLoader->find($queryType->getQuery($parameters));
    }

    private function resolveParameters(array $parameters, Content $content)
    {
        foreach ($parameters as $key => $parameter) {
            if (substr($parameter, 0, 2) === '@=') {
                $language = new ExpressionLanguage();
                $parameters[$key] = $language->evaluate(
                    substr($parameter, 2),
                    [
                        'content' => $content,
                        'contentInfo' => $content->contentInfo,
                    ]
                );
            }
        }

        return $parameters;
    }
}