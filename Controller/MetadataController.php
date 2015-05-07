<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Path\DirectoryPath;
use Symfony\Component\HttpFoundation\Response;

class MetadataController extends BaseController
{

    public function jsonDirectoriesAction()
    {
        $this->assertWatcher();
        $directoryService = $this->getDirectoryService();
        $directories = $directoryService->listDirectories(new DirectoryPath(), true, true);

        $response = new Response(json_encode($directories));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function jsonFilesAction()
    {
        $this->assertWatcher();
        $directoryService = $this->getDirectoryService();
        $files = $directoryService->listFiles(new DirectoryPath(), true);

        $response = new Response(json_encode($files));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
