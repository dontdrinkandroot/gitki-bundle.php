<?php

namespace Dontdrinkandroot\GitkiBundle\Tests;

use Override;
use PHPUnit\Framework\TestCase;

class GitRepositoryTestCase extends TestCase
{
    final public const string GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    use GitRepositoryTestTrait;

    #[Override]
    public function setUp(): void
    {
        $this->setUpRepo();
    }

    #[Override]
    public function tearDown(): void
    {
        $this->tearDownRepo();
    }

    /**
     * @psalm-return '/tmp/gitkitest/repo/'
     */
    #[Override]
    protected function getRepositoryTargetPath(): string
    {
        return self::GIT_REPOSITORY_PATH;
    }
}
