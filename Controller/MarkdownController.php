<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Form\Type\MarkdownEditType;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use GitWrapper\GitException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class MarkdownController extends BaseController
{
    /**
     * @var WikiService
     */
    private $wikiService;

    /**
     * @var DirectoryServiceInterface
     */
    private $directoryService;

    /**
     * @var ExtensionRegistryInterface
     */
    private $extensionRegistry;

    /**
     * @var MarkdownServiceInterface
     */
    private $markdownService;

    /**
     * MarkdownController constructor.
     */
    public function __construct(
        WikiService $wikiService,
        DirectoryServiceInterface $directoryService,
        ExtensionRegistryInterface $extensionRegistry,
        SecurityService $securityService,
        MarkdownServiceInterface $markdownService
    ) {
        parent::__construct($securityService);
        $this->wikiService = $wikiService;
        $this->directoryService = $directoryService;
        $this->extensionRegistry = $extensionRegistry;
        $this->markdownService = $markdownService;
    }

    public function viewAction(Request $request, string $path)
    {
        $this->securityService->assertWatcher();

        $showDirectoryContents = $this->getParameter('ddr_gitki.show_directory_contents');
        $filePath = FilePath::parse($path);
        $directoryListing = null;

        try {
            $file = $this->wikiService->getFile($filePath);
            $response = new Response();
            if (!$showDirectoryContents) {
                $lastModified = new \DateTime();
                $lastModified->setTimestamp($file->getMTime());
                $response->setLastModified($lastModified);
                $response->setEtag($this->generateEtag($lastModified));
                if ($response->isNotModified($request)) {
                    return $response;
                }
            } else {
                $directoryPath = $filePath->getParentPath();
                $directoryListing = $this->directoryService->getDirectoryListing($directoryPath);
            }

            $content = $this->wikiService->getContent($filePath);
            $document = $this->markdownService->parse($content, $filePath);

            return $this->render(
                '@DdrGitki/Markdown/view.html.twig',
                [
                    'path'               => $filePath,
                    'document'           => $document,
                    'editableExtensions' => $this->extensionRegistry->getEditableExtensions(),
                    'directoryListing'   => $directoryListing
                ],
                $response
            );
        } catch (FileNotFoundException $e) {

            if (null === $this->getUser()) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath, 'action' => 'edit']
                )
            );
        }
    }

    public function previewAction(Request $request, $path)
    {
        $this->securityService->assertWatcher();

        $filePath = FilePath::parse($path);

        $markdown = $request->request->get('markdown');
        $document = $this->markdownService->parse($markdown, $filePath);

        return new Response($document->getHtml());
    }

    public function editAction(Request $request, $path)
    {
        $this->securityService->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->securityService->getGitUser();

        try {
            $this->wikiService->createLock($user, $filePath);
        } catch (FileLockedException $e) {
            $renderedView = $this->renderView(
                '@DdrGitki/File/locked.html.twig',
                ['path' => $filePath, 'lockedBy' => $e->getLockedBy()]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        $form = $this->createForm(MarkdownEditType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->wikiService->saveFile($user, $filePath, $content, $commitMessage);
                $this->wikiService->removeLock($user, $filePath);

                return $this->redirect($this->generateUrl('ddr_gitki_file', ['path' => $filePath]));
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            $content = null;
            if ($this->wikiService->exists($filePath)) {
                $content = $this->wikiService->getContent($filePath);
            } else {
                $title = $request->query->get('title');
                if (!empty($title)) {
                    $content = $title . "\n";
                    $titleLength = strlen($title);
                    for ($i = 0; $i < $titleLength; $i++) {
                        $content .= '=';
                    }
                    $content .= "\n\n";
                }
            }

            if (!$form->isSubmitted()) {
                $form->setData(
                    [
                        'content'       => $content,
                        'commitMessage' => 'Editing ' . $filePath->toAbsoluteString()
                    ]
                );
            }
        }

        return $this->render(
            '@DdrGitki/Markdown/edit.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }
}
