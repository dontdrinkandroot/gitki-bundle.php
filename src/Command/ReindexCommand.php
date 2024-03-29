<?php

namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use Dontdrinkandroot\GitkiBundle\Service\Wiki\WikiService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'gitki:reindex', description: 'Reindex all documents')]
class ReindexCommand extends Command
{
    public function __construct(
        private readonly WikiService $wikiService,
        private readonly ElasticsearchServiceInterface $elasticsearchService
    ) {
        parent::__construct();
    }

    #[Override]
    protected function execute(InputInterface $input, OutputInterface $output): int
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

        return Command::SUCCESS;
    }
}
