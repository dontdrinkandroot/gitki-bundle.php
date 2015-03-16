<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory;

use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\AbstractContainerAwareHandler;
use Dontdrinkandroot\Gitki\BaseBundle\ActionHandler\Directory\DirectoryActionHandlerInterface;
use Dontdrinkandroot\Gitki\BaseBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Request;

class CreateFileActionHandler extends AbstractContainerAwareHandler implements DirectoryActionHandlerInterface
{

    /**
     * {@inheritdoc}
     */
    public function handle(DirectoryPath $directoryPath, Request $request, GitUserInterface $user)
    {
        $this->assertRole('ROLE_COMMITTER');
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
                        'ddr_gitki_wiki_file',
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
}
