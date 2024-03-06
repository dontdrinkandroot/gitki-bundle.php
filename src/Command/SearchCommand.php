<?php

namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use Override;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(name: 'gitki:search', description: 'Search for documents')]
class SearchCommand extends Command
{
    public function __construct(private readonly ElasticsearchServiceInterface $elasticsearchService)
    {
        parent::__construct();
    }

    #[Override]
    protected function configure(): void
    {
        $this
            ->addArgument('searchstring', InputArgument::REQUIRED, 'The search string');
    }

    #[Override]
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
