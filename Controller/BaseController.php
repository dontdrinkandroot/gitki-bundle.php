<?php

namespace Dontdrinkandroot\GitkiBundle\Controller;

use DateTime;
use Dontdrinkandroot\GitkiBundle\Service\Security\SecurityService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

abstract class BaseController extends AbstractController
{
    const ANONYMOUS_ROLE = 'IS_AUTHENTICATED_ANONYMOUSLY';

    public function __construct(protected SecurityService $securityService)
    {
    }

    /**
     * Generate an etag based on the timestamp and the current user.
     */
    protected function generateEtag(DateTime $timeStamp): string
    {
        $user = $this->securityService->findGitUser();
        $userString = '';
        if (null !== $user) {
            $userString = $user->getGitUserName();
        }

        return md5($timeStamp->getTimestamp() . $userString);
    }

    protected function assertAdmin(): void
    {
        $this->securityService->assertAdmin();
    }

    protected function assertWatcher(): void
    {
        $this->securityService->assertWatcher();
    }
}
