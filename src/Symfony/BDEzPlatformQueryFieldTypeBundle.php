<?php

namespace BD\EzPlatformQueryFieldType\Symfony;

use BD\EzPlatformQueryFieldType\Symfony\DependencyInjection\Compiler\QueryTypesListPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BDEzPlatformQueryFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new QueryTypesListPass());
    }
}
