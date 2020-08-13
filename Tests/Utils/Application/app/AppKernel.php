<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Utils\Application\app;

use Psr\Log\NullLogger;
use RuntimeException;
use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundlesFile = $this->getEnvConfigDir() . '/bundles.php';
        if (!file_exists($bundlesFile)) {
            throw new RuntimeException($bundlesFile . ' is missing');
        }

        return include $bundlesFile;
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $resource = $this->getEnvConfigDir() . '/config.yml';
        $loader->load($resource);
    }

    /**
     * {@inheritdoc}
     */
    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/ddr_gitki_bundle/cache/';
    }

    /**
     * {@inheritdoc}
     */
    public function getLogDir()
    {
        return sys_get_temp_dir() . '/ddr_gitki_bundle/logs/';
    }

    /**
     * @return string
     */
    public function getEnvConfigDir(): string
    {
        return $this->getProjectDir() . '/Tests/Utils/Application/app/config/' . $this->getEnvironment();
    }

    protected function build(ContainerBuilder $container)
    {
        $container->register(NullLogger::class)->setDecoratedService('logger');
    }
}
