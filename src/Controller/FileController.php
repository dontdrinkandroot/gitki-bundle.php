<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\RootDirectoryPath;
use RuntimeException;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ConflictHttpException;

class FileController extends BaseController
{
    public function __construct(
        SecurityService $securityService,
        private readonly WikiService $wikiService,
        private readonly DirectoryServiceInterface $directoryService
    ) {
        parent::__construct($securityService);
    }

    public function removeAction(FilePath $path): RedirectResponse
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $user = $this->securityService->getGitUser();

        $commitMessage = 'Removing ' . $path->toAbsoluteString();
        $this->wikiService->removeFile($user, $path, $commitMessage);

        return $this->redirectToRoute(
            'ddr_gitki_directory',
            ['path' => $path->getParent()->toAbsoluteString()]
        );
    }

    public function holdLockAction(FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $user = $this->securityService->getGitUser();

        try {
            $expiry = $this->wikiService->holdLock($user, $path);
        } catch (FileLockedException $e) {
            $renderedView = $this->renderView(
                '@DdrGitki/File/locked.html.twig',
                ['path' => $path, 'lockedBy' => $e->lockedBy]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        return new Response((string)$expiry);
    }

    public function historyAction(FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_HISTORY, $path);

        $history = $this->wikiService->getFileHistory($path);

        return $this->render(
            '@DdrGitki/File/history.html.twig',
            [
                'path' => $path,
                'history' => $history
            ]
        );
    }

    public function moveAction(Request $request, FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $user = $this->securityService->getGitUser();

        try {
            $this->wikiService->createLock($user, $path);
        } catch (FileLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }

        $directories = $this->directoryService->listDirectories(new RootDirectoryPath(), true, true);
        $directoryChoices = [];
        foreach ($directories as $directory) {
            $directoryString = $directory->getAbsolutePath()->toAbsoluteUrlString();
            $directoryChoices[$directoryString] = $directoryString;
        }

        $form = $this->createFormBuilder()
            ->add(
                'directory',
                ChoiceType::class,
                [
                    'choices' => $directoryChoices,
                    'required' => true,
                    'data' => $path->getParent()->toAbsoluteString()
                ]
            )
            ->add('name', TextType::class, ['required' => true, 'data' => $path->getName()])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDirectory = DirectoryPath::parse($form->get('directory')->getData());
            $newName = $form->get('name')->getData();
            $newPath = $newDirectory->appendFile($newName);

            $this->wikiService->renameFile(
                $user,
                $path,
                $newPath,
                sprintf('Moving %s to %s', $path->toAbsoluteString(), $newPath->toAbsoluteString())
            );

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_directory',
                    ['path' => $newPath->getParent()->toAbsoluteString()]
                )
            );
        }

        return $this->render(
            '@DdrGitki/File/move.html.twig',
            ['form' => $form, 'path' => $path]
        );
    }

    /**
     * Cancels editing.
     */
    public function cancelAction(FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);
        $user = $this->securityService->getGitUser();
        $this->wikiService->removeLock($user, $path);

        return $this->redirect($this->generateUrl('ddr_gitki_file', ['path' => $path]));
    }

    /**
     * @throws RuntimeException
     */
    protected function getContents(File $file): string
    {
        $level = error_reporting(0);
        $content = file_get_contents($file->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            error_reporting($level);
            throw new RuntimeException($error['message'] ?? 'Could not get contents');
        }

        error_reporting($level);
        return $content;
    }
}
