<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Functional;

use Dontdrinkandroot\GitkiBundle\Tests\Functional\app\AppKernel;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Liip\FunctionalTestBundle\Test\WebTestCase;

abstract class FunctionalTest extends WebTestCase
{
    const GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    use GitRepositoryTestTrait;

    public function setUp()
    {
        $this->setUpRepo();
    }

    public function tearDown()
    {
        $this->tearDownRepo();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryTargetPath()
    {
        return self::GIT_REPOSITORY_PATH;
    }

    /**
     * {@inheritdoc}
     */
    protected static function getKernelClass()
    {
        return AppKernel::class;
    }
}