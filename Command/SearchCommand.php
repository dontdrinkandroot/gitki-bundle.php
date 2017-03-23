<?php


namespace Dontdrinkandroot\GitkiBundle\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SearchCommand extends GitkiContainerAwareCommand
{
    protected function configure()
    {
        $this->setName('gitki:search')
            ->setDescription('Search for Markdown documents')
            ->addArgument('searchstring', InputArgument::REQUIRED, 'The search string');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $searchString = $input->getArgument('searchstring');
        $elasticsearchService = $this->getElasticsearchService();
        $results = $elasticsearchService->search($searchString);
        if (count($results) == 0) {
            $output->writeln('No results found');
        } else {
            foreach ($results as $result) {
                $output->writeln(
                    '[' . $result->getScore() . '] ' . $result->getTitle() . ' (' . $result->getPath(
                    )->toAbsoluteString() . ')'
                );
            }
        }
    }
}
