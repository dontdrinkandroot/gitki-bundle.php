<?php

namespace Dontdrinkandroot\GitkiBundle\Controller\Markdown;

use DateTime;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ViewAction extends AbstractController
{
    public function __construct(
        private readonly WikiService $wikiService,
        private readonly DirectoryServiceInterface $directoryService,
        private readonly MarkdownServiceInterface $markdownService,
        private readonly ExtensionRegistryInterface $extensionRegistry
    ) {
    }

    public function __invoke(Request $request, FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH, $path);

        $showDirectoryContents = $this->getParameter('ddr_gitki.show_directory_contents');
        $directoryListing = null;

        try {
            $file = $this->wikiService->getFile($path);
            $response = new Response();
            if (!$showDirectoryContents) {
                $lastModified = new DateTime();
                $lastModified->setTimestamp($file->getMTime());
                $response->setLastModified($lastModified);
                if ($response->isNotModified($request)) {
                    return $response;
                }
            } else {
                $directoryPath = $path->getParent();
                $directoryListing = $this->directoryService->getDirectoryListing($directoryPath);
            }

            $content = $this->wikiService->getContent($path);
            $document = $this->markdownService->parse($content, $path);

            return $this->render(
                '@DdrGitki/Markdown/view.html.twig',
                [
                    'path' => $path,
                    'document' => $document,
                    'editableExtensions' => $this->extensionRegistry->getEditableExtensions(),
                    'directoryListing' => $directoryListing
                ],
                $response
            );
        } catch (FileNotFoundException) {
            if (null === $this->getUser()) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $path, 'action' => 'edit']
                )
            );
        }
    }
}
