<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('ddr_gitki');
        $rootNode = $treeBuilder->getRootNode();

        // @formatter:off
        $rootNode
            ->children()
                ->scalarNode('repository_path')
                    ->info('The path to the git repository containing the wiki files. Must end with slash.')
                    ->isRequired()
                ->end()
                ->scalarNode('display_name')
                    ->defaultValue('GitKi')
                    ->info('The name of the wiki that is to be used within all templates')
                ->end()
                ->booleanNode('show_breadcrumbs')
                    ->defaultTrue()
                    ->info('When enabled breadcrumbs are shown for easy navigation')
                ->end()
                ->booleanNode('show_directory_contents')
                    ->defaultTrue()
                    ->info('When enabled the files and folders of the containing directory are shown while viewing a file')
                ->end()
            ->end();
        // @formatter:on

        $this->addMarkdownSection($rootNode);
        $this->addElasticsearchSection($rootNode);
        $this->addIndexFilesSection($rootNode);

        return $treeBuilder;
    }

    private function addMarkdownSection(ArrayNodeDefinition $node): void
    {
        // @formatter:off
        $node
            ->children()
            ->arrayNode('markdown')
            ->info('Markdown specific configuration')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('allow_html')
            ->defaultFalse()
            ->info('When disabled all html content is escaped')
            ->end()
            ->arrayNode('toc')
            ->addDefaultsIfNotSet()
            ->children()
            ->booleanNode('enabled')
            ->defaultTrue()
            ->info('Show the table of contents')
            ->end()
            ->integerNode('max_level')
            ->min(1)->max(6)->defaultValue(3)
            ->info('Max depth of the table of contents')
            ->end()
            ->end()
            ->end()
            ->end()
            ->end()
            ->end();
        // @formatter:on
    }

    private function addElasticsearchSection(ArrayNodeDefinition $node): void
    {
        // @formatter:off
        $node
            ->children()
            ->arrayNode('elasticsearch')
            ->info('Configure elasticsearch integration')
            ->children()
            ->scalarNode('index_name')->isRequired()->end()
            ->scalarNode('host')->defaultValue('localhost')->end()
            ->integerNode('port')->defaultValue(9200)->end()
            ->end()
            ->end()
            ->end();
        // @formatter:on
    }

    private function addIndexFilesSection(ArrayNodeDefinition $node): void
    {
        // @formatter:off
        $node
            ->children()
            ->arrayNode('index_files')
            ->info('The file names that are used as a directory index. Searched in the order defined.')
            ->prototype('scalar')->end()
            ->defaultValue(['index.md', 'README.md', 'index.txt', 'README.txt'])
            ->end()
            ->end();
        // @formatter:on
    }
}
