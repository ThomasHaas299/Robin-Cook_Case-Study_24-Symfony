<?php

namespace App\Service;

use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\Worker;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class TaskProcessedService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository         $taskRepository
    )
    {
    }

    /**
     * @throws Exception
     */
    public function updateTaskStatus(Worker $worker, string $id, string $status): ?Task
    {

        $statusEnum = TaskStatus::tryFrom($status);

        // allow only TaskStatus::COMPLETED or TaskStatus::FAILED
        if (!in_array($statusEnum, [TaskStatus::COMPLETED, TaskStatus::FAILED])) {
            throw new Exception('Invalid status');
        }

        $this->entityManager->beginTransaction();
        try {

            // find the task with the given id and worker
            $task = $this->taskRepository->findOneBy([
                'id' => $id,
                'status' => TaskStatus::IN_PROGRESS,
                'worker' => $worker,
            ]);

            if (!$task) {
                $this->entityManager->commit();
                return null;
            }

            $task->setStatus($statusEnum);
            $task->setWorker(null);

            $worker->setCurrentTask(null);

            $this->entityManager->persist($task);
            $this->entityManager->flush();

            $this->entityManager->commit();
            return $task;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }

    }
}