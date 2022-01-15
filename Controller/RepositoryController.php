<?php


namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\GitkiBundle\Utils\StringUtils;
use Dontdrinkandroot\Path\FilePath;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symplify\GitWrapper\Exception\GitException;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class RepositoryController extends BaseController
{
    const REQUEST_PARAMETER_ACTION = 'action';

    /**
     * @var ExtensionRegistryInterface
     */
    private $extensionRegistry;

    /**
     * @var WikiService
     */
    private $wikiService;

    /**
     * RepositoryController constructor.
     */
    public function __construct(
        SecurityService $securityService,
        ExtensionRegistryInterface $extensionRegistry,
        WikiService $wikiService
    ) {
        parent::__construct($securityService);
        $this->extensionRegistry = $extensionRegistry;
        $this->wikiService = $wikiService;
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @return Response
     */
    public function directoryAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);
        $action = $request->query->get(self::REQUEST_PARAMETER_ACTION, '');
        $controller = $this->extensionRegistry->resolveDirectoryAction($action);

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
        $extensionRegistry = $request->query->get(self::REQUEST_PARAMETER_ACTION, '');
        $controller = $this->extensionRegistry->resolveFileAction($extensionRegistry, $filePath->getExtension());

        return $this->forward(
            $controller,
            ['path' => $path],
            $request->query->all()
        );
    }

    /**
     * @return Response
     * @throws Exception
     * @throws GitException
     */
    public function historyAction()
    {
        $this->securityService->assertWatcher();

        $history = $this->wikiService->getHistory(20);

        return $this->render('@DdrGitki/history.html.twig', ['history' => $history]);
    }

    /**
     * @param Request $request
     * @param string  $path
     *
     * @throws AccessDeniedHttpException
     */
    protected function checkPreconditions(Request $request, $path): void
    {
        if (str_starts_with($path, '/.git')) {
            throw new AccessDeniedHttpException();
        }
    }
}
