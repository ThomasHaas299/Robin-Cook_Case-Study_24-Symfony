<?php

namespace App\Tests\Service;

use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\Worker;
use App\Exceptions\TaskNotFoundException;
use App\Repository\TaskRepository;
use App\Service\TaskProcessedService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;
use Exception;

class TaskProcessedServiceTest extends TestCase
{
    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testUpdateTaskStatusSuccess():void
    {
        $worker = $this->createMock(Worker::class);
        $task = $this->createMock(Task::class);

        $taskRepository = $this->createMock(TaskRepository::class);
        $taskRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'id' => '123',
                'status' => TaskStatus::IN_PROGRESS,
                'worker' => $worker,
            ])
            ->willReturn($task);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('persist')->with($task);
        $entityManager->expects($this->once())->method('flush');
        $entityManager->expects($this->once())->method('commit');

        $task->expects($this->once())->method('setStatus')->with(TaskStatus::COMPLETED);
        $task->expects($this->once())->method('setWorker')->with(null);
        $worker->expects($this->once())->method('setCurrentTask')->with(null);

        $service = new TaskProcessedService($entityManager, $taskRepository);
        $result = $service->updateTaskStatus($worker, '123', 'completed');

        $this->assertSame($task, $result);
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testUpdateTaskStatusInvalidStatus():void
    {
        $worker = $this->createMock(Worker::class);

        $taskRepository = $this->createMock(TaskRepository::class);
        $entityManager = $this->createMock(EntityManagerInterface::class);

        $service = new TaskProcessedService($entityManager, $taskRepository);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Invalid status');

        $service->updateTaskStatus($worker, '123', 'invalid_status');
    }

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     * @throws Exception
     */
    public function testUpdateTaskStatusTaskNotFound():void
    {
        $worker = $this->createMock(Worker::class);

        $taskRepository = $this->createMock(TaskRepository::class);
        $taskRepository->expects($this->once())
            ->method('findOneBy')
            ->with([
                'id' => '123',
                'status' => TaskStatus::IN_PROGRESS,
                'worker' => $worker,
            ])
            ->willReturn(null);

        $entityManager = $this->createMock(EntityManagerInterface::class);
        $entityManager->expects($this->once())->method('beginTransaction');
        $entityManager->expects($this->once())->method('commit');
        $entityManager->expects($this->never())->method('rollback');

        $service = new TaskProcessedService($entityManager, $taskRepository);

        $result = $service->updateTaskStatus($worker, '123', 'completed');

        $this->assertNull($result);
    }
}