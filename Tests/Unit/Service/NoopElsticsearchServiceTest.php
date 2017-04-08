<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Unit\Service;

use Dontdrinkandroot\GitkiBundle\Event\FileChangedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileDeletedEvent;
use Dontdrinkandroot\GitkiBundle\Event\FileMovedEvent;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use Dontdrinkandroot\GitkiBundle\Tests\Functional\Helpers\User;
use Dontdrinkandroot\Path\FilePath;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class NoopElsticsearchServiceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var NoopElasticsearchService
     */
    private $elasticSearchService;

    protected function setUp()
    {
        $this->elasticSearchService = new NoopElasticsearchService();
    }

    public function testSearch()
    {
        $this->assertEquals([], $this->elasticSearchService->search(null));
        $this->assertEquals([], $this->elasticSearchService->search('blabla'));
    }

    public function testMethodCalls()
    {
        $user = new User('asdf', 'as df', 'asdf@example.com', []);
        $filePath = new FilePath('asdf');
        $this->elasticSearchService->indexFile($filePath);
        $this->elasticSearchService->deleteFile($filePath);
        $this->elasticSearchService->clearIndex();
        $this->elasticSearchService->onFileChanged(
            new FileChangedEvent($user, 'test', new DateTime(), $filePath, 'qwer')
        );
        $this->elasticSearchService->onFileDeleted(new FileDeletedEvent($user, 'test', new Date(), $filePath));
        $this->elasticSearchService->onFileMoved(
            new FileMovedEvent($user, 'test', new DateTime(), $filePath, $filePath)
        );
    }
}
