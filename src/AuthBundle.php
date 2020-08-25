<?php

namespace Damian972\AuthBundle;

use Damian972\AuthBundle\DependencyInjection\AuthCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class AuthBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new AuthCompilerPass());
    }
}
