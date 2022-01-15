<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance\Command;

use Dontdrinkandroot\Common\Asserted;
use Dontdrinkandroot\GitkiBundle\Command\SearchCommand;
use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\KernelTestCase;
use Dontdrinkandroot\GitkiBundle\Tests\ElasticsearchReindexTrait;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class SearchCommandTest extends KernelTestCase
{
    use ElasticsearchReindexTrait;

    public function testSearchEmpty(): void
    {
        static::bootKernel(['environment' => 'elasticsearch']);
        $this->reindex(self::getContainer());
        $application = new Application(static::$kernel);
        $application->add(Asserted::instanceOf(self::getContainer()->get(SearchCommand::class), Command::class));
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

    public function testSearchSuccess(): void
    {
        static::bootKernel(['environment' => 'elasticsearch']);
        $this->reindex(self::getContainer());
        $application = new Application(static::$kernel);
        $application->add(Asserted::instanceOf(self::getContainer()->get(SearchCommand::class), Command::class));
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

    public function testNoElasticsearch(): void
    {
        static::bootKernel(['environment' => 'default']);
        $application = new Application(static::$kernel);
        $application->add(Asserted::instanceOf(self::getContainer()->get(SearchCommand::class), Command::class));
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
