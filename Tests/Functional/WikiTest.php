<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;

class WikiTest extends FunctionalTest
{
    protected $environment = 'default';

    public function testBrowseRedirect()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/');
//        $routes = $this->client->getContainer()->get('router')->getRouteCollection();
//        foreach ($routes as $route) {
//            echo $route->getPath() . "\n";
//        }
        $this->assertEquals(302, $this->client->getResponse()->getStatusCode());
        $this->assertEquals('/browse/index.md', $this->client->getResponse()->headers->get('location'));
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'default';
    }
}
