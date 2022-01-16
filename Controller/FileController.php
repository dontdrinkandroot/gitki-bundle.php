<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use DateTime;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
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
        private WikiService $wikiService,
        private DirectoryServiceInterface $directoryService
    ) {
        parent::__construct($securityService);
    }

    public function removeAction($path): RedirectResponse
    {
        $this->securityService->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->securityService->getGitUser();

        $commitMessage = 'Removing ' . $filePath->toAbsoluteString();
        $this->wikiService->removeFile($user, $filePath, $commitMessage);

        return $this->redirectToRoute(
            'ddr_gitki_directory',
            ['path' => $filePath->getParentPath()->toAbsoluteString()]
        );
    }

    public function holdLockAction($path): Response
    {
        $this->securityService->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->securityService->getGitUser();

        try {
            $expiry = $this->wikiService->holdLock($user, $filePath);
        } catch (FileLockedException $e) {
            $renderedView = $this->renderView(
                '@DdrGitki/File/locked.html.twig',
                ['path' => $filePath, 'lockedBy' => $e->getLockedBy()]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        return new Response((string)$expiry);
    }

    public function historyAction($path): Response
    {
        $this->securityService->assertWatcher();

        $filePath = FilePath::parse($path);

        $history = $this->wikiService->getFileHistory($filePath);

        return $this->render(
            '@DdrGitki/File/history.html.twig',
            [
                'path'    => $filePath,
                'history' => $history
            ]
        );
    }

    public function moveAction(Request $request, $path): Response
    {
        $this->securityService->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->securityService->getGitUser();

        try {
            $this->wikiService->createLock($user, $filePath);
        } catch (FileLockedException $e) {
            throw new ConflictHttpException($e->getMessage());
        }

        $directories = $this->directoryService->listDirectories(new DirectoryPath(), true, true);
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
                    'choices'  => $directoryChoices,
                    'required' => true,
                    'data'     => $filePath->getParentPath()->toAbsoluteString()
                ]
            )
            ->add('name', TextType::class, ['required' => true, 'data' => $filePath->getName()])
            ->add('submit', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDirectory = DirectoryPath::parse($form->get('directory')->getData());
            $newName = $form->get('name')->getData();
            $newPath = $newDirectory->appendFile($newName);

            $this->wikiService->renameFile(
                $user,
                $filePath,
                $newPath,
                sprintf('Moving %s to %s', $filePath->toAbsoluteString(), $newPath->toAbsoluteString())
            );

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_directory',
                    ['path' => $newPath->getParentPath()->toAbsoluteString()]
                )
            );
        }

        return $this->render(
            '@DdrGitki/File/move.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }

    /**
     * Cancels editing.
     *
     * @param string $path
     *
     * @return Response
     */
    public function cancelAction($path)
    {
        $this->securityService->assertCommitter();
        $filePath = FilePath::parse($path);
        $user = $this->securityService->getGitUser();
        $this->wikiService->removeLock($user, $filePath);

        return $this->redirect($this->generateUrl('ddr_gitki_file', ['path' => $filePath]));
    }

    /**
     * @param File $file
     *
     * @return string
     * @throws RuntimeException
     */
    protected function getContents(File $file): string
    {
        $level = error_reporting(0);
        $content = file_get_contents($file->getPathname());
        error_reporting($level);
        if (false === $content) {
            $error = error_get_last();
            throw new RuntimeException($error['message'] ?? 'Could not get contents');
        }

        return $content;
    }
}
