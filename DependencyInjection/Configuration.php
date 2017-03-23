<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
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
                ->scalarNode('repository_path')
                    ->info('The path to the git repository containing the wiki files. Must end with slash.')
                    ->isRequired()
                ->end()
                ->scalarNode('name')->defaultValue('GitKi')->end()
                ->booleanNode('show_breadcrumbs')->defaultTrue()->end()
                ->booleanNode('show_directory_contents')->defaultTrue()->end()
            ->end();
        // @formatter:on

        $this->addMarkdownSection($rootNode);
        $this->addElasticsearchSection($rootNode);
        $this->addRolesSection($rootNode);
        $this->addIndexFilesSection($rootNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addMarkdownSection(ArrayNodeDefinition $node)
    {
        // @formatter:off
        $node
            ->children()
                ->arrayNode('markdown')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('allow_html')->defaultFalse()->end()
                        ->arrayNode('toc')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('enabled')->defaultTrue()->end()
                                ->integerNode('max_level')->min(1)->max(6)->defaultValue(3)->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addElasticsearchSection(ArrayNodeDefinition $node)
    {
        // @formatter:off
        $node
            ->children()
                ->arrayNode('elasticsearch')
                    ->children()
                        ->scalarNode('index_name')->isRequired()->end()
                        ->scalarNode('host')->defaultValue('localhost')->end()
                        ->integerNode('port')->defaultValue(9200)->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addRolesSection(ArrayNodeDefinition $node)
    {
        // @formatter:off
        $node
            ->children()
                ->arrayNode('roles')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('watcher')->defaultValue('IS_AUTHENTICATED_ANONYMOUSLY')->end()
                        ->scalarNode('committer')->defaultValue('ROLE_USER')->end()
                        ->scalarNode('admin')->defaultValue('ROLE_ADMIN')->end()
                    ->end()
                ->end()
            ->end();
        // @formatter:on
    }

    /**
     * @param ArrayNodeDefinition $node
     */
    private function addIndexFilesSection(ArrayNodeDefinition $node)
    {
        // @formatter:off
        $node
            ->children()
                ->arrayNode('index_files')
                    ->info(
                        'The file names that are used as a directory index. Searched in the order defined.'
                    )
                    ->prototype('scalar')->end()
                    ->defaultValue(['index.md', 'README.md','index.txt','README.txt'])
                ->end()
            ->end();
        // @formatter:on
    }
}
