<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

class CommitMetadata
{
    public function __construct(
        private readonly string $hash,
        private readonly string $committer,
        private readonly string $email,
        private readonly int $timeStamp,
        private readonly string $message
    ) {
    }

    public function getCommitter(): string
    {
        return $this->committer;
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function getHash(): string
    {
        return $this->hash;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getTimeStamp(): int
    {
        return $this->timeStamp;
    }
}
