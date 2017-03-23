<?php


namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\Path;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
abstract class AbstractPathAwareFileInfo extends \SplFileInfo
{
    /**
     * @return Path
     */
    abstract public function getRelativePath();

    /**
     * @return Path
     */
    abstract public function getAbsolutePath();
}
