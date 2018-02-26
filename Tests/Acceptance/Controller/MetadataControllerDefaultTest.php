<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance\Controller;

use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class MetadataControllerDefaultTest extends WebTestCase
{
    public function testDirectoriesAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/meta/directories.json');
        $this->assertStatusCode(Response::HTTP_OK);

        $response = $this->client->getResponse();
        $content = $response->getContent();
        $deserializedContent = json_decode($content, true);

        $this->assertCount(3, $deserializedContent);
    }

    public function testFilesAction()
    {
        $crawler = $this->client->request(Request::METHOD_GET, '/meta/files.json');
        $this->assertStatusCode(Response::HTTP_OK);

        $response = $this->client->getResponse();
        $content = $response->getContent();
        $deserializedContent = json_decode($content, true);

        $this->assertCount(6, $deserializedContent);
    }

    protected function getEnvironment(): string
    {
        return 'default';
    }
}
