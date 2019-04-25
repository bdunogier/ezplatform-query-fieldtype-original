<?php
/**
 * @copyright Copyright (C) eZ Systems AS. All rights reserved.
 * @license For full copyright and license information view LICENSE file distributed with this source code.
 */

namespace BD\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler;

use BD\EzPlatformQueryFieldType\FieldType\Mapper\QueryFormMapper;
use BD\EzPlatformQueryFieldType\FieldType\Query\SearchField;
use BD\EzPlatformQueryFieldType\FieldType\Query\Type as FieldType;
use BD\EzPlatformQueryFieldType\Persistence\Legacy\Content\FieldValue\Converter\QueryConverter;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class DefineVariableFieldTypesPass implements CompilerPassInterface
{
    const PROVIDER_TAG = 'ez_dynamic_relations.provider';

    public function process(ContainerBuilder $container)
    {
        // iterate over (tagged) providers
        foreach ($container->findTaggedServiceIds(self::PROVIDER_TAG) as $id => $tags) {
            foreach ($tags as $tag) {
                if (!isset($tag['identifier'])) {
                    throw new \InvalidArgumentException("The ez_dynamic_relations.provider tag requires an 'identifier' property set to the provider's identifier");
                }

                $identifier = $tag['identifier'];

                // define the fieldtype service
                $this->defineFieldTypeService($container, $identifier);
                $this->defineFormMapperService($container, $identifier, $id);
                $this->defineSearchFieldService($container, $identifier);
                $this->defineConverterService($container, $identifier);
                // define templates ?
            }
        }
    }

    private function defineFieldTypeService(ContainerBuilder $container, $identifier)
    {
        $definition = $container->getDefinition(FieldType::class);
        $definition->addTag('ezpublish.fieldType', ['alias' => 'query_' . $identifier]);
        $container->setDefinition(FieldType::class, $definition);
    }

    private function defineFormMapperService(ContainerBuilder $container, $identifier, $providerServiceId)
    {
        $definition = new Definition(QueryFormMapper::class);
        $definition->setArgument('$provider', new Reference($providerServiceId));
        $definition->addTag('ez.fieldFormMapper.definition', ['fieldType' => 'query_' . $identifier]);
        $definition->addTag('ez.fieldFormMapper.value', ['fieldType' => 'query_' . $identifier]);
        $container->setDefinition(QueryFormMapper::class . '\\' . ucfirst($identifier), $definition);
    }

    private function defineSearchFieldService(ContainerBuilder $container, $identifier)
    {
        $definition = $container->getDefinition(SearchField::class);
        $definition->addTag('ezpublish.fieldType.indexable', ['alias' => 'query_' . $identifier]);
        $container->setDefinition(SearchField::class . '\\' . ucfirst($identifier), $definition);
    }

    private function defineConverterService(ContainerBuilder $container, $identifier)
    {
        $definition = $container->getDefinition(QueryConverter::class);
        $definition->addTag('ezpublish.storageEngine.legacy.converter', ['alias' => 'query_' . $identifier]);
        $container->setDefinition(QueryConverter::class, $definition);
    }
}