<?php

namespace App\Controller;

use App\Entity\Task;
use App\Entity\Worker;
use App\Exceptions\TaskNotFoundException;
use App\Service\TaskProcessedService;
use Exception;
use FOS\RestBundle\Controller\Annotations as Rest;
use RuntimeException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Rest\Route("/task")]
class TaskProcessedController extends AbstractController
{

    public function __construct(
        private readonly TaskProcessedService $taskProcessedService
    )
    {
    }

    /**
     * @throws Exception
     */
    #[Rest\Post("/{id}/{status}")]
    #[Rest\View(serializerGroups: ["task"])]
    public function postJobStatus(Request $request):Task
    {
        /** @var Worker|null $worker */
        $worker = $this->getUser();

        if ($worker === null) {
            throw new RuntimeException('Worker not found');
        }

        $id = (string) $request->get('id');
        $status = (string) $request->get('status');

        $task = $this->taskProcessedService->updateTaskStatus($worker, $id, $status);

        if ($task === null) {
            throw new TaskNotFoundException();
        }

        return $task;

    }


}
