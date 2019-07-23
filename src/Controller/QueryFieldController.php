<?php
namespace BD\EzPlatformQueryFieldType\Controller;

use BD\EzPlatformQueryFieldType\API\QueryFieldService;
use BD\EzPlatformQueryFieldType\GraphQL\QueryFieldResolver;
use eZ\Publish\Core\MVC\Symfony\View\ContentView;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field as GraphQLField;

class QueryFieldController
{
    /**
     * @var \BD\EzPlatformQueryFieldType\API\QueryFieldService
     */
    private $queryFieldService;

    public function __construct(QueryFieldService $queryFieldService)
    {
        $this->queryFieldService = $queryFieldService;
    }

    public function renderQueryFieldAction(ContentView $view, $queryFieldDefinitionIdentifier)
    {
        $view->addParameters([
            'children_view_type' => 'line',
            'query_results' => $this->queryFieldService->loadFieldData(
                $view->getContent(),
                $queryFieldDefinitionIdentifier
            )
        ]);

        return $view;
    }
}