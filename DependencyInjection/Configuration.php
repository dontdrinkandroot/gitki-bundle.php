<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ddr_gitki');

        // @formatter:off
        $rootNode
            ->children()
                ->scalarNode('repository_path')->isRequired()->end()
                ->scalarNode('name')->defaultValue('GitKi')->end()
                ->booleanNode('show_breadcrumbs')->defaultTrue()->end()
                ->arrayNode('markdown')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('allow_html')->defaultFalse()->end()
                        ->arrayNode('toc')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->integerNode('max_level')->defaultValue(3)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->scalarNode('index_name')->isRequired()->end()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->integerNode('port')->defaultValue(9200)->end()
                    ->end()
                ->end()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('watcher')->defaultValue('IS_AUTHENTICATED_ANONYMOUSLY')->end()
                        ->scalarNode('committer')->defaultValue('ROLE_USER')->end()
                        ->scalarNode('admin')->defaultValue('ROLE_USER')->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on

        $this->postProcessRootNode($rootNode);

        return $treeBuilder;
    }

    protected function postProcessRootNode($rootNode)
    {
    }
}
