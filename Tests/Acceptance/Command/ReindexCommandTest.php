<?php

namespace Dontdrinkandroot\GitkiBundle\Tests\Acceptance\Command;

use Dontdrinkandroot\GitkiBundle\Command\ReindexCommand;
use Dontdrinkandroot\GitkiBundle\Tests\Acceptance\KernelTestCase;
use Symfony\Bundle\FrameworkBundle\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

/**
 * @author Philip Washington Sorst <philip@sorst.net>
 */
class ReindexCommandTest extends KernelTestCase
{
    public function testSearchEmpty()
    {
        $application = new Application(static::$kernel);
        $application->add(new ReindexCommand());
        $command = $application->find('gitki:reindex');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName()
            ]
        );

        $this->assertNotEmpty($commandTester->getDisplay());
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'elasticsearch';
    }
}
