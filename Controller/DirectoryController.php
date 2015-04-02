<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends BaseController
{

    public function listAction($path)
    {
        $this->assertWatcher();

        $directoryPath = DirectoryPath::parse($path);

        $directoryListing = $this->getWikiService()->listDirectory($directoryPath);

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

        $indexFilePath = $this->getDirectoryService()->resolveIndexFile($directoryPath);
        if (null !== $indexFilePath) {
            return $this->redirectToRoute('ddr_gitki_file', ['path' => $indexFilePath->toAbsoluteString()]);
        }

        return $this->redirectToRoute('ddr_gitki_directory', ['path' => $directoryPath->toAbsoluteString()]);
    }

    public function createSubdirectoryAction(Request $request, $path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);

        $path = DirectoryPath::parse($directoryPath);

        $form = $this->createFormBuilder()
            ->add(
                'dirname',
                'text',
                [
                    'label'    => 'Foldername',
                    'required' => true,
                ]
            )
            ->add('create', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
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
                'text',
                [
                    'label'    => 'Filename',
                    'required' => true,
                    'attr'     => [
                        'input_group' => ['append' => '.' . $extension]
                    ]
                ]
            )
            ->add('create', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $filename = $form->get('filename')->getData() . '.' . $extension;
                $filePath = $directoryPath->appendFile($filename);

                return $this->redirect(
                    $this->generateUrl(
                        'ddr_gitki_file',
                        ['path' => $filePath->toAbsoluteString(), 'action' => 'edit']
                    )
                );
            }
        }

        return $this->render(
            'DdrGitkiBundle:Directory:create.file.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath]
        );
    }

    public function deleteAction($path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);

        $this->getWikiService()->deleteDirectory($directoryPath);

        $parentDirPath = $directoryPath->getParentPath()->toAbsoluteString();

        return $this->redirect(
            $this->generateUrl(
                'ddr_gitki_directory',
                ['path' => $parentDirPath]
            )
        );
    }

    public function uploadFileAction(Request $request, $path)
    {
        $this->assertCommitter();

        $directoryPath = DirectoryPath::parse($path);
        $user = $this->getGitUser();

        $form = $this->createFormBuilder()
            ->add('uploadedFile', 'file', array('label' => 'File'))
            ->add('uploadedFileName', 'text', array('label' => 'Filename (if other)', 'required' => false))
            ->add('Upload', 'submit')
            ->getForm();

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
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
        }

        return $this->render(
            'DdrGitkiBundle:Directory:upload.file.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath]
        );
    }
}
