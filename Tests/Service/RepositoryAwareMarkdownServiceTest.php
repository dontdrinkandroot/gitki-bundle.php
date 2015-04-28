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

    /**
     * @var FilePath
     */
    private $example1Path;

    private $example1Content;

    public function setUp()
    {
        parent::setUp();

        $this->gitRepository = new GitRepository(GitRepositoryTestCase::TEST_PATH);
        $this->user = new TestUser('Tester', 'test@example.com');

        $this->example1Content = file_get_contents(__DIR__ . '/../Data/example1.md');
        $this->example1Path = new FilePath('example1.md');

        $this->gitRepository->putContent($this->example1Path, $this->example1Content);
        $this->gitRepository->addAndCommit($this->user, 'Adding example1', $this->example1Path);
    }

    public function testParseWithHtmlEnabled()
    {
        $markdownService = new RepositoryAwareMarkdownService($this->gitRepository, true);
        $parsedMarkdownDocument = $markdownService->parse($this->example1Path, $this->example1Content);

        $this->assertEquals('The Document Title', $parsedMarkdownDocument->getTitle());

        $toc = $parsedMarkdownDocument->getToc();
        $this->assertEquals(
            [
                [
                    'text'     => 'First Subheading',
                    'id'       => 'heading1',
                    'level'    => 2,
                    'children' => [
                        [
                            'text'     => 'Third level heading',
                            'id'       => 'heading2',
                            'level'    => 3,
                            'children' => []
                        ],
                        [
                            'text'     => 'Second third level heading',
                            'id'       => 'heading3',
                            'level'    => 3,
                            'children' => []
                        ]
                    ],
                ],
                [
                    'text'     => 'Second Subheading with a link',
                    'id'       => 'heading4',
                    'level'    => 2,
                    'children' => []
                ]
            ],
            $toc
        );
    }
}
