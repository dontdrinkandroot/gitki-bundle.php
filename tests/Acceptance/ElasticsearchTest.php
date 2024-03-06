<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Dontdrinkandroot\GitkiBundle\Tests\ElasticsearchReindexTrait;
use Override;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchTest extends WebTestCase
{
    use ElasticsearchReindexTrait;

    #[Override]
    public function setUp(): void
    {
        parent::setUp();
    }

    public function testSearch(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $this->reindex(self::getContainer());
        $crawler = $client->request(Request::METHOD_GET, 'search/');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $form = $crawler->selectButton('form_search')->form(
            [
                'form[searchString]' => 'multiple lines'
            ]
        );
        $crawler = $client->submit($form);

        $resultElements = $crawler->filter('ul.results li');
        $this->assertCount(1, $resultElements);

        $resultLink = $resultElements->filter('a');
        $this->assertEquals('/browse/examples/toc-example.md', $resultLink->attr('href'));
        $this->assertEquals('TOC Example', trim($resultLink->text()));
    }

    public function testFileTitlesInDirectoryIndex(): void
    {
        $client = static::createClient(['environment' => $this->getEnvironment()]);
        $this->reindex(self::getContainer());
        $crawler = $client->request(Request::METHOD_GET, 'browse/examples/?action=list');
        self::assertResponseStatusCodeSame(Response::HTTP_OK);

        $fileNames = $crawler->filter('.ddr-gitki-directory-files .ddr-gitki-item-name');
        $this->assertCount(5, $fileNames);

        $fileNameParts = $crawler->filter('.ddr-gitki-directory-files .ddr-gitki-item-name span');

        $this->assertEquals('A filename with spaces', $fileNameParts->eq(0)->text());
        $this->assertEquals('Link Example', $fileNameParts->eq(2)->text());
        $this->assertEquals('TOC Example', $fileNameParts->eq(4)->text());
        $this->assertEquals('Table Example', $fileNameParts->eq(6)->text());
        $this->assertEquals('textfile.txt', $fileNameParts->eq(8)->text());
    }

    #[Override]
    protected function getEnvironment(): string
    {
        return "elasticsearch";
    }
}
