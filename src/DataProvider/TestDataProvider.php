<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\DataProvider;

use BD\EzPlatformQueryFieldType\DataProvider\DataProvider;
use eZ\Publish\API\Repository\ContentTypeService;
use eZ\Publish\API\Repository\Values\Content\Content;
use eZ\Publish\Core\QueryType\QueryTypeRegistry;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\DataLoader\ContentTypeLoader;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;

class TestDataProvider implements DataProvider
{
    /**
     * @var \eZ\Publish\API\Repository\ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService) {
        $this->contentTypeService = $contentTypeService;
    }

    public function configureFieldDefinitionForm(FormInterface $form, FieldDefinitionData $fieldDefinitionData)
    {
        $form
            ->add('ContentId',Type\NumberType::class,
                [
                    'label' => 'Content id',
                    'property_path' => 'fieldSettings[ContentId]'
                ]
            );
    }

    /**
     * @todo we need the field settings + parameters. Getting them requires the contentTypeService.
     */
    public function getData(Field $field, Content $content)
    {
        $queryFieldDefinition =
            $this
                ->contentTypeService->loadContentType($content->contentInfo->contentTypeId)
                ->getFieldDefinition($field->fieldDefIdentifier);

        return $queryFieldDefinition->fieldSettings['ContentId'];
    }

    public function getName(): string
    {
        return 'Test';
    }
}