<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Service;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitService;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security\User;
use Dontdrinkandroot\Path\FilePath;
use Override;

class GitServiceTest extends GitRepositoryTestCase
{
    protected FileSystemService $fileSystemService;

    protected GitService $gitService;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();

        $this->fileSystemService = new FileSystemService(GitRepositoryTestCase::GIT_REPOSITORY_PATH);
        $this->gitService = new GitService($this->fileSystemService);
    }

    public function testAddAndCommit(): void
    {
        $user = new User('tester', 'Tester', 'test@example.com');

        /* Test with spaces */

        $filePath = FilePath::parse('A filename with spaces.txt');
        $this->gitService->putAndCommitFile($user, $filePath, 'asdf', 'Added A filename with spaces.txt');
        $this->assertTrue($this->gitService->exists($filePath));

        $history = $this->gitService->getFileHistory($filePath);
        $this->assertCount(1, $history);

        /** @var CommitMetadata $firstEntry */
        $firstEntry = $history[0];
        $this->assertEquals('Added A filename with spaces.txt', $firstEntry->getMessage());
        $this->assertEquals('test@example.com', $firstEntry->getEmail());
        $this->assertEquals('Tester', $firstEntry->getCommitter());

        /* Test with umlauts */

        $filePath = FilePath::parse('A filename with Ümläuts.txt');
        $this->gitService->putAndCommitFile($user, $filePath, 'asdf', 'Added A filename with Ümläuts.txt');
        $this->assertTrue($this->gitService->exists($filePath));

        $history = $this->gitService->getFileHistory($filePath);
        $this->assertCount(1, $history);

        /** @var CommitMetadata $firstEntry */
        $firstEntry = $history[0];
        $this->assertEquals('Added A filename with Ümläuts.txt', $firstEntry->getMessage());
        $this->assertEquals('test@example.com', $firstEntry->getEmail());
        $this->assertEquals('Tester', $firstEntry->getCommitter());
    }
}
