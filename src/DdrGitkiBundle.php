<?php

namespace Dontdrinkandroot\GitkiBundle;

use Dontdrinkandroot\GitkiBundle\DependencyInjection\ElasticsearchCompilerPass;
use Override;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiBundle extends Bundle
{
    #[Override]
    public function getPath(): string
    {
        return dirname(__DIR__);
    }

    #[Override]
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);
        $container->addCompilerPass(new ElasticsearchCompilerPass());
    }
}
