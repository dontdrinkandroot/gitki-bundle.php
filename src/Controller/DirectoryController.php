<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Form\Type\SubdirectoryCreateType;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\FileSystem\FileSystemService;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class DirectoryController extends BaseController
{
    public function __construct(
        SecurityService $securityService,
        private readonly WikiService $wikiService,
        private readonly DirectoryServiceInterface $directoryService,
        private readonly ExtensionRegistryInterface $extensionRegistry,
        private readonly FileSystemService $fileSystemService
    ) {
        parent::__construct($securityService);
    }

    public function listAction(DirectoryPath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH, $path);

        if (!$this->fileSystemService->exists($path)) {
            throw new NotFoundHttpException();
        }

        $directoryListing = $this->directoryService->getDirectoryListing($path);

        return $this->render(
            '@DdrGitki/Directory/list.html.twig',
            [
                'path' => $path,
                'directoryListing' => $directoryListing,
                'editableExtensions' => $this->extensionRegistry->getEditableExtensions()
            ]
        );
    }

    public function indexAction(DirectoryPath $path): RedirectResponse
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH, $path);

        $indexFilePath = $this->directoryService->resolveExistingIndexFile($path);
        if (null !== $indexFilePath) {
            return $this->redirectToRoute('ddr_gitki_file', ['path' => $indexFilePath->toAbsoluteString()]);
        }

        if (!$this->fileSystemService->exists($path)) {
            if (!$this->isGranted(SecurityAttribute::WRITE_PATH, $path)) {
                throw new NotFoundHttpException();
            }

            $indexFilePath = $this->directoryService->getPrimaryIndexFile($path);
            if (null === $indexFilePath) {
                throw new NotFoundHttpException();
            }

            return $this->redirectToRoute(
                'ddr_gitki_file',
                ['path' => $indexFilePath->toAbsoluteString()]
            );
        }

        return $this->redirectToRoute(
            'ddr_gitki_directory',
            ['path' => $path->toAbsoluteString(), 'action' => 'list']
        );
    }

    public function createSubdirectoryAction(Request $request, DirectoryPath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $form = $this->createForm(SubdirectoryCreateType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dirname = (string)$form->get('dirname')->getData();
            $subDirPath = $path->appendDirectory($dirname);

            $this->wikiService->createFolder($subDirPath);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_directory',
                    ['path' => $subDirPath->toAbsoluteString()]
                )
            );
        }

        return $this->render(
            '@DdrGitki/Directory/create.subdirectory.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }

    public function createFileAction(Request $request, DirectoryPath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $extension = $request->query->get('extension', 'txt');

        $form = $this->createFormBuilder()
            ->add(
                'filename',
                TextType::class,
                [
                    'label' => 'Filename',
                    'required' => true,
//                    'attr'     => [
//                        'input_group' => ['append' => '.' . $extension]
//                    ]
                ]
            )
            ->add('create', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $form->get('filename')->getData() . '.' . $extension;
            $filePath = $path->appendFile($filename);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath->toAbsoluteString(), 'action' => 'edit']
                )
            );
        }

        return $this->render(
            '@DdrGitki/Directory/create.file.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }

    public function removeAction(Request $request, DirectoryPath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $files = $this->wikiService->findAllFiles($path);
        $parentDirPath = $path->getParentPath()->toAbsoluteString();

        if (0 === count($files)) {
            $this->wikiService->removeDirectory($path);

            return $this->redirect($this->generateUrl('ddr_gitki_directory', ['path' => $parentDirPath]));
        }

        $form = $this->createFormBuilder()
            ->add('commitMessage', TextType::class, ['label' => 'Commit Message', 'required' => true])
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commitMessage = $form->get('commitMessage')->getData();
            $this->wikiService->removeDirectoryRecursively(
                $this->securityService->getGitUser(),
                $path,
                $commitMessage
            );

            return $this->redirect($this->generateUrl('ddr_gitki_directory', ['path' => $parentDirPath]));
        }

        if (!$form->isSubmitted()) {
            $form->setData(['commitMessage' => 'Removing ' . $path->toAbsoluteString()]);
        }

        return $this->render(
            '@DdrGitki/Directory/remove.html.twig',
            ['form' => $form->createView(), 'path' => $path, 'files' => $files]
        );
    }

    public function uploadFileAction(Request $request, DirectoryPath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $user = $this->securityService->getGitUser();

        $form = $this->createFormBuilder()
            ->add('uploadedFile', FileType::class, ['label' => 'File'])
            ->add('uploadedFileName', TextType::class, ['label' => 'Filename (if other)', 'required' => false])
            ->add('Upload', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = Asserted::instanceOf($form->get('uploadedFile')->getData(), UploadedFile::class);
            $uploadedFileName = Asserted::stringOrNull($form->get('uploadedFileName')->getData());
            if (null === $uploadedFileName || trim($uploadedFileName) === '') {
                $uploadedFileName = $uploadedFile->getClientOriginalName();
            }
            $filePath = $path->appendFile($uploadedFileName);
            $this->wikiService->addFile(
                $user,
                $filePath,
                $uploadedFile,
                'Adding ' . $filePath
            );

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_directory',
                    ['path' => $path->toAbsoluteString()]
                )
            );
        }

        return $this->render(
            '@DdrGitki/Directory/upload.file.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }
}
