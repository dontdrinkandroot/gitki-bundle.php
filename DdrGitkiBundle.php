<?php

namespace Dontdrinkandroot\GitkiBundle;

use Dontdrinkandroot\GitkiBundle\DependencyInjection\ElasticsearchCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ElasticsearchCompilerPass());
    }
}
