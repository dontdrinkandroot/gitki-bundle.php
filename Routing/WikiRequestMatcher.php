<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Routing;

use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\ResourceNotFoundException;
use Symfony\Component\Routing\Matcher\RequestMatcherInterface;

class WikiRequestMatcher implements RequestMatcherInterface
{

    /**
     * @var string
     */
    private $basePath;

    /**
     * @var RouteProviderInterface
     */
    private $routeProvider;

    /**
     * @param        RouteProviderInterface
     * @param string $basePath
     */
    public function __construct(RouteProviderInterface $routeProvider, $basePath = '')
    {
        $this->basePath = $basePath;
        $this->routeProvider = $routeProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function matchRequest(Request $request)
    {
        $pathInfo = $request->getPathInfo();
        $this->checkBasePathMatches($pathInfo);
        $pathInfo = substr($pathInfo, strlen($this->basePath));

        if (preg_match('#^/(?P<repository>[^/]*)(?P<path>.*/)$#s', $pathInfo, $matches)) {
            $repository = $matches['repository'];
            $directoryPath = DirectoryPath::parse($matches['path']);
            $action = $request->query->get('action');

            return $this->resolveDirectoryPath($repository, $directoryPath, $action);
        }

        if (preg_match('#^/(?P<repository>[^/]*)(?P<path>.+)$#s', $pathInfo, $matches)) {
            $repository = $matches['repository'];
            $action = $request->query->get('action');
            $filePath = FilePath::parse($matches['path']);

            return $this->resolveFilePath($repository, $filePath, $action);
        }

        throw new ResourceNotFoundException();
    }

    /**
     * @param string        $repository
     * @param DirectoryPath $directoryPath
     * @param string        $action
     *
     * @return string[]
     */
    protected function resolveDirectoryPath($repository, DirectoryPath $directoryPath, $action)
    {
        $routeDefinition = $this->routeProvider->resolveDirectoryAction($action);

        return [
            '_controller' => $routeDefinition->getController(),
            '_route'      => 'ddr_gitki_wiki_directory',
            'path'        => $directoryPath->toAbsoluteString(),
            'action'      => $action
        ];
    }

    /**
     * @param string   $repository
     * @param FilePath $filePath
     * @param string   $action
     *
     * @return string[]
     */
    protected function resolveFilePath($repository, FilePath $filePath, $action)
    {
        $extension = $filePath->getExtension();
        $routeDefinition = $this->routeProvider->resolveFileAction($action, $extension);

        return [
            '_controller' => $routeDefinition->getController(),
            '_route'      => 'ddr_gitki_wiki_file',
            'path'        => $filePath->toAbsoluteString(),
            'action'      => $action
        ];
    }

    private function checkBasePathMatches($pathInfo)
    {
        if (substr($pathInfo, 0, strlen($this->basePath)) != $this->basePath) {
            throw new ResourceNotFoundException();
        }
    }
}
