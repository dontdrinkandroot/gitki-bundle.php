<?php

namespace Dontdrinkandroot\GitkiBundle\Controller\Markdown;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Exception\FileLockedException;
use Dontdrinkandroot\GitkiBundle\Form\Type\MarkdownEditType;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symplify\GitWrapper\Exception\GitException;

class EditAction extends AbstractController
{
    public function __construct(
        private readonly SecurityService $securityService,
        private readonly WikiService $wikiService
    ) {
    }

    public function __invoke(Request $request, FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::WRITE_PATH, $path);

        $user = $this->securityService->getGitUser();

        try {
            $this->wikiService->createLock($user, $path);
        } catch (FileLockedException $e) {
            $renderedView = $this->renderView(
                '@DdrGitki/File/locked.html.twig',
                ['path' => $path, 'lockedBy' => $e->lockedBy]
            );

            return new Response($renderedView, Response::HTTP_LOCKED);
        }

        $form = $this->createForm(MarkdownEditType::class);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $content = $form->get('content')->getData();
            $commitMessage = $form->get('commitMessage')->getData();
            try {
                $this->wikiService->saveFile($user, $path, $content, $commitMessage);
                $this->wikiService->removeLock($user, $path);

                return $this->redirect($this->generateUrl('ddr_gitki_file', ['path' => $path]));
            } catch (GitException $e) {
                throw $e;
            }
        } else {
            $content = null;
            if ($this->wikiService->exists($path)) {
                $content = $this->wikiService->getContent($path);
            } else {
                $title = Asserted::stringOrNull($request->query->get('title'));
                if (!empty($title)) {
                    $content = $title . "\n";
                    $titleLength = strlen($title);
                    $content .= str_repeat('=', $titleLength);
                    $content .= "\n\n";
                }
            }

            if (!$form->isSubmitted()) {
                $form->setData(
                    [
                        'content' => $content,
                        'commitMessage' => 'Editing ' . $path->toAbsoluteString()
                    ]
                );
            }
        }

        return $this->render(
            '@DdrGitki/Markdown/edit.html.twig',
            ['form' => $form->createView(), 'path' => $path]
        );
    }
}
