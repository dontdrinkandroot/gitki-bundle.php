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
    public function setUp()
    {
        $this->setUpRepo();
    }

    /**
     * {@inheritdoc}
     */
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
}
