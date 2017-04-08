<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\Path\FilePath;
use GitWrapper\GitException;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\SubmitButton;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class TextController extends BaseController
{
    public function viewAction(Request $request, $path)
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
            $response->setEtag($this->generateEtag($lastModified));
            $response->setLastModified($lastModified);
            if ($response->isNotModified($request)) {
                return $response;
            }

            $content = $this->getWikiService()->getContent($filePath);

            $renderedView = $this->renderView(
                'DdrGitkiBundle:Text:view.html.twig',
                [
                    'path'               => $filePath,
                    'content'            => $content,
                    'editableExtensions' => $this->getExtensionRegistry()->getEditableExtensions()
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
            ->add('content', TextareaType::class, ['attr' => ['rows' => 15]])
            ->add('commitMessage', TextType::class, ['label' => 'Commit Message', 'required' => true])
            ->add('submit', SubmitType::class, ['label' => 'Save'])
            ->add('cancel', SubmitType::class, ['label' => 'Cancel'])
            ->getForm();

        $form->handleRequest($request);

        /** @var SubmitButton $cancelButton */
        $cancelButton = $form->get('cancel');
        if ($cancelButton->isClicked()) {
            $this->getWikiService()->removeLock($user, $filePath);

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath]
                )
            );
        }

        if ($form->isSubmitted() && $form->isValid()) {
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
