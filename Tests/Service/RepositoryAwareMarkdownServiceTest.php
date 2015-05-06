<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Service;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitService;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\RepositoryAwareMarkdownService;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestUser;
use Dontdrinkandroot\Path\FilePath;

class RepositoryAwareMarkdownServiceTest extends GitRepositoryTestCase
{

    /**
     * @var GitServiceInterface
     */
    protected $gitRepository;

    /**
     * @var GitUserInterface
     */
    protected $user;

    /**
     * @var FilePath
     */
    private $tocTestPath;

    private $tocTestContent;

    public function setUp()
    {
        parent::setUp();

        $this->gitRepository = new GitService(GitRepositoryTestCase::TEST_PATH);
        $this->user = new TestUser('Tester', 'test@example.com');

        $this->tocTestContent = file_get_contents(__DIR__ . '/../Data/toc.md');
        $this->tocTestPath = new FilePath('toc.md');

        $this->gitRepository->putContent($this->tocTestPath, $this->tocTestContent);
        $this->gitRepository->addAndCommit($this->user, 'Adding tocTest', $this->tocTestPath);
    }

    public function testToc()
    {
        $markdownService = new RepositoryAwareMarkdownService($this->gitRepository, true);
        $parsedMarkdownDocument = $markdownService->parse($this->tocTestContent, $this->tocTestPath);

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

    public function testLinks()
    {
        $linkTestPath = new FilePath('links.md');
        $markdownService = new RepositoryAwareMarkdownService($this->gitRepository, true);

        $parsedMarkdownDocument = $markdownService->parse('[Existing Link](./toc.md)', $linkTestPath);
        $this->assertSame('<p><a href="./toc.md">Existing Link</a></p>' . "\n", $parsedMarkdownDocument->getHtml());
        $linkedPaths = $parsedMarkdownDocument->getLinkedPaths();
        $this->assertCount(1, $linkedPaths);
        $this->assertEquals(new FilePath('toc.md'), $linkedPaths[0]);

        $parsedMarkdownDocument = $markdownService->parse('[Missing Link](./missing.md)', $linkTestPath);
        $this->assertSame(
            '<p><a href="./missing.md" class="missing">Missing Link</a></p>' . "\n",
            $parsedMarkdownDocument->getHtml()
        );

        $parsedMarkdownDocument = $markdownService->parse('[External Link](http://example.com)', $linkTestPath);
        $this->assertSame(
            '<p><a href="http://example.com" rel="external">External Link</a></p>' . "\n",
            $parsedMarkdownDocument->getHtml()
        );
    }
}
