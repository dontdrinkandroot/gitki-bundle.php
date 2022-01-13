<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use DateTime;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Form\Type\TextEditType;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symplify\GitWrapper\Exception\GitException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class TextController extends BaseController
{
    /**
     * @var WikiService
     */
    private $wikiService;

    /**
     * @var ExtensionRegistryInterface
     */
    private $extensionRegistry;

    public function __construct(
        SecurityService $securityService,
        WikiService $wikiService,
        ExtensionRegistryInterface $extensionRegistry
    ) {
        parent::__construct($securityService);
        $this->wikiService = $wikiService;
        $this->extensionRegistry = $extensionRegistry;
    }

    public function viewAction(Request $request, $path)
    {
        $this->securityService->assertWatcher();

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        $file = null;
        try {
            $file = $this->wikiService->getFile($filePath);
            $response = new Response();
            $lastModified = new DateTime();
            $lastModified->setTimestamp($file->getMTime());
            $response->setEtag($this->generateEtag($lastModified));
            $response->setLastModified($lastModified);
            if ($response->isNotModified($request)) {
                return $response;
            }

            $content = $this->wikiService->getContent($filePath);

            $renderedView = $this->renderView(
                '@DdrGitki/Text/view.html.twig',
                [
                    'path'               => $filePath,
                    'content'            => $content,
                    'editableExtensions' => $this->extensionRegistry->getEditableExtensions()
                ]
            );

            $response->setContent($renderedView);

            return $response;
        } catch (FileNotFoundException $e) {
            if (null === $user) {
                throw new NotFoundHttpException('This file does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath, 'action' => 'edit']
                )
            );
        }
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
                'DdrGitkiBundle:File:locked.html.twig',
                ['path' => $filePath, 'lockedBy' => $e->getLockedBy()]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        $form = $this->createForm(TextEditType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->wikiService->saveFile($user, $filePath, $content, $commitMessage);
                $this->wikiService->removeLock($user, $filePath);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_file',
                        ['path' => $filePath]
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            $content = '';
            if ($this->wikiService->exists($filePath)) {
                $content = $this->wikiService->getContent($filePath);
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
            '@DdrGitki/Text/edit.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }
}
