<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Form\Type\SubdirectoryCreateType;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class DirectoryController extends BaseController
{
    public function listAction($path)
    {
        $this->assertWatcher();

        $directoryPath = DirectoryPath::parse($path);
        if (!$this->getFileSystemService()->exists($directoryPath)) {
            throw new NotFoundHttpException();
        }

        $directoryListing = $this->getDirectoryService()->getDirectoryListing($directoryPath);

        return $this->render(
            'DdrGitkiBundle:Directory:list.html.twig',
            [
                'path'               => $directoryPath,
                'directoryListing'   => $directoryListing,
                'editableExtensions' => $this->getExtensionRegistry()->getEditableExtensions()
            ]
        );
    }

    public function indexAction($path)
    {
        $this->assertWatcher();

        $directoryPath = DirectoryPath::parse($path);

        $indexFilePath = $this->getDirectoryService()->resolveExistingIndexFile($directoryPath);
        if (null !== $indexFilePath) {
            return $this->redirectToRoute('ddr_gitki_file', ['path' => $indexFilePath->toAbsoluteString()]);
        }

        if (!$this->getFileSystemService()->exists($directoryPath)) {
            if (!$this->isCommitter()) {
                throw new NotFoundHttpException();
            }

            $indexFilePath = $this->getDirectoryService()->getPrimaryIndexFile($directoryPath);
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
            ['path' => $directoryPath->toAbsoluteString(), 'action' => 'list']
        );
    }

    public function createSubdirectoryAction(Request $request, $path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);

        $path = DirectoryPath::parse($directoryPath);

        $form = $this->createForm(SubdirectoryCreateType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dirname = $form->get('dirname')->getData();
            $subDirPath = $path->appendDirectory($dirname);

            $this->getWikiService()->createFolder($subDirPath);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_directory',
                    ['path' => $subDirPath->toAbsoluteString()]
                )
            );
        }

        return $this->render(
            'DdrGitkiBundle:Directory:create.subdirectory.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }

    public function createFileAction(Request $request, $path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);

        $extension = $request->query->get('extension', 'txt');

        $form = $this->createFormBuilder()
            ->add(
                'filename',
                TextType::class,
                [
                    'label'    => 'Filename',
                    'required' => true,
                    'attr'     => [
                        'input_group' => ['append' => '.' . $extension]
                    ]
                ]
            )
            ->add('create', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $filename = $form->get('filename')->getData() . '.' . $extension;
            $filePath = $directoryPath->appendFile($filename);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath->toAbsoluteString(), 'action' => 'edit']
                )
            );
        }

        return $this->render(
            'DdrGitkiBundle:Directory:create.file.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath]
        );
    }

    public function removeAction(Request $request, $path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);

        $wikiService = $this->getWikiService();
        $files = $wikiService->findAllFiles($directoryPath);
        $parentDirPath = $directoryPath->getParentPath()->toAbsoluteString();

        if (0 === count($files)) {
            $wikiService->removeDirectory($directoryPath);

            return $this->redirect($this->generateUrl('ddr_gitki_directory', ['path' => $parentDirPath]));
        }

        $form = $this->createFormBuilder()
            ->add('commitMessage', TextType::class, ['label' => 'Commit Message', 'required' => true])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isValid()) {
            $commitMessage = $form->get('commitMessage')->getData();
            $wikiService->removeDirectoryRecursively($this->getGitUser(), $directoryPath, $commitMessage);

            return $this->redirect($this->generateUrl('ddr_gitki_directory', ['path' => $parentDirPath]));
        }

        if (!$form->isSubmitted()) {
            $form->setData(['commitMessage' => 'Removing ' . $directoryPath->toAbsoluteString()]);
        }

        return $this->render(
            'DdrGitkiBundle:Directory:remove.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath, 'files' => $files]
        );
    }

    public function uploadFileAction(Request $request, $path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);
        $user = $this->getGitUser();

        $form = $this->createFormBuilder()
            ->add('uploadedFile', FileType::class, array('label' => 'File'))
            ->add('uploadedFileName', TextType::class, array('label' => 'Filename (if other)', 'required' => false))
            ->add('Upload', SubmitType::class)
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /* @var UploadedFile $uploadedFile */
            $uploadedFile = $form->get('uploadedFile')->getData();
            $uploadedFileName = $form->get('uploadedFileName')->getData();
            if (null === $uploadedFileName || trim($uploadedFileName) === '') {
                $uploadedFileName = $uploadedFile->getClientOriginalName();
            }
            $filePath = $directoryPath->appendFile($uploadedFileName);
            $this->getWikiService()->addFile(
                $user,
                $filePath,
                $uploadedFile,
                'Adding ' . $filePath
            );

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_directory',
                    ['path' => $directoryPath->toAbsoluteString()]
                )
            );
        }

        return $this->render(
            'DdrGitkiBundle:Directory:upload.file.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath]
        );
    }
}
