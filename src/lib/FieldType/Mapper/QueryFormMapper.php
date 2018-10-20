<?php

namespace BD\PlatformQueryFieldType\FieldType\Mapper;

use BD\PlatformQueryFieldType\Form\Type\FieldType\QueryFieldType;
use eZ\Publish\API\Repository\ContentTypeService;
use EzSystems\RepositoryForms\Data\Content\FieldData;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use EzSystems\RepositoryForms\FieldType\FieldDefinitionFormMapperInterface;
use EzSystems\RepositoryForms\FieldType\FieldValueFormMapperInterface;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class QueryFormMapper implements FieldDefinitionFormMapperInterface, FieldValueFormMapperInterface
{
    /**
     * @var ContentTypeService
     */
    private $contentTypeService;

    public function __construct(ContentTypeService $contentTypeService)
    {
        $this->contentTypeService = $contentTypeService;
    }

    public function mapFieldDefinitionForm(FormInterface $fieldDefinitionForm, FieldDefinitionData $data)
    {
        $fieldDefinitionForm
            ->add('QueryType',Type\ChoiceType::class,
                [
                    'label' => 'Query type',
                    'property_path' => 'fieldSettings[QueryType]',
                    'choices' => [
                        // @todo use QueryType registry or something
                        'Nearby places' => 'NearbyPlaces',
                        'Children' => 'Children'
                    ]
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

    public function mapFieldValueForm(FormInterface $fieldForm, FieldData $data)
    {
        $fieldDefinition = $data->fieldDefinition;
        $formConfig = $fieldForm->getConfig();
        $validatorConfiguration = $fieldDefinition->getValidatorConfiguration();
        $names = $fieldDefinition->getNames();
        $label = $fieldDefinition->getName($formConfig->getOption('mainLanguageCode')) ?: reset($names);

        $fieldForm
            ->add(
                $formConfig->getFormFactory()->createBuilder()
                    ->create(
                        'value',
                        QueryFieldType::class,
                        [
                            'required' => $fieldDefinition->isRequired,
                            'label' => $label,
                        ]
                    )
                    ->setAutoInitialize(false)
                    ->getForm()
            );
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'translation_domain' => 'ezrepoforms_content_type',
            ]);
    }

    private function getContentTypes()
    {
        foreach ($this->contentTypeService->loadContentTypeGroups() as $contentTypeGroup) {
            foreach ($this->contentTypeService->loadContentTypes($contentTypeGroup) as $contentType) {
                yield $contentType->identifier => $contentType->getName();
            }
        }
    }
}
