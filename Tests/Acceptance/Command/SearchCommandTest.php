<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance\Command;

use Dontdrinkandroot\GitkiBundle\Command\SearchCommand;
use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\KernelTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\ElasticsearchReindexTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SearchCommandTest extends KernelTestCase
{
    use ElasticsearchReindexTrait;

    public function testSearchEmpty()
    {
        static::bootKernel(['environment' => 'elasticsearch']);
        $this->reindex(self::$container);
        $application = new Application(static::$kernel);
        $application->add(self::$container->get(SearchCommand::class));
        $command = $application->find('gitki:search');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'      => $command->getName(),
                'searchstring' => 'bla'
            ]
        );

        $this->assertEquals('No results found', trim($commandTester->getDisplay()));
    }

    public function testSearchSuccess()
    {
        static::bootKernel(['environment' => 'elasticsearch']);
        $this->reindex(self::$container);
        $application = new Application(static::$kernel);
        $application->add(self::$container->get(SearchCommand::class));
        $command = $application->find('gitki:search');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'      => $command->getName(),
                'searchstring' => 'muliple lines'
            ]
        );

        $this->assertRegExp('#TOC Example#', $commandTester->getDisplay());
    }

    public function testNoElasticsearch()
    {
        static::bootKernel(['environment' => 'default']);
        $application = new Application(static::$kernel);
        $application->add(self::$container->get(SearchCommand::class));
        $command = $application->find('gitki:search');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command'      => $command->getName(),
                'searchstring' => 'muliple lines'
            ]
        );

        $this->assertEquals("Elasticsearch not configured\n", $commandTester->getDisplay());
    }
}
