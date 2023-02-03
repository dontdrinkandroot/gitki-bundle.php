<?php

namespace Dontdrinkandroot\GitkiBundle\Tests;

use PHPUnit\Framework\TestCase;

class GitRepositoryTestCase extends TestCase
{
    final const GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    use GitRepositoryTestTrait;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        $this->setUpRepo();
    }

    /**
     * {@inheritdoc}
     */
    public function tearDown(): void
    {
        $this->tearDownRepo();
    }

    /**
     * {@inheritdoc}
     *
     *
     * @psalm-return '/tmp/gitkitest/repo/'
     */
    protected function getRepositoryTargetPath(): string
    {
        return self::GIT_REPOSITORY_PATH;
    }
}
