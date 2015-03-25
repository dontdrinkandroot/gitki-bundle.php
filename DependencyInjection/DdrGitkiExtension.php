<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

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
    public function prepend(ContainerBuilder $container)
    {
        $configs = $container->getExtensionConfig($this->getAlias());
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $twigConfig = [
            'globals' => [
                'ddr_gitki_name'                   => $config['name'],
                'ddr_gitki_show_breadcrumbs'       => $config['show_breadcrumbs'],
                'ddr_gitki_elasticsearch_enabled'  => isset($config['elasticsearch']),
                'ddr_gitki_markdown_show_toc'      => $config['markdown']['toc']['enabled'],
                'ddr_gitki_markdown_toc_max_level' => $config['markdown']['toc']['max_level']
            ]
        ];

        $container->prependExtensionConfig('twig', $twigConfig);
    }

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = $this->getConfiguration($configs, $container);
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\YamlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.yml');

        $container->setParameter('ddr_gitki.repository_path', $config['repository_path']);
        $container->setParameter('ddr_gitki.name', $config['name']);
        $container->setParameter('ddr_gitki_markdown.allow_html', $config['markdown']['allow_html']);

        if (isset($config['elasticsearch'])) {
            $container->setParameter('ddr_gitki.elasticsearch.enabled', true);
            $container->setParameter('ddr_gitki.elasticsearch.host', $config['elasticsearch']['host']);
            $container->setParameter('ddr_gitki.elasticsearch.port', $config['elasticsearch']['port']);
            $container->setParameter('ddr_gitki.elasticsearch.index_name', $config['elasticsearch']['index_name']);
        } else {
            $container->setParameter('ddr_gitki.elasticsearch.enabled', false);
        }

        $container->setParameter('ddr_gitki.role.watcher', $config['roles']['watcher']);
        $container->setParameter('ddr_gitki.role.committer', $config['roles']['committer']);
        $container->setParameter('ddr_gitki.role.admin', $config['roles']['admin']);
    }
}
