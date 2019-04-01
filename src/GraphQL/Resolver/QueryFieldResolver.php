<?php
namespace BD\EzPlatformQueryFieldType\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Exceptions\NotFoundException;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use Overblog\GraphQLBundle\Error\UserWarning;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;

class QueryFieldResolver
{
    /**
     * @var ContentService
     */
    private $contentService;

    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    /**
     * @var QueryTypeRegistry
     */
    private $queryTypeRegistry;

    /**
     * @var SearchService
     */
    private $searchService;

    /**
     * @var \EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader
     */
    private $contentLoader;

    public function __construct(
        ContentService $contentService,
        ContentLoader $contentLoader,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        QueryTypeRegistry $queryTypeRegistry
    ) {
        $this->contentService = $contentService;
        $this->contentLoader = $contentLoader;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function resolveQueryField(Field $field, Content $content)
    {
        try {
            $queryFieldDefinition =
                $this->contentTypeService->loadContentType($content->contentInfo->contentTypeId)
                    ->getFieldDefinition($field->fieldDefIdentifier);
        } catch (NotFoundException $e) {
            throw new UserWarning("Content type with id $content->contentInfo->contentTypeId not found");
        }

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