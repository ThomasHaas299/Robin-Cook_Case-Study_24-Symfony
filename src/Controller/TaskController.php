<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Worker;
use App\Exceptions\NoTaskAvailableException;
use App\Service\TaskAssignerService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Rest\Route('/task')]
class TaskController extends AbstractController
{
    public function __construct(private readonly TaskAssignerService $taskAssigner)
    {
    }

    /**
     * @throws \Exception
     */
    #[Rest\Get('/request-task')]
    #[Rest\View(serializerGroups: ['task'])]
    public function requestJobAction(): Task
    {
        /** @var Worker|null $worker */
        $worker = $this->getUser();

        if (null === $worker) {
            throw new \RuntimeException('Worker not found');
        }

        if (null !== $worker->getCurrentTask()) {
            throw new \RuntimeException('Worker already has a task.');
        }

        $task = $this->taskAssigner->assignTask($worker);

        if (null === $task) {
            throw new NoTaskAvailableException();
        }

        return $task;
    }
}
