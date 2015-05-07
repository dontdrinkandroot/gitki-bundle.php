<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
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
        $this->assertWatcher();

        $filePath = FilePath::parse($path);
        $user = $this->getUser();

        $file = null;
        try {
            $file = $this->getWikiService()->getFile($filePath);
            $response = new Response();
            $lastModified = new \DateTime();
            $lastModified->setTimestamp($file->getMTime());
            $response->setEtag(md5($lastModified->getTimestamp() . $user));
            $response->setLastModified($lastModified);

            $content = $this->getWikiService()->getContent($filePath);

            $renderedView = $this->renderView(
                'DdrGitkiBundle:Text:view.html.twig',
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
        $this->assertCommitter();

        $filePath = FilePath::parse($path);
        $user = $this->getGitUser();

        try {
            $this->getWikiService()->createLock($user, $filePath);
        } catch (FileLockedException $e) {
            $renderedView = $this->renderView(
                'DdrGitkiBundle:File:locked.html.twig',
                ['path' => $filePath, 'lockedBy' => $e->getLockedBy()]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        $form = $this->createFormBuilder()
            ->add('content', 'textarea')
            ->add('commitMessage', 'text', ['label' => 'Commit Message', 'required' => true])
            ->add(
                'actions',
                'form_actions',
                [
                    'buttons' => [
                        'save'   => ['type' => 'submit', 'options' => ['label' => 'Save']],
                        'cancel' => [
                            'type'    => 'submit',
                            'options' => ['label' => 'Cancel', 'button_class' => 'default']
                        ],
                    ]
                ]
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
            'DdrGitkiBundle:Text:edit.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }
}
