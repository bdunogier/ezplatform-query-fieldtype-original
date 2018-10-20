<?php

namespace BD\PlatformQueryFieldTypeBundle;

use BD\PlatformQueryFieldTypeBundle\DependencyInjection\Compiler\QueryTypesListPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class BDPlatformQueryFieldTypeBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new QueryTypesListPass());
    }
}
