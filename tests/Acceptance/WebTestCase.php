<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestTrait;
use Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security\StaticUserProvider;
use Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security\User;
use Override;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase as BaseWebTestCase;

abstract class WebTestCase extends BaseWebTestCase
{
    use GitRepositoryTestTrait;

    public const string GIT_REPOSITORY_PATH = '/tmp/gitkitest/repo/';

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
        $this->setUpRepo();
    }

    #[Override]
    public function tearDown(): void
    {
        parent::tearDown();
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

    /**
     * @template T
     * @param class-string<T> $class
     * @return T
     */
    protected static function getService(string $class, ?string $id = null): object
    {
        if (null === $id) {
            $id = $class;
        }

        return Asserted::instanceOf(self::getContainer()->get($id), $class);
    }

    protected static function getUser(string $identifier): User
    {
        $user = self::getService(StaticUserProvider::class)->loadUserByIdentifier($identifier);
        return Asserted::instanceOf($user, User::class);
    }

    abstract protected function getEnvironment(): string;
}
