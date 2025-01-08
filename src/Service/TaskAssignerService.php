<?php

namespace App\Service;

use App\Entity\Enum\TaskStatus;
use App\Entity\Task;
use App\Entity\Worker;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Exception;

readonly class TaskAssignerService
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
    public function assignTask(Worker $worker): ?Task
    {

        $this->entityManager->beginTransaction();

        try {

            // find a free Task
            $task = $this->taskRepository->findOneBy([
                'status' => TaskStatus::NEW,
                'worker' => null
            ]);

            if (!$task) {
                $this->entityManager->commit();
                return null;
            }

            $task->setStatus(TaskStatus::IN_PROGRESS);
            $task->setWorker($worker);

            $worker->setCurrentTask($task);

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