<?php

namespace BD\EzPlatformQueryFieldTypeBundle;

use BD\EzPlatformQueryFieldTypeBundle\DependencyInjection\Compiler\QueryTypesListPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BDEzPlatformQueryFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new QueryTypesListPass());
    }
}
