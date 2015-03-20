<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;

class DirectoryController extends BaseController
{

    public function listAction($path)
    {
        $directoryPath = DirectoryPath::parse($path);

        $directoryListing = $this->getWikiService()->listDirectory($directoryPath);
        $editableExtensions = $this->getWikiService()->getEditableExtensions();

        return $this->render(
            'DdrGitkiBaseBundle:Wiki:directory.listing.html.twig',
            [
                'path'               => $directoryPath,
                'directoryListing'   => $directoryListing,
                'editableExtensions' => $editableExtensions
            ]
        );
    }

    public function indexAction($path)
    {
        $directoryPath = DirectoryPath::parse($path);

        $indexFilePath = $directoryPath->appendFile('index.md');
        if ($this->getWikiService()->exists($indexFilePath)) {
            return $this->redirectToRoute('ddr_gitki_file', ['path' => $indexFilePath->toAbsoluteString()]);
        }

        return $this->redirectToRoute('ddr_gitki_directory', ['path' => $directoryPath->toAbsoluteString()]);
    }

    public function createSubdirectoryAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITTER');

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
            'DdrGitkiBaseBundle:Wiki:directory.addFolder.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }

    public function createFileAction(Request $request, $path)
    {
        $this->assertRole('ROLE_COMMITTER');

        $directoryPath = DirectoryPath::parse($path);

        $extension = $request->query->get('extension', 'txt');

        $form = $this->createFormBuilder()
            ->add(
                'filename',
                'text',
                array(
                    'label'    => 'Filename',
                    'required' => true,
                    'attr'     => array(
                        'input_group' => array('append' => '.' . $extension)
                    )
                )
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
            'DdrGitkiBaseBundle:Wiki:directory.createFile.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath]
        );
    }

    public function deleteAction($path)
    {
        $this->assertRole('ROLE_COMMITTER');

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
        $this->assertRole('ROLE_COMMITTER');

        $directoryPath = DirectoryPath::parse($path);
        $user = $this->getUser();

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
                if (null == $uploadedFileName || trim($uploadedFileName) == '') {
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
            'DdrGitkiBaseBundle:Wiki:directory.uploadFile.html.twig',
            ['form' => $form->createView(), 'path' => $directoryPath]
        );
    }
}
