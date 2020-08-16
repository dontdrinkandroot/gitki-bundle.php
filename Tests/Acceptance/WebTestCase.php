<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use GitRepositoryTestTrait;

    const GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpRepo();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        parent::tearDown();
        $this->tearDownRepo();
    }

    /**
     * {@inheritdoc}
     */
    protected function getRepositoryTargetPath()
    {
        return self::GIT_REPOSITORY_PATH;
    }

    abstract protected function getEnvironment(): string;
}
