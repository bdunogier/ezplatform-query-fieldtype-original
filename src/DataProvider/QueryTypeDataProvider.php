<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace DataProvider;

use BD\EzPlatformQueryFieldType\DataProvider\DataProvider;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

class QueryTypeDataProvider implements DataProvider
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

    /**
     * List of query types
     * @var array
     */
    private $queryTypes;

    public function __construct(
        ContentLoader $contentLoader,
        ContentTypeLoader $contentTypeLoader,
        QueryTypeRegistry $queryTypeRegistry,
        array $queryTypes = []
    ) {
        $this->contentLoader = $contentLoader;
        $this->contentTypeLoader = $contentTypeLoader;
        $this->queryTypeRegistry = $queryTypeRegistry;
        $this->queryTypes = $queryTypes;
    }

    public function configureForm(FormInterface $form, FieldDefinitionData $fieldDefinitionData)
    {
        $form
            ->add('QueryType',Type\ChoiceType::class,
                [
                    'label' => 'Query type',
                    'property_path' => 'fieldSettings[QueryType]',
                    'choices' => $this->queryTypes,
                ]
            )
            ->add('ReturnedType', Type\ChoiceType::class,
                [
                    'label' => 'Returned type',
                    'property_path' => 'fieldSettings[ReturnedType]',
                    'choices' => $this->getContentTypes(),
                ]
            )
            ->add('Parameters', Type\TextareaType::class,
                [
                    'label' => 'Parameters',
                    'property_path' => 'fieldSettings[Parameters]'
                ]
            );
    }

    public function getData(Field $field, Content $content)
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

    public function getName(): string
    {
        return 'Query type';
    }

    private function getContentTypes()
    {
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                yield $contentType->getName() => $contentType->identifier;
            }
        }
    }
}