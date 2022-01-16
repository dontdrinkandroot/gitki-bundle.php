<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Common\CrudOperation;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class RawAction extends AbstractController
{
    public function __construct(private WikiService $wikiService)
    {
    }

    public function __invoke(Request $request, FilePath $path): Response
    {
        $this->denyAccessUnlessGranted(CrudOperation::READ, $path);

        return new BinaryFileResponse($this->wikiService->getFile($path));
    }
}
