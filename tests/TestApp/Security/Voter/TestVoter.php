<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\TestApp\Security\Voter;

use Dontdrinkandroot\GitkiBundle\Security\GitkiVoter;
use Dontdrinkandroot\Path\DirectoryPath;
use Dontdrinkandroot\Path\FilePath;
use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;

class TestVoter extends GitkiVoter
{
    #[Override]
    protected function voteOnReadHistory(mixed $subject, TokenInterface $token): bool
    {
        if (null === $subject || $subject instanceof FilePath) {
            /* Public read */
            return true;
        }
        return false;
    }

    #[Override]
    protected function voteOnWritePath(mixed $subject, TokenInterface $token): bool
    {
        if ($subject instanceof DirectoryPath || $subject instanceof FilePath) {
            /* Users can write */
            return in_array('ROLE_USER', $token->getRoleNames(), true);
        }
        return false;
    }

    #[Override]
    protected function voteOnReadPath(mixed $subject, TokenInterface $token): bool
    {
        if (null === $subject || $subject instanceof DirectoryPath || $subject instanceof FilePath) {
            /* Public read */
            return true;
        }
        return false;
    }
}
