<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Service;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use PHPUnit\Framework\TestCase;

class NoopElasticsearchServiceTest extends TestCase
{
    private NoopElasticsearchService $elasticSearchService;

    /**
     * {@inheritdoc}
     */
    protected function setUp(): void
    {
        $this->elasticSearchService = new NoopElasticsearchService();
    }

    public function testSearch()
    {
        $this->assertEquals([], $this->elasticSearchService->search('blabla'));
    }

//    public function testMethodCalls()
//    {
//        $user = new User('asdf', 'as df', 'asdf@example.com', []);
//        $filePath = new FilePath('asdf');
//        $this->elasticSearchService->indexFile($filePath);
//        $this->elasticSearchService->deleteFile($filePath);
//        $this->elasticSearchService->clearIndex();
//        $this->elasticSearchService->onFileChanged(
//            new FileChangedEvent($user, 'test', new DateTime(), $filePath, 'qwer')
//        );
//        $this->elasticSearchService->onFileRemoved(new FileRemovedEvent($user, 'test', new Date(), $filePath));
//        $this->elasticSearchService->onFileMoved(
//            new FileMovedEvent($user, 'test', new DateTime(), $filePath, $filePath)
//        );
//    }
}
