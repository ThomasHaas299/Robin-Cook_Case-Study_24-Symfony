<?php

namespace App\Tests\Controller;

use App\Controller\TaskProcessedController;
use App\Entity\Task;
use App\Entity\Worker;
use App\Exceptions\TaskNotFoundException;
use App\Service\TaskProcessedService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

class TaskProcessedControllerTest extends TestCase
{
    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testPostJobStatusSuccess():void
    {
        $worker = $this->createMock(Worker::class);
        $task = $this->createMock(Task::class);

        $taskProcessedService = $this->createMock(TaskProcessedService::class);
        $taskProcessedService->expects($this->once())
            ->method('updateTaskStatus')
            ->with($worker, '123', 'completed')
            ->willReturn($task);

        $controller = $this->getMockBuilder(TaskProcessedController::class)
            ->setConstructorArgs([$taskProcessedService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($worker);

        $request = new Request(['id' => '123', 'status' => 'completed']);
        $result = $controller->postJobStatus($request);

        $this->assertInstanceOf(Task::class, $result);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testPostJobStatusTaskNotFound():void
    {
        $worker = $this->createMock(Worker::class);

        $taskProcessedService = $this->createMock(TaskProcessedService::class);
        $taskProcessedService->expects($this->once())
            ->method('updateTaskStatus')
            ->with($worker, '123', 'completed')
            ->willReturn(null);

        $controller = $this->getMockBuilder(TaskProcessedController::class)
            ->setConstructorArgs([$taskProcessedService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn($worker);

        $request = new Request(['id' => '123', 'status' => 'completed']);

        $this->expectException(TaskNotFoundException::class);

        $controller->postJobStatus($request);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testPostJobStatusWorkerNotFound():void
    {
        $taskProcessedService = $this->createMock(TaskProcessedService::class);

        $controller = $this->getMockBuilder(TaskProcessedController::class)
            ->setConstructorArgs([$taskProcessedService])
            ->onlyMethods(['getUser'])
            ->getMock();

        $controller->expects($this->once())
            ->method('getUser')
            ->willReturn(null);

        $request = new Request(['id' => '123', 'status' => 'completed']);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Worker not found');

        $controller->postJobStatus($request);
    }
}