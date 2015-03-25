<?php

namespace Dontdrinkandroot\GitkiBundle;

use Dontdrinkandroot\GitkiBundle\DependencyInjection\DirectoryActionHandlerCompilerPass;
use Dontdrinkandroot\GitkiBundle\DependencyInjection\EditorCompilerPass;
use Dontdrinkandroot\GitkiBundle\DependencyInjection\ElasticsearchCompilerPass;
use Dontdrinkandroot\GitkiBundle\DependencyInjection\FileActionHandlerCompilerPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DdrGitkiBundle extends Bundle
{

    public function build(ContainerBuilder $container)
    {
        parent::build($container);
        $container->addCompilerPass(new ElasticsearchCompilerPass());
    }

}
