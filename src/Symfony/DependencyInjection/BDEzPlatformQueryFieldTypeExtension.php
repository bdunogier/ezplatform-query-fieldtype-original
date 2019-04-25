<?php
namespace BD\EzPlatformQueryFieldType\Symfony\DependencyInjection;

use Symfony\Component\Config\Resource\FileResource;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\Yaml\Yaml;

class BDEzPlatformQueryFieldTypeExtension extends Extension implements PrependExtensionInterface
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );

        $loader->load('fieldtypes.yml');
        $loader->load('indexable_fieldtypes.yml');
        $loader->load('field_value_converters.yml');
        $loader->load('graphql.yml');
        $loader->load('data_providers.yml');
    }

    public function prepend(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('assetic', ['bundles' => ['BDEzPlatformQueryFieldTypeBundle']]);

        $configFile = __DIR__ . '/../Resources/config/field_templates.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));

        $configFile = __DIR__ . '/../Resources/config/field_templates_ui.yml';
        $config = Yaml::parse(file_get_contents($configFile));
        $container->prependExtensionConfig('ezpublish', $config);
        $container->addResource(new FileResource($configFile));
    }
}
