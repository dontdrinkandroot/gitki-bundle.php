<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Service;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitService;
use Dontdrinkandroot\GitkiBundle\Service\Git\GitServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\FileSystemAwareMarkdownService;
use Dontdrinkandroot\GitkiBundle\Tests\GitRepositoryTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security\User;
use Dontdrinkandroot\GitkiBundle\Tests\TestUser;
use Dontdrinkandroot\Path\FilePath;

class FileSystemAwareMarkdownServiceTest extends GitRepositoryTestCase
{
    /**
     * @var GitServiceInterface
     */
    protected $gitService;

    /**
     * @var GitUserInterface
     */
    protected $user;

    /**
     * @var FileSystemService
     */
    protected $fileSystemService;

    /**
     * @var FilePath
     */
    private FilePath $tocTestPath;

    private $tocTestContent;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->fileSystemService = new FileSystemService(GitRepositoryTestCase::GIT_REPOSITORY_PATH);
        $this->gitService = new GitService($this->fileSystemService);
        $this->user = new User('tester', 'Tester', 'test@example.com', []);

        $this->tocTestContent = file_get_contents(__DIR__ . '/../../Data/repo/examples/toc-example.md');
        $this->tocTestPath = new FilePath('toc.md');

        $this->gitService->putAndCommitFile($this->user, $this->tocTestPath, $this->tocTestContent, 'Adding tocTest');
    }

    public function testToc(): void
    {
        $markdownService = new FileSystemAwareMarkdownService($this->fileSystemService, true);
        $parsedMarkdownDocument = $markdownService->parse($this->tocTestContent, $this->tocTestPath);

        $this->assertEquals('TOC Example', $parsedMarkdownDocument->title);

        $toc = $parsedMarkdownDocument->toc;
        $this->assertEquals(
            [
                [
                    'text' => 'First Subheading',
                    'id' => 'heading1',
                    'level' => 2,
                    'children' => [
                        [
                            'text' => 'Third level heading',
                            'id' => 'heading2',
                            'level'    => 3,
                            'children' => []
                        ],
                        [
                            'text'     => 'Second third level heading which is very long so it will wrap around to multiple lines',
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
                ],
                [
                    'text'     => 'Third Subheading which is very long so it will wrap around to muliple lines',
                    'id'       => 'heading5',
                    'level'    => 2,
                    'children' => []
                ]
            ],
            $toc
        );
    }

    public function testLinks(): void
    {
        $linkTestPath = new FilePath('links.md');
        $markdownService = new FileSystemAwareMarkdownService($this->fileSystemService, true);

        $parsedMarkdownDocument = $markdownService->parse('[Existing Link](./toc.md)', $linkTestPath);
        $this->assertSame('<p><a href="./toc.md">Existing Link</a></p>' . "\n", $parsedMarkdownDocument->html);
        $linkedPaths = $parsedMarkdownDocument->getLinkedPaths();
        $this->assertCount(1, $linkedPaths);
        $this->assertEquals(new FilePath('toc.md'), $linkedPaths[0]);

        $parsedMarkdownDocument = $markdownService->parse('[Missing Link](./missing.md)', $linkTestPath);
        $this->assertSame(
            '<p><a href="./missing.md" class="missing">Missing Link</a></p>' . "\n",
            $parsedMarkdownDocument->html
        );

        $parsedMarkdownDocument = $markdownService->parse('[External Link](http://example.com)', $linkTestPath);
        $this->assertSame(
            '<p><a href="http://example.com" rel="external">External Link</a></p>' . "\n",
            $parsedMarkdownDocument->html
        );
    }
}
