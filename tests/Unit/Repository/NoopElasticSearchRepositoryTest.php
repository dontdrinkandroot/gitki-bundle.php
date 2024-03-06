<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Repository;

use Dontdrinkandroot\GitkiBundle\Repository\NoopElasticsearchRepository;
use Dontdrinkandroot\Path\FilePath;
use Override;
use PHPUnit\Framework\TestCase;

class NoopElasticSearchRepositoryTest extends TestCase
{
    private NoopElasticsearchRepository $elasticSearchRepository;

    #[Override]
    protected function setUp(): void
    {
        $this->elasticSearchRepository = new NoopElasticsearchRepository();
    }

    public function testFindTitle(): void
    {
        $this->assertNull($this->elasticSearchRepository->findTitle(new FilePath('assdf')));
    }

    public function testSearch(): void
    {
        $this->assertEquals([], $this->elasticSearchRepository->search('Kaazing'));
    }

//    public function testMethodCalls()
//    {
//        $filePath = new FilePath('asdf');
//        $this->elasticSearchRepository->indexFile($filePath, new AnalyzedDocument($filePath));
//        $this->elasticSearchRepository->clear();
//        $this->elasticSearchRepository->deleteFile($filePath);
//    }
}
