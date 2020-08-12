<?php

use Dontdrinkandroot\GitkiBundle\DdrGitkiBundle;
use Symfony\Bundle\FrameworkBundle\FrameworkBundle;
use Symfony\Bundle\SecurityBundle\SecurityBundle;
use Symfony\Bundle\TwigBundle\TwigBundle;

return [
    new FrameworkBundle(),
    new TwigBundle(),
    new SecurityBundle(),
    new DdrGitkiBundle()
];
