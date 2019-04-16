<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\DataProvider;

use eZ\Publish\API\Repository\Values\Content\Content;
use EzSystems\EzPlatformGraphQL\GraphQL\Value\Field;
use EzSystems\RepositoryForms\Data\FieldDefinitionData;
use Symfony\Component\Form\FormInterface;

interface DataProvider
{
    /**
     * Adds the provider's form to a field definition configuration form.
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param \EzSystems\RepositoryForms\Data\FieldDefinitionData $fieldDefinitionData
     */
    public function configureForm(FormInterface $form, FieldDefinitionData $fieldDefinitionData);

    /**
     * Returns related data for a given field.
     *
     * @param \EzSystems\EzPlatformGraphQL\GraphQL\Value\Field $field
     * @param \eZ\Publish\API\Repository\Values\Content\Content $content
     *
     * @return Content[]
     */
    public function getData(Field $field, Content $content);

    /**
     * Gets the name of the data provider.
     *
     * @return string
     */
    public function getName(): string;
}