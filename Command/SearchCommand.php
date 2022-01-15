<?php


namespace Dontdrinkandroot\GitkiBundle\Command;

use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchService;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\ElasticsearchServiceInterface;
use Dontdrinkandroot\GitkiBundle\Service\Elasticsearch\NoopElasticsearchService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SearchCommand extends Command
{
    /**
     * @var ElasticsearchService
     */
    private $elasticsearchService;

    public function __construct(ElasticsearchServiceInterface $elasticsearchService)
    {
        parent::__construct();
        $this->elasticsearchService = $elasticsearchService;
    }

    protected function configure(): void
    {
        $this->setName('gitki:search')
            ->setDescription('Search for Markdown documents')
            ->addArgument('searchstring', InputArgument::REQUIRED, 'The search string');
    }

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
                    '[' . $result->getScore() . '] ' . $result->getTitle() . ' (' . $result->getPath(
                    )->toAbsoluteString() . ')'
                );
            }
        }

        return 0;
    }
}
