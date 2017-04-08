<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Repository;

use Dontdrinkandroot\GitkiBundle\Model\Document\AnalyzedDocument;
use Dontdrinkandroot\GitkiBundle\Repository\NoopElasticsearchRepository;
use Dontdrinkandroot\Path\FilePath;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class NoopElasticSearchRepositoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NoopElasticsearchRepository
     */
    private $elasticSearchRepository;

    protected function setUp()
    {
        $this->elasticSearchRepository = new NoopElasticsearchRepository();
    }

    public function testFindTitle()
    {
        $this->assertNull($this->elasticSearchRepository->findTitle(new FilePath('assdf')));
    }

    public function testSearch()
    {
        $this->assertEquals([], $this->elasticSearchRepository->search('asdf'));
    }

    public function testMethodCalls()
    {
        $filePath = new FilePath('asdf');
        $this->elasticSearchRepository->indexFile($filePath, new AnalyzedDocument($filePath));
        $this->elasticSearchRepository->clear();
        $this->elasticSearchRepository->deleteFile($filePath);
    }
}
