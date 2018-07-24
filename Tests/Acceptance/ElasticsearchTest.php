<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchTest extends WebTestCase
{
    public function setUp()
    {
        parent::setUp();
        $this->reindex();
    }

    protected function reindex()
    {
        $container = self::$kernel->getContainer();
        $wikiService = $container->get('test.Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService');
        $elasticSearchService = $container->get(
            'test.Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface'
        );
        $elasticSearchService->clearIndex();
        $filePaths = $wikiService->findAllFiles();
        foreach ($filePaths as $filePath) {
            $elasticSearchService->indexFile($filePath);
        }
    }

    public function testSearch()
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'search/');
        $this->assertStatusCode(Response::HTTP_OK);

        $form = $crawler->selectButton('form_search')->form(
            [
                'form[searchString]' => 'muliple lines'
            ]
        );
        $crawler = $this->client->submit($form);

        $resultElements = $crawler->filter('ul.results li');
        $this->assertCount(1, $resultElements);

        $resultLink = $resultElements->filter('a');
        $this->assertEquals('/browse/examples/toc-example.md', $resultLink->attr('href'));
        $this->assertEquals('TOC Example', trim($resultLink->text()));
    }

    public function testFileTitlesInDirectoryIndex()
    {
        $crawler = $this->client->request(Request::METHOD_GET, 'browse/examples/?action=list');
        $this->assertStatusCode(Response::HTTP_OK);

        $fileNames = $crawler->filter('.ddr-gitki-directory-files .ddr-gitki-item-name');
        $this->assertCount(5, $fileNames);

        $fileNameParts = $crawler->filter('.ddr-gitki-directory-files .ddr-gitki-item-name span');

        $this->assertEquals('A filename with spaces', $fileNameParts->eq(0)->text());
        $this->assertEquals('Link Example', $fileNameParts->eq(2)->text());
        $this->assertEquals('TOC Example', $fileNameParts->eq(4)->text());
        $this->assertEquals('Table Example', $fileNameParts->eq(6)->text());
        $this->assertEquals('textfile.txt', $fileNameParts->eq(8)->text());
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return "elasticsearch";
    }
}
