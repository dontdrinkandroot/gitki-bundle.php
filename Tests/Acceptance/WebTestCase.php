<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\app\AppKernel;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Symfony\Bundle\FrameworkBundle\Client;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use GitRepositoryTestTrait;

    const GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    /**
     * @var Client
     */
    protected $client;

    public function setUp()
    {
        $this->setUpRepo();
        $this->client = static::createClient(['environment' => $this->getEnvironment()]);
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

    protected function assertStatusCode(int $expectedCode)
    {
        $this->assertEquals($expectedCode, $this->client->getResponse()->getStatusCode());
    }

    abstract protected function getEnvironment(): string;
}