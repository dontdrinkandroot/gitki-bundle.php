<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use DateTime;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Form\Type\TextEditType;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symplify\GitWrapper\Exception\GitException;

class TextController extends BaseController
{

    public function __construct(
        SecurityService $securityService,
        private WikiService $wikiService,
        private ExtensionRegistryInterface $extensionRegistry
    ) {
        parent::__construct($securityService);
    }

    public function viewAction(Request $request, FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH, $path);

        $user = $this->getUser();

        $file = null;
        try {
            $file = $this->wikiService->getFile($path);
            $response = new Response();
            $lastModified = new DateTime();
            $lastModified->setTimestamp($file->getMTime());
            $response->setEtag($this->generateEtag($lastModified));
            $response->setLastModified($lastModified);
            if ($response->isNotModified($request)) {
                return $response;
            }

            $content = $this->wikiService->getContent($path);

            $renderedView = $this->renderView(
                '@DdrGitki/Text/view.html.twig',
                [
                    'path' => $path,
                    'content' => $content,
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
                    ['path' => $path, 'action' => 'edit']
                )
            );
        }
    }

    public function editAction(Request $request, FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $user = $this->securityService->getGitUser();

        try {
            $this->wikiService->createLock($user, $path);
        } catch (FileLockedException $e) {
            $renderedView = $this->renderView(
                'DdrGitkiBundle:File:locked.html.twig',
                ['path' => $path, 'lockedBy' => $e->getLockedBy()]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        $form = $this->createForm(TextEditType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->wikiService->saveFile($user, $path, $content, $commitMessage);
                $this->wikiService->removeLock($user, $path);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_file',
                        ['path' => $path]
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            $content = '';
            if ($this->wikiService->exists($path)) {
                $content = $this->wikiService->getContent($path);
            }

            if (!$form->isSubmitted()) {
                $form->setData(
                    [
                        'content' => $content,
                        'commitMessage' => 'Editing ' . $path->toAbsoluteString()
                    ]
                );
            }
        }

        return $this->render(
            '@DdrGitki/Text/edit.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }
}
