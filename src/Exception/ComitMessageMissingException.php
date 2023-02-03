<?php

namespace Dontdrinkandroot\GitkiBundle\Exception;

use Exception;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ComitMessageMissingException extends Exception
{
    public function __construct()
    {
        parent::__construct('Commit message is missing');
    }
}
