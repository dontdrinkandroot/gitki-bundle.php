<?php

namespace Dontdrinkandroot\GitkiBundle\Controller\Markdown;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\GitkiBundle\Service\Markdown\MarkdownServiceInterface;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class PreviewAction extends AbstractController
{
    public function __construct(private MarkdownServiceInterface $markdownService)
    {
    }

    public function __invoke(Request $request, FilePath $path): Response
    {
        $this->isGranted(CrudOperation::READ);

        $markdown = Asserted::string($request->request->get('markdown'));
        $document = $this->markdownService->parse($markdown, $path);

        return new Response($document->getHtml());
    }
}
