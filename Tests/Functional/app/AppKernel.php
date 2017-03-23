<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Functional\app;

use Symfony\Component\Config\Loader\LoaderInterface;
use Symfony\Component\HttpKernel\Kernel;

class AppKernel extends Kernel
{
    /**
     * @var array
     */
    private $bundleClasses;

    public function __construct($environment, $debug, $bundleClasses = [])
    {
        parent::__construct($environment, $debug);
        $this->bundleClasses = $bundleClasses;
    }

    /**
     * {@inheritdoc}
     */
    public function registerBundles()
    {
        $bundlesFile = $this->getRootDir() . '/config/' . $this->getEnvironment() . '/bundles.php';
        if (!file_exists($bundlesFile)) {
            throw new \RuntimeException($bundlesFile . ' is missing');
        }

        return include $bundlesFile;
    }

    public function getCacheDir()
    {
        return sys_get_temp_dir() . '/gitkitest/cache/';
    }

    public function getLogDir()
    {
        return sys_get_temp_dir() . '/gitkitest/logs';
    }

    /**
     * {@inheritdoc}
     */
    public function registerContainerConfiguration(LoaderInterface $loader)
    {
        $resource = $this->getRootDir() . '/config/' . $this->getEnvironment() . '/config.yml';
        $loader->load($resource);
    }
}
