<?php

namespace App\Tests\Service;

use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\Worker;
use App\Repository\TaskRepository;
use App\Service\TaskAssignerService;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class TaskAssignerServiceTest extends TestCase
{
    private Worker $worker;
    private TaskAssignerService $service;

    /** @var MockObject&Task $task */
    private Task $task;

    /** @var MockObject&TaskRepository $taskRepository */
    private TaskRepository $taskRepository;

    /** @var MockObject&EntityManagerInterface $entityManager */
    private EntityManagerInterface $entityManager;

    /**
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->worker = $this->createMock(Worker::class);
        $this->task = $this->createMock(Task::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->service = new TaskAssignerService($this->entityManager, $this->taskRepository);
    }

    /**
     * @throws Exception
     */
    public function testAssignTaskSuccess(): void
    {
        $this->taskRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['status' => TaskStatus::NEW, 'worker' => null])
            ->willReturn($this->task);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('persist')->with($this->task);
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('commit');

        $this->task->expects($this->once())->method('setStatus')->with(TaskStatus::IN_PROGRESS);
        $this->task->expects($this->once())->method('setWorker')->with($this->worker);

        $result = $this->service->assignTask($this->worker);

        $this->assertSame($this->task, $result);
    }

    /**
     * @throws Exception
     */
    public function testAssignTaskNoTaskAvailable():void
    {
        $this->taskRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['status' => TaskStatus::NEW, 'worker' => null])
            ->willReturn(null);

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->once())->method('commit');

        $result = $this->service->assignTask($this->worker);

        $this->assertNull($result);
    }

    /**
     * @throws Exception
     */
    public function testAssignTaskExceptionRollback():void
    {
        $this->taskRepository->expects($this->once())
            ->method('findOneBy')
            ->with(['status' => TaskStatus::NEW, 'worker' => null])
            ->willThrowException(new Exception('Database error'));

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('rollback');
        $this->entityManager->expects($this->never())->method('commit');

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Database error');

        $this->service->assignTask($this->worker);
    }
}