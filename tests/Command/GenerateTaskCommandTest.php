<?php

namespace App\Tests\Command;

use App\Command\GenerateTaskCommand;
use App\Entity\Task;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class GenerateTaskCommandTest extends TestCase
{
    /**
     * @throws Exception
     */
    public function testExecute(): void
    {
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())
            ->method('persist')
            ->with($this->isInstanceOf(Task::class));
        $entityManager->expects($this->once())
            ->method('flush');

        $command = new GenerateTaskCommand($entityManager);

        $application = new Application();
        $application->add($command);

        $commandTester = new CommandTester($application->find('app:generate-task'));
        $commandTester->execute([]);

        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Task created successfully!', $output);
        $this->assertEquals(0, $commandTester->getStatusCode());
    }
}
