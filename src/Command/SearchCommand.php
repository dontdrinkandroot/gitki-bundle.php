<?php

namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SearchCommand extends Command
{
    protected static $defaultDescription = 'Search for Markdown documents';

    public function __construct(private readonly ElasticsearchServiceInterface $elasticsearchService)
    {
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure(): void
    {
        $this->setName('gitki:search')
            ->addArgument('searchstring', InputArgument::REQUIRED, 'The search string');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        if ($this->elasticsearchService instanceof NoopElasticsearchService) {
            $output->writeln('Elasticsearch not configured');

            return -1;
        }

        $searchString = $input->getArgument('searchstring');
        $results = $this->elasticsearchService->search($searchString);
        if (count($results) == 0) {
            $output->writeln('No results found');
        } else {
            foreach ($results as $result) {
                $output->writeln(
                    '[' . $result->score . '] ' . $result->title . ' (' . $result->path->toAbsoluteString() . ')'
                );
            }
        }

        return Command::SUCCESS;
    }
}
