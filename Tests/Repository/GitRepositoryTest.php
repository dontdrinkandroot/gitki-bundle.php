<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Repository;

use Dontdrinkandroot\GitkiBundle\Model\CommitMetadata;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestUser;
use Dontdrinkandroot\Path\FilePath;

class GitRepositoryTest extends GitRepositoryTestCase
{

    protected function createGitRepository()
    {
        return new \Dontdrinkandroot\GitkiBundle\Service\Git\GitService(self::TEST_PATH);
    }

    public function testAddAndCommit()
    {
        $user = new TestUser('Tester', 'test@example.com');

        $gitRepository = $this->createGitRepository();
        $filePath = FilePath::parse('test.txt');
        $gitRepository->putContent($filePath, 'asdf');
        $gitRepository->addAndCommit($user, 'Added test.txt', $filePath);
        $this->assertTrue($gitRepository->exists($filePath));

        $history = $gitRepository->getFileHistory($filePath);
        $this->assertCount(1, $history);

        /** @var CommitMetadata $firstEntry */
        $firstEntry = $history[0];
        $this->assertEquals('Added test.txt', $firstEntry->getMessage());
        $this->assertEquals('test@example.com', $firstEntry->getEmail());
        $this->assertEquals('Tester', $firstEntry->getCommitter());
    }
}
