<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase as BaseKernelTestCase;

abstract class KernelTestCase extends BaseKernelTestCase
{
    use GitRepositoryTestTrait;

    public const string GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    #[Override]
    public function setUp(): void
    {
        $this->setUpRepo();
    }

    protected function getRepositoryTargetPath(): string
    {
        return self::GIT_REPOSITORY_PATH;
    }
}
