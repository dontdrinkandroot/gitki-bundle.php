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
    public function testReindex()
    {
        static::bootKernel(['environment' => 'elasticsearch']);
        $application = new Application(static::$kernel);
        $application->add(self::$container->get(ReindexCommand::class));
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

    public function testNoElasticsearch()
    {
        static::bootKernel(['environment' => 'default']);
        $application = new Application(static::$kernel);
        $application->add(self::$container->get(ReindexCommand::class));
        $command = $application->find('gitki:reindex');
        $command->setApplication($application);
        $commandTester = new CommandTester($command);
        $commandTester->execute(
            [
                'command' => $command->getName()
            ]
        );

        $this->assertEquals("Elasticsearch not configured\n", $commandTester->getDisplay());
    }

    /**
     * {@inheritdoc}
     */
    protected function getEnvironment(): string
    {
        return 'elasticsearch';
    }
}
