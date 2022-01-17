<?php

namespace Dontdrinkandroot\GitkiBundle\Security;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;

abstract class GitkiVoter extends Voter
{
    /**
     * {@inheritdoc}
     */
    protected function supports(string $attribute, $subject): bool
    {
        return in_array(
            $attribute,
            [SecurityAttribute::READ_HISTORY, SecurityAttribute::WRITE_PATH, SecurityAttribute::READ_PATH],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
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
