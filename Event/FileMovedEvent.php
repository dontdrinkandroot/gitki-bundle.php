<?php

namespace Dontdrinkandroot\GitkiBundle\Event;

use Dontdrinkandroot\GitkiBundle\Model\GitUserInterface;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class FileMovedEvent extends AbstractFileEvent
{
    const NAME = 'ddr.gitki.file.moved';

    /**
     * @var FilePath
     */
    private $previousFile;

    public function __construct(GitUserInterface $user, $commitMessage, $time, FilePath $file, FilePath $previousFile)
    {
        parent::__construct($user, $commitMessage, $time, $file);
        $this->previousFile = $previousFile;
    }

    /**
     * @return FilePath
     */
    public function getPreviousFile()
    {
        return $this->previousFile;
    }
}
