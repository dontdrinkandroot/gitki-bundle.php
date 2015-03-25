<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RepositoryController extends BaseController
{

    const REQUEST_PARAMETER_ACTION = 'action';

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return Response
     */
    public function directoryAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);
        $extensionRegistry = $this->getExtensionRegistry();
        $action = $request->query->get(self::REQUEST_PARAMETER_ACTION, '');
        $controller = $extensionRegistry->resolveDirectoryAction($action);

        return $this->forward(
            $controller,
            ['path' => $path],
            $request->query->all()
        );
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return Response
     */
    public function fileAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);
        $filePath = FilePath::parse($path);
        $routeProvider = $this->getExtensionRegistry();
        $extensionRegistry = $request->query->get(self::REQUEST_PARAMETER_ACTION, '');
        $controller = $routeProvider->resolveFileAction($extensionRegistry, $filePath->getExtension());

        return $this->forward(
            $controller,
            ['path' => $path],
            $request->query->all()
        );
    }

    /**
     * @return Response
     * @throws \Exception
     * @throws \GitWrapper\GitException
     */
    public function historyAction()
    {
        $this->assertWatcher();

        $history = $this->getWikiService()->getHistory(20);

        return $this->render('DdrGitkiBundle::history.html.twig', ['history' => $history]);
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @throws AccessDeniedHttpException
     */
    protected function checkPreconditions(Request $request, $path)
    {
        if (StringUtils::startsWith($path, '/.git')) {
            throw new AccessDeniedHttpException();
        }
    }
}
