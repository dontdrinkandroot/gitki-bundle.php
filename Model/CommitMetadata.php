<?php

namespace Dontdrinkandroot\GitkiBundle\Model;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class CommitMetadata
{
    /**
     * @var string
     */
    private $hash;

    /**
     * @var string
     */
    private $committer;

    /**
     * @var string
     */
    private $email;

    /**
     * @var int
     */
    private $timeStamp;

    /**
     * @var string
     */
    private $message;

    public function __construct(string $hash, string $committer, string $eMail, int $timeStamp, string $message)
    {
        $this->hash = $hash;
        $this->committer = $committer;
        $this->email = $eMail;
        $this->timeStamp = $timeStamp;
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getCommitter(): string
    {
        return $this->committer;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @return string
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * @return string
     */
    public function getMessage(): string
    {
        return $this->message;
    }

    /**
     * @return int
     */
    public function getTimeStamp(): int
    {
        return $this->timeStamp;
    }
}
