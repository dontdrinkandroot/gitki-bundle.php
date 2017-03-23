<?php

namespace Dontdrinkandroot\GitkiBundle\Tests;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class GitRepositoryTestCase extends \PHPUnit_Framework_TestCase
{
    const GIT_REPOSITORY_PATH = '/tmp/gitkitest/';

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
}
