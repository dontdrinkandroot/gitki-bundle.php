<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Collects editable file types.
 */
class EditorCompilerPass implements CompilerPassInterface
{

    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ddr.gitki.service.wiki')) {
            return;
        }

        $wikiServiceDefinition = $container->getDefinition('ddr.gitki.service.wiki');

        $taggedServices = $container->findTaggedServiceIds('ddr.gitki.editor');

        foreach ($taggedServices as $id => $tags) {
            foreach ($tags as $attributes) {
                if (!isset($attributes['extension'])) {
                    throw new \Exception('Extension must be given');
                }
                $extension = $attributes['extension'];
                $wikiServiceDefinition->addMethodCall(
                    'registerEditableExtension',
                    [$extension]
                );
            }
        }
    }
}