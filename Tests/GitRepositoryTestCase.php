<?php

namespace Dontdrinkandroot\GitkiBundle\Tests;

use PHPUnit\Framework\TestCase;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class GitRepositoryTestCase extends TestCase
{
    const GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

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
     * @return string
     *
     * @psalm-return '/tmp/gitkitest/repo/'
     */
    protected function getRepositoryTargetPath()
    {
        return self::GIT_REPOSITORY_PATH;
    }
}
