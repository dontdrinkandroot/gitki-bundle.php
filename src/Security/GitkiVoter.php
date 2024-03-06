<?php

namespace Dontdrinkandroot\GitkiBundle\Security;

use Override;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

/**
 * @extends Voter<'GITKI_READ_HISTORY'|'GITKI_WRITE_PATH'|'GITKI_READ_PATH',mixed>
 */
abstract class GitkiVoter extends Voter
{
    #[Override]
    protected function supports(string $attribute, $subject): bool
    {
        return in_array(
            $attribute,
            [SecurityAttribute::READ_HISTORY, SecurityAttribute::WRITE_PATH, SecurityAttribute::READ_PATH],
            true
        );
    }

    #[Override]
    protected function voteOnAttribute(string $attribute, $subject, TokenInterface $token): bool
    {
        return match ($attribute) {
            SecurityAttribute::READ_HISTORY => $this->voteOnReadHistory($subject, $token),
            SecurityAttribute::WRITE_PATH => $this->voteOnWritePath($subject, $token),
            SecurityAttribute::READ_PATH => $this->voteOnReadPath($subject, $token)
        };
    }

    abstract protected function voteOnReadHistory(mixed $subject, TokenInterface $token): bool;

    abstract protected function voteOnWritePath(mixed $subject, TokenInterface $token): bool;

    abstract protected function voteOnReadPath(mixed $subject, TokenInterface $token): bool;
}
