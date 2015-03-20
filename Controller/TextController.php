<?php

namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Dontdrinkandroot\Gitki\BaseBundle\Exception\PageLockedException;
use Dontdrinkandroot\Path\FilePath;
use GitWrapper\GitException;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class TextController extends BaseController
{

    public function viewAction($path)
    {
        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        $file = null;
        try {
            $file = $this->getWikiService()->getFile($filePath);
            $response = new Response();
            $lastModified = new \DateTime();
            $lastModified->setTimestamp($file->getMTime());
            $response->setLastModified($lastModified);

            $content = $this->getWikiService()->getContent($filePath);

            $renderedView = $this->renderView(
                'DdrGitkiBaseBundle:Text:view.html.twig',
                [
                    'path'    => $filePath,
                    'content' => $content
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
        $this->assertRole('ROLE_COMMITTER');

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        try {
            $this->getWikiService()->createLock($user, $filePath);
        } catch (PageLockedException $e) {
            $renderedView = $this->renderView(
                'DdrGitkiBaseBundle:Wiki:locked.html.twig',
                ['path' => $filePath, 'lockedBy' => $e->getLockedBy()]
            );

            return new Response($renderedView, 409);
        }

        $form = $this->createFormBuilder()
            ->add('content', 'textarea')
            ->add('commitMessage', 'text', array('label' => 'Commit Message', 'required' => true))
            ->add(
                'actions',
                'form_actions',
                array(
                    'buttons' => array(
                        'save'   => array('type' => 'submit', 'options' => array('label' => 'Save')),
                        'cancel' => array(
                            'type'    => 'submit',
                            'options' => array('label' => 'Cancel', 'button_class' => 'default')
                        ),
                    )
                )
            )
            ->getForm();

        $form->handleRequest($request);

        /** @var SubmitButton $cancelButton */
        $cancelButton = $form->get('actions')->get('cancel');
        if ($cancelButton->isClicked()) {
            $this->getWikiService()->removeLock($user, $filePath);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath]
                )
            );
        }

        if ($form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->getWikiService()->saveFile($user, $filePath, $content, $commitMessage);
                $this->getWikiService()->removeLock($user, $filePath);

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
            if ($this->getWikiService()->exists($filePath)) {
                $content = $this->getWikiService()->getContent($filePath);
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
            'DdrGitkiBaseBundle:Text:edit.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }
}
