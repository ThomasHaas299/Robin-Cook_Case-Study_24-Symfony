<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Worker;
use App\Exceptions\NoTaskAvailableException;
use App\Service\TaskAssignerService;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

#[Rest\Route("/task")]
class TaskController extends AbstractController
{

    public function __construct(private readonly TaskAssignerService $taskAssigner)
    {
    }

    /**
     * @throws Exception
     */
    #[Rest\Get("/request-task")]
    #[Rest\View(serializerGroups: ["task"])]
    public function requestJobAction(): Task
    {
        /** @var Worker|null $worker */
        $worker = $this->getUser();

        if ($worker === null) {
            throw new RuntimeException('Worker not found');
        }

        $task = $this->taskAssigner->assignTask($worker);

        if ($task === null) {
            throw new NoTaskAvailableException();
        }

        return $task;
    }

}
