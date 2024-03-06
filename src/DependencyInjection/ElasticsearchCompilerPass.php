<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use Dontdrinkandroot\CrudAdminBundle\Service\FieldRenderer\FieldRendererProviderInterface;
use Dontdrinkandroot\GitkiBundle\Analyzer\AnalyzerInterface;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepository;
use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchService;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Override;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

class ElasticsearchCompilerPass implements CompilerPassInterface
{
    #[Override]
    public function process(ContainerBuilder $container): void
    {
        $enabled = $container->getParameter('ddr_gitki.elasticsearch.enabled');
        if (!$enabled) {
            return;
        }

        $repositoryDefinition = $container->findDefinition(ElasticsearchRepositoryInterface::class);
        $repositoryDefinition->setClass(ElasticsearchRepository::class);
        $repositoryDefinition->setArguments(
            [
                $container->getParameter('ddr_gitki.elasticsearch.host'),
                $container->getParameter('ddr_gitki.elasticsearch.port'),
                $container->getParameter('ddr_gitki.elasticsearch.index_name'),
            ]
        );

        $serviceDefinition = $container->findDefinition(ElasticsearchServiceInterface::class);
        $serviceDefinition->setClass(ElasticsearchService::class);

        $container
            ->registerForAutoconfiguration(AnalyzerInterface::class)
            ->addTag('ddr.gitki.analyzer');
    }
}
