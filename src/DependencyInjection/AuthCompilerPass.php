<?php

namespace Damian972\AuthBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class AuthCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
    }
}
