<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Security\SecurityAttribute;
use Dontdrinkandroot\GitkiBundle\Service\ExtensionRegistry\ExtensionRegistryInterface;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Dontdrinkandroot\Path\FilePath;
use Exception;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symplify\GitWrapper\Exception\GitException;

class RepositoryController extends BaseController
{
    public const REQUEST_PARAMETER_ACTION = 'action';

    public function __construct(
        SecurityService $securityService,
        private ExtensionRegistryInterface $extensionRegistry,
        private WikiService $wikiService
    ) {
        parent::__construct($securityService);
    }

    /**
     * @param Request $request
     * @param string $path
     *
     * @return Response
     */
    public function directoryAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);
        $action = (string)$request->query->get(self::REQUEST_PARAMETER_ACTION, '');
        $controller = $this->extensionRegistry->resolveDirectoryAction($action);

        return $this->forward(
            $controller,
            ['path' => $path],
            $request->query->all()
        );
    }

    /**
     * @param Request $request
     * @param string $path
     *
     * @return Response
     */
    public function fileAction(Request $request, $path)
    {
        $this->checkPreconditions($request, $path);
        $filePath = FilePath::parse($path);
        $action = (string)$request->query->get(self::REQUEST_PARAMETER_ACTION, '');
        $controller = $this->extensionRegistry->resolveFileAction(
            $action,
            Asserted::notNull($filePath->getExtension())
        );

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
    public function historyAction(): Response
    {
        $this->denyAccessUnlessGranted(SecurityAttribute::READ_HISTORY);

        $history = $this->wikiService->getHistory(20);

        return $this->render('@DdrGitki/history.html.twig', ['history' => $history]);
    }

    /**
     * @param Request $request
     * @param string $path
     *
     * @throws AccessDeniedHttpException
     */
    protected function checkPreconditions(Request $request, string $path): void
    {
        if (str_starts_with($path, '/.git')) {
            throw new AccessDeniedHttpException();
        }
    }
}
