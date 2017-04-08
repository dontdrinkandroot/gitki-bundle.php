<?php

use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\app\AppKernel;
use Symfony\Component\Debug\Debug;
use Symfony\Component\HttpFoundation\Request;

/**
 * @var Composer\Autoload\ClassLoader $loader
 */
$loader = require __DIR__ . '/../../../vendor/autoload.php';
Debug::enable();

$kernel = new AppKernel('default', true);
//$kernel->loadClassCache();
$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
