<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Service;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitService;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestUser;
use Dontdrinkandroot\Path\FilePath;

class GitServiceTest extends GitRepositoryTestCase
{

    /**
     * @var FileSystemService
     */
    protected $fileSystemService;

    /**
     * @var GitService
     */
    protected $gitService;

    public function setUp()
    {
        parent::setUp();
        $this->fileSystemService = new FileSystemService(GitRepositoryTestCase::GIT_REPOSITORY_PATH);
        $this->gitService = new GitService($this->fileSystemService);
    }

    public function testAddAndCommit()
    {
        $user = new TestUser('Tester', 'test@example.com');

        $filePath = FilePath::parse('test.txt');
        $this->gitService->putAndCommitFile($user, $filePath, 'asdf', 'Added test.txt');
        $this->assertTrue($this->gitService->exists($filePath));

        $history = $this->gitService->getFileHistory($filePath);
        $this->assertCount(1, $history);

        /** @var CommitMetadata $firstEntry */
        $firstEntry = $history[0];
        $this->assertEquals('Added test.txt', $firstEntry->getMessage());
        $this->assertEquals('test@example.com', $firstEntry->getEmail());
        $this->assertEquals('Tester', $firstEntry->getCommitter());
    }
}
