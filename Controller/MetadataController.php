<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\PathUtils;
use Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class MetadataController extends BaseController
{
    public function directoriesJsonAction()
    {
        $this->assertWatcher();

        $directoryService = $this->getDirectoryService();
        $directories = $directoryService->listDirectories(new DirectoryPath(), true, true);

        $response = new Response(json_encode($directories));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function filesJsonAction(Request $request)
    {
        $this->assertWatcher();

        $currentPath = null;
        $currentPathString = $request->query->get('currentpath', null);
        if (null !== $currentPathString) {
            $currentPath = FilePath::parse($currentPathString);
        }

        $directoryService = $this->getDirectoryService();
        $files = $directoryService->listFiles(new DirectoryPath(), true);

        $data = [];
        foreach ($files as $file) {
            $absolutePath = $file->getAbsolutePath();
            $element = [
                'absolutePath' => $absolutePath->toAbsoluteString(),
                'name'         => $absolutePath->getFileName(),
                'extension'    => $absolutePath->getExtension(),
                'title'        => $file->getTitle()
            ];
            if (null !== $currentPath) {
                $relativePath = PathUtils::getPathDiff($currentPath, $absolutePath);
                if (!StringUtils::startsWith($relativePath, '../')) {
                    $relativePath = './' . $relativePath;
                }
                $element['relativePath'] = $relativePath;
            }
            $data[] = $element;
        }

        $response = new Response(json_encode($data));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }
}
