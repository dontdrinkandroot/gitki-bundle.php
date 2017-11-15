<?php

namespace Dontdrinkandroot\GitkiBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ElasticsearchCompilerPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        $enabled = $container->getParameter('ddr_gitki.elasticsearch.enabled');
        if (!$enabled) {
            return;
        }

        $repositoryDefinition = $container->findDefinition('ddr.gitki.repository.elasticsearch');
        $repositoryDefinition->setClass('Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepository');
        $repositoryDefinition->setArguments(
            [
                $container->getParameter('ddr_gitki.elasticsearch.host'),
                $container->getParameter('ddr_gitki.elasticsearch.port'),
                $container->getParameter('ddr_gitki.elasticsearch.index_name'),
            ]
        );

        $serviceDefinition = $container->findDefinition('ddr.gitki.service.elasticsearch');
        $serviceDefinition->setClass('Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchService');

        $taggedServices = $container->findTaggedServiceIds('ddr.gitki.analyzer');

        foreach ($taggedServices as $id => $tags) {
            $serviceDefinition->addMethodCall(
                'registerAnalyzer',
                [new Reference($id)]
            );
        }
    }
}
