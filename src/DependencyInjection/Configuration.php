<?php

namespace Damian972\AuthBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('auth');
        /**
         * @var ArrayNodeDefinition
         */
        $rootNode = $treeBuilder->getRootNode();

        $this->addLoginAttemptConfig($rootNode);

        return $treeBuilder;
    }

    /**
     * $tree['auth'] = [
     *  'max_login_attempts' => '',
     *  'token_expire_after' => ''
     * ].
     *
     * @param ArrayNodeDefinition
     */
    private function addLoginAttemptConfig(ArrayNodeDefinition $rootNode): void
    {
        $rootNode
            ->children()
            ->scalarNode('max_login_attempts')->cannotBeEmpty()->isRequired()->end()
            ->scalarNode('token_expire_after')->cannotBeEmpty()->isRequired()->end()
            ->end()
        ;
    }
}
