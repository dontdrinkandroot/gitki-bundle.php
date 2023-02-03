<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use RuntimeException;
use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;

class DdrGitkiExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritdoc}
     */
    public function prepend(ContainerBuilder $container): void
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $twigConfig = [
            'globals' => [
                'ddr_gitki_display_name' => $config['display_name'],
                'ddr_gitki_show_breadcrumbs' => $config['show_breadcrumbs'],
                'ddr_gitki_show_directory_contents' => $config['show_directory_contents'],
                'ddr_gitki_elasticsearch_enabled' => isset($config['elasticsearch']),
                'ddr_gitki_markdown_show_toc' => $config['markdown']['toc']['enabled'],
                'ddr_gitki_markdown_toc_max_level' => $config['markdown']['toc']['max_level'],
            ],
        ];

        $container->prependExtensionConfig('twig', $twigConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../../config'));
        $loader->load('services.yaml');

        $container->setParameter('ddr_gitki.repository_path', $config['repository_path']);
        $container->setParameter('ddr_gitki.display_name', $config['display_name']);
        $container->setParameter('ddr_gitki_markdown.allow_html', $config['markdown']['allow_html']);
        $container->setParameter('ddr_gitki.show_directory_contents', $config['show_directory_contents']);
        $container->setParameter('ddr_gitki.index_files', $config['index_files']);

        if (isset($config['elasticsearch'])) {
            $this->assertElasticSearchAvailable();
            $container->setParameter('ddr_gitki.elasticsearch.enabled', true);
            $container->setParameter('ddr_gitki.elasticsearch.host', $config['elasticsearch']['host']);
            $container->setParameter('ddr_gitki.elasticsearch.port', $config['elasticsearch']['port']);
            $container->setParameter('ddr_gitki.elasticsearch.index_name', $config['elasticsearch']['index_name']);
        } else {
            $container->setParameter('ddr_gitki.elasticsearch.enabled', false);
        }
//        var_dump($container->getParameter('kernel.environment'));
    }

    /**
     * {@inheritdoc}
     *
     *
     * @return ConfigurationInterface
     */
    public function getConfiguration(array $config, ContainerBuilder $container)
    {
        $configuration = parent::getConfiguration($config, $container);
        if (null === $configuration) {
            throw new RuntimeException('No configuration found');
        }

        return $configuration;
    }

    private function assertElasticSearchAvailable(): void
    {
        if (!class_exists('Elasticsearch\Client')) {
            throw new RuntimeException('You configured elasticsearch but the client is not available');
        }
    }
}
