<?php

namespace Dontdrinkandroot\GitkiBundle\Tests;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Psr\Container\ContainerInterface;

trait ElasticsearchReindexTrait
{
    public function reindex(ContainerInterface $container, int $sleepSeconds = 1): void
    {
        $wikiService = $container->get(WikiService::class);
        $elasticSearchService = $container->get(ElasticsearchServiceInterface::class);
        $elasticSearchService->clearIndex();
        $filePaths = $wikiService->findAllFiles();
        foreach ($filePaths as $filePath) {
            $elasticSearchService->indexFile($filePath);
        }
        sleep($sleepSeconds);
    }
}
