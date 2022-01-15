<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance\Controller;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\WebTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MetadataControllerDefaultTest extends WebTestCase
{
    public function testDirectoriesAction(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/meta/directories.json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $client->getResponse();
        $content = Asserted::string($response->getContent());
        $deserializedContent = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(3, $deserializedContent);
    }

    public function testFilesAction(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $crawler = $client->request(Request::METHOD_GET, '/meta/files.json');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $response = $client->getResponse();
        $content = Asserted::string($response->getContent());
        $deserializedContent = json_decode($content, true, 512, JSON_THROW_ON_ERROR);

        $this->assertCount(6, $deserializedContent);
    }

    protected function getEnvironment(): string
    {
        return 'default';
    }
}
