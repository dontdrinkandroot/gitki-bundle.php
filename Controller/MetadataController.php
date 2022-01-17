<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\Directory\DirectoryServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Path\PathUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MetadataController extends BaseController
{
    public function __construct(SecurityService $securityService, private DirectoryServiceInterface $directoryService)
    {
        parent::__construct($securityService);
    }

    public function directoriesJsonAction(): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH);

        $directories = $this->directoryService->listDirectories(new DirectoryPath(), true, true);

        $response = new Response(json_encode($directories, JSON_THROW_ON_ERROR));
        $response->headers->set('Content-Type', 'application/json');

        return $response;
    }

    public function filesJsonAction(Request $request): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_PATH);

        $currentPath = null;
        $currentPathString = Asserted::stringOrNull($request->query->get('currentpath'));
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
                if (str_starts_with($relativePath, '../')) {
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
