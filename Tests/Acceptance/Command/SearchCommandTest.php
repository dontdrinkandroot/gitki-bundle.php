<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance\Command;

use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class SearchCommandTest extends KernelTestCase
{
    public function testSearchEmpty()
    {
        $application = new Application(static::$kernel);
        $application->add(
            $application->add(
                static::$kernel->getContainer()->get('test.Dontdrinkandroot\GitkiBundle\Command\SearchCommand')
            )
        );
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
        $application = new Application(static::$kernel);
        $application->add(
            $application->add(
                static::$kernel->getContainer()->get('test.Dontdrinkandroot\GitkiBundle\Command\SearchCommand')
            )
        );
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

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'elasticsearch';
    }
}
