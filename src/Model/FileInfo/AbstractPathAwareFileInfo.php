<?php

namespace Dontdrinkandroot\GitkiBundle\Model\FileInfo;

use Dontdrinkandroot\Path\Path;
use SplFileInfo;

abstract class AbstractPathAwareFileInfo extends SplFileInfo
{
    abstract public function getRelativePath(): Path;

    abstract public function getAbsolutePath(): Path;
}
