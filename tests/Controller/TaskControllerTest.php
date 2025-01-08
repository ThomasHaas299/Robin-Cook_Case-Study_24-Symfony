<?php

namespace App\Tests\Controller;

use App\Controller\TaskController;
use App\Entity\Task;
use App\Entity\Worker;
use App\Exceptions\NoTaskAvailableException;
use App\Service\TaskAssignerService;
use PHPUnit\Framework\MockObject\Exception;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class TaskControllerTest extends WebTestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testRequestJobActionSuccess():void
    {
        $worker = $this->createMock(Worker::class);
        $task = $this->createMock(Task::class);

        $taskAssignerService = $this->createMock(TaskAssignerService::class);
        $taskAssignerService->expects($this->once())
            ->method('assignTask')
            ->with($worker)
            ->willReturn($task);

        // Mocking getUser()
        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$taskAssignerService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($worker);

        $response = $controller->requestJobAction();

        $this->assertInstanceOf(Task::class, $response);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testRequestJobActionNoWorker():void
    {
        $taskAssignerService = $this->createMock(TaskAssignerService::class);

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$taskAssignerService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Worker not found');

        $controller->requestJobAction();
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testRequestJobActionWorkerHasTask():void
    {
        $worker = $this->createMock(Worker::class);
        $task = $this->createMock(Task::class);

        $worker->expects($this->once())
            ->method('getCurrentTask')
            ->willReturn($task);

        $taskAssignerService = $this->createMock(TaskAssignerService::class);

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$taskAssignerService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($worker);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Worker already has a task.');

        $controller->requestJobAction();
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testRequestJobActionNoTaskAvailable():void
    {
        $worker = $this->createMock(Worker::class);

        $taskAssignerService = $this->createMock(TaskAssignerService::class);
        $taskAssignerService->expects($this->once())
            ->method('assignTask')
            ->with($worker)
            ->willReturn(null);

        $controller = $this->getMockBuilder(TaskController::class)
            ->setConstructorArgs([$taskAssignerService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($worker);

        $this->expectException(NoTaskAvailableException::class);

        $controller->requestJobAction();
    }
}