<?php


namespace Dontdrinkandroot\GitkiBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ReindexCommand extends GitkiContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gitki:reindex')
            ->setDescription('Reindex all Markdown documents');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $wikiService = $this->getWikiService();
        $elasticSearchService = $this->getElasticsearchService();

        $elasticSearchService->clearIndex();

        $filePaths = $wikiService->findAllFiles();

        $progress = new ProgressBar($output, count($filePaths));
        $progress->start();

        foreach ($filePaths as $filePath) {
            $progress->setMessage('Indexing ' . $filePath->toAbsoluteString());
            $progress->advance();

            $elasticSearchService->indexFile($filePath);
        }

        $progress->finish();

        $output->writeln('');
    }
}
