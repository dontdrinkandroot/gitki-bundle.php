<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class WikiTest extends FunctionalTest
{
    protected $environment = 'default';

    public function testBrowseRedirect()
    {
        $this->client->followRedirects(false);
        $crawler = $this->client->request(Request::METHOD_GET, '/browse/');
        $this->assertStatusCode(Response::HTTP_FOUND);
        $this->assertEquals('/browse/index.md', $this->client->getResponse()->headers->get('location'));
    }

    public function testExampleAFilenameWithSpaces()
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'browse/examples/a%20filename%20with%20spaces.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testExampleLinkExample()
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'browse/examples/link-example.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testExampleTableExample()
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'browse/examples/table-example.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    public function testExampleTocExample()
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'browse/examples/toc-example.md');
        $this->assertStatusCode(Response::HTTP_OK);
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'default';
    }
}
