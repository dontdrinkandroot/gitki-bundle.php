<?php


namespace Dontdrinkandroot\Gitki\BaseBundle\Controller;

use Dontdrinkandroot\Gitki\BaseBundle\Routing\ActionResolver;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Dontdrinkandroot\Utils\StringUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class RepositoryController extends BaseController
{

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return Response
     */
    public function directoryAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);
        $directoryPath = DirectoryPath::parse($path);
        $routeProvider = $this->getActionResolver();
        $action = $request->query->get('action', '');
        $controller = $routeProvider->resolveDirectoryAction($action);

        return $this->forward($controller,['path' => $path]);
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
        $routeProvider = $this->getActionResolver();
        $action = $request->query->get('action', '');
        $controller = $routeProvider->resolveFileAction($action, $filePath->getExtension());

        return $this->forward($controller, ['path' => $path]);
    }

    /**
     * @return Response
     * @throws \Exception
     * @throws \GitWrapper\GitException
     */
    public function historyAction()
    {
        $history = $this->getWikiService()->getHistory(20);

        return $this->render('DdrGitkiBaseBundle:Wiki:history.html.twig', ['history' => $history]);
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

    /**
     * @return ActionResolver
     */
    protected function getActionResolver()
    {
        return $this->get('ddr.gitki.router.action_resolver');
    }
}
