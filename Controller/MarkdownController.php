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

class MarkdownController extends BaseController
{

    public function viewAction(Request $request, $path)
    {
        $this->assertWatcher();

        $filePath = FilePath::parse($path);

        $file = null;
        try {
            $file = $this->getWikiService()->getFile($filePath);
            $response = new Response();
            $lastModified = new \DateTime();
            $lastModified->setTimestamp($file->getMTime());
            $response->setLastModified($lastModified);
            if ($response->isNotModified($request)) {
                return $response;
            }

            $content = $this->getWikiService()->getContent($filePath);
            $document = $this->getMarkdownService()->parse($content, $filePath);

            $renderedView = $this->renderView(
                'DdrGitkiBundle:Markdown:view.html.twig',
                [
                    'path'     => $filePath,
                    'document' => $document
                ]
            );

            $response->setContent($renderedView);

            return $response;
        } catch (FileNotFoundException $e) {

            if (null === $this->getUser()) {
                throw new NotFoundHttpException('This page does not exist');
            }

            return $this->redirect(
                $this->generateUrl(
                    'ddr_gitki_file',
                    ['path' => $filePath, 'action' => 'edit']
                )
            );
        }
    }

    public function previewAction(Request $request, $path)
    {
        $this->assertWatcher();

        $filePath = FilePath::parse($path);

        $markdown = $request->request->get('markdown');
        $document = $this->getMarkdownService()->parse($markdown, $filePath);

        return new Response($document->getHtml());
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
                        'save'   => [
                            'type'    => 'submit',
                            'options' => ['label' => 'Save']
                        ],
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
                    array('path' => $filePath)
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
                        array('path' => $filePath)
                    )
                );
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            $content = null;
            if ($this->getWikiService()->exists($filePath)) {
                $content = $this->getWikiService()->getContent($filePath);
            } else {
                $title = $request->query->get('title');
                if (!empty($title)) {
                    $content = $title . "\n";
                    $titleLength = strlen($title);
                    for ($i = 0; $i < $titleLength; $i++) {
                        $content .= '=';
                    }
                    $content .= "\n\n";
                }
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
            'DdrGitkiBundle:Markdown:edit.html.twig',
            ['form' => $form->createView(), 'path' => $filePath]
        );
    }

    /**
     * @return \Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface
     */
    protected function getMarkdownService()
    {
        return $this->container->get('ddr.gitki.service.markdown');
    }
}
