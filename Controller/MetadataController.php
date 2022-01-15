<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Utils\StringUtils;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\PathUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class MetadataController extends BaseController
{
    /**
     * @var DirectoryServiceInterface
     */
    private $directoryService;

    public function __construct(SecurityService $securityService, DirectoryServiceInterface $directoryService)
    {
        parent::__construct($securityService);
        $this->directoryService = $directoryService;
    }

    public function directoriesJsonAction(): Response
    {
        $this->assertWatcher();

        $directories = $this->directoryService->listDirectories(new DirectoryPath(), true, true);

        $response = new Response(json_encode($directories));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function filesJsonAction(Request $request): Response
    {
        $this->assertWatcher();

        $currentPath = null;
        $currentPathString = $request->query->get('currentpath', null);
        if (null !== $currentPathString) {
            $currentPath = FilePath::parse($currentPathString);
        }

        $files = $this->directoryService->listFiles(new DirectoryPath(), true);

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
