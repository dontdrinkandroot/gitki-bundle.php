<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Service;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepository;
use Dontdrinkandroot\GitkiBundle\Repository\GitRepositoryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\RepositoryAwareMarkdownService;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestUser;
use Dontdrinkandroot\Path\FilePath;

class RepositoryAwareMarkdownServiceTest extends GitRepositoryTestCase
{

    /**
     * @var GitRepositoryInterface
     */
    protected $gitRepository;

    /**
     * @var GitUserInterface
     */
    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->gitRepository = new GitRepository(GitRepositoryTestCase::TEST_PATH);#
        $this->user = new TestUser('Tester', 'test@example.com');

        $example1Content = file_get_contents(__DIR__ . '/../Data/example1.md');
        $example1Path = new FilePath('example1.md');

        $this->gitRepository->putContent($example1Path, $example1Content);
        $this->gitRepository->addAndCommit($this->user, 'Adding example1', $example1Path);
    }

    public function testParseWithHtmlEnabled()
    {
        //TODO: Implement
        $this->markTestSkipped('Needs to be implemented');
        $markdownService = new RepositoryAwareMarkdownService($this->gitRepository, true);
    }
}
