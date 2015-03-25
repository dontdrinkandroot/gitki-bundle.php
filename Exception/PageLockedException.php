<?php


namespace Dontdrinkandroot\GitkiBundle\Exception;

class PageLockedException extends \Exception
{

    /**
     * @var string
     */
    private $lockedBy;

    /**
     * @var int
     */
    private $expires;

    /**
     * @param string $lockedBy
     * @param int    $expires
     */
    public function __construct($lockedBy, $expires)
    {
        parent::__construct('Page is locked by ' . $lockedBy);
        $this->lockedBy = $lockedBy;
        $this->expires = $expires;
    }

    /**
     * @return int
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return string
     */
    public function getLockedBy()
    {
        return $this->lockedBy;
    }
}
