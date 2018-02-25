<?php

namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ReindexCommand extends Command
{
    /**
     * @var WikiService
     */
    private $wikiService;

    /**
     * @var ElasticsearchServiceInterface
     */
    private $elasticsearchService;

    public function __construct(WikiService $wikiService, ElasticsearchServiceInterface $elasticsearchService)
    {
        parent::__construct();
        $this->wikiService = $wikiService;
        $this->elasticsearchService = $elasticsearchService;
    }

    protected function configure()
    {
        $this
            ->setName('gitki:reindex')
            ->setDescription('Reindex all Markdown documents');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($this->elasticsearchService instanceof NoopElasticsearchService) {
            $output->writeln('Elasticsearch not configured');

            return -1;
        }

        $this->elasticsearchService->clearIndex();

        $filePaths = $this->wikiService->findAllFiles();

        $progress = new ProgressBar($output, count($filePaths));
        $progress->start();

        foreach ($filePaths as $filePath) {
            $progress->setMessage('Indexing ' . $filePath->toAbsoluteString());
            $progress->advance();

            $this->elasticsearchService->indexFile($filePath);
        }

        $progress->finish();

        $output->writeln('');
    }
}
