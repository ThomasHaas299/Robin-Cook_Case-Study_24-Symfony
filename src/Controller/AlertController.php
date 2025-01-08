<?php

namespace App\Controller;

use App\Entity\Alert;
use App\Service\AlertProcessService;
use FOS\RestBundle\Controller\Annotations as Rest;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;

#[Rest\View(serializerGroups: ['alert'])]
class AlertController extends AbstractController
{
    public function __construct(private readonly AlertProcessService $alertProcessService)
    {
    }

    /**
     * @throws \Exception
     */
    #[Rest\Post('/alert')]
    #[Rest\View(serializerGroups: ['alert', 'task'])]
    public function alertAction(Request $request): Alert
    {
        $content = (string) $request->getContent(); // Raw JSON-Data

        $alert = $this->alertProcessService->processAlert($content);

        if (!$alert) {
            throw new \RuntimeException('Alert could not be processed');
        }

        return $alert;
    }
}
