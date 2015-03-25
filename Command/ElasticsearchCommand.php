<?php


namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Repository\ElasticsearchRepositoryInterface;

abstract class ElasticsearchCommand extends GitkiContainerAwareCommand
{

    /**
     * @return ElasticsearchRepositoryInterface
     */
    protected function getElasticsearchRepository()
    {
        return $this->getContainer()->get('ddr.gitki.repository.elasticsearch');
    }
}
