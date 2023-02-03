<?php

namespace Dontdrinkandroot\GitkiBundle\Controller\Markdown;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewAction extends AbstractController
{
    public function __construct(private readonly MarkdownServiceInterface $markdownService)
    {
    }

    public function __invoke(Request $request, FilePath $path): Response
    {
        $this->isGranted(SecurityAttribute::READ_PATH);

        $markdown = Asserted::string($request->request->get('markdown'));
        $document = $this->markdownService->parse($markdown, $path);

        return new Response($document->html);
    }
}
