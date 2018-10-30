<?php
namespace BD\EzPlatformQueryFieldType\GraphQL\Resolver;

use eZ\Publish\API\Repository\ContentService;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\SearchService;
use eZ\Publish\API\Repository\Values\Content\ContentInfo;
use eZ\Publish\API\Repository\Values\Content\Search\SearchHit;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
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

    public function __construct(
        ContentService $contentService,
        ContentTypeService $contentTypeService,
        SearchService $searchService,
        QueryTypeRegistry $queryTypeRegistry
    ) {
        $this->contentService = $contentService;
        $this->contentTypeService = $contentTypeService;
        $this->searchService = $searchService;
        $this->queryTypeRegistry = $queryTypeRegistry;
    }

    public function resolveQueryField(ContentInfo $contentInfo, $fieldDefinitionIdentifier)
    {
        $contentType = $this->contentTypeService->loadContentType($contentInfo->contentTypeId);
        $queryFieldDefinition = $contentType->getFieldDefinition($fieldDefinitionIdentifier);

        $queryType = $this->queryTypeRegistry->getQueryType($queryFieldDefinition->fieldSettings['QueryType']);

        $parameters = $this->resolveParameters(
            json_decode($queryFieldDefinition->fieldSettings['Parameters'], true),
            $contentInfo
        );

        $searchResults = $this->searchService->findContentInfo(
            $queryType->getQuery($parameters)
        );

        return array_map(
            function(SearchHit $searchHit) {
                return $searchHit->valueObject;
            },
            $searchResults->searchHits
        );
    }

    private function resolveParameters(array $parameters, ContentInfo $contentInfo)
    {
        // @todo Only load if it is needed
        $content = $this->contentService->loadContent($contentInfo->id);

        foreach ($parameters as $key => $parameter) {
            if (substr($parameter, 0, 2) === '@=') {
                $language = new ExpressionLanguage();
                $parameters[$key] = $language->evaluate(
                    substr($parameter, 2),
                    [
                        'content' => $content,
                        'contentInfo' => $contentInfo,
                    ]
                );
            }
        }

        return $parameters;
    }
}