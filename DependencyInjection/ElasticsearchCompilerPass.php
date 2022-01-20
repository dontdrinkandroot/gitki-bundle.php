<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use Dontdrinkandroot\CrudAdminBundle\Service\FieldRenderer\FieldRendererProviderInterface;
use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzerInterface;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ElasticsearchCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container): void
    {
        $enabled = $container->getParameter('ddr_gitki.elasticsearch.enabled');
        if (!$enabled) {
            return;
        }

        $repositoryDefinition = $container->findDefinition(
            'Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface'
        );
        $repositoryDefinition->setClass('Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepository');
        $repositoryDefinition->setArguments(
            [
                $container->getParameter('ddr_gitki.elasticsearch.host'),
                $container->getParameter('ddr_gitki.elasticsearch.port'),
                $container->getParameter('ddr_gitki.elasticsearch.index_name'),
            ]
        );

        $serviceDefinition = $container->findDefinition(
            'Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface'
        );
        $serviceDefinition->setClass('Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchService');

        $container
            ->registerForAutoconfiguration(AnalyzerInterface::class)
            ->addTag('ddr.gitki.analyzer');
    }
}
