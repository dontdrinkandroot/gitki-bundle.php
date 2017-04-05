<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Functional;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ElasticsearchTest extends FunctionalTest
{
    public function setUp()
    {
        parent::setUp();
        $this->reindex();
    }

    protected function reindex()
    {
        $container = self::$kernel->getContainer();
        $wikiService = $container->get('ddr.gitki.service.wiki');
        $elasticSearchService = $container->get('ddr.gitki.service.elasticsearch');
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

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return "elasticsearch";
    }
}
