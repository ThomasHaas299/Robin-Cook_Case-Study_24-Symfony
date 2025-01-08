<?php

namespace App\Tests\Controller;

use App\Controller\AlertController;
use App\Entity\Alert;
use App\Service\AlertProcessService;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use RuntimeException;
use Symfony\Component\HttpFoundation\Request;

class AlertControllerTest extends TestCase
{
    /** @var AlertProcessService&MockObject */
    private AlertProcessService $alertProcessService;
    private AlertController $controller;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->alertProcessService = $this->createMock(AlertProcessService::class);
        $this->controller = new AlertController($this->alertProcessService);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testAlertActionProcessesAlertSuccessfully(): void
    {
        $mockAlert = $this->createMock(Alert::class);
        $this->alertProcessService->method('processAlert')->willReturn($mockAlert);

        $request = new Request([], [], [], [], [], [], (string)json_encode(['example' => 'data']));
        $result = $this->controller->alertAction($request);

        $this->assertInstanceOf(Alert::class, $result);
    }

    /**
     * @throws \Exception
     */
    public function testAlertActionThrowsExceptionWhenProcessingFails(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Alert could not be processed');

        $this->alertProcessService->method('processAlert')->willReturn(null);

        $request = new Request([], [], [], [], [], [], (string)json_encode(['example' => 'data']));
        $this->controller->alertAction($request);
    }

    /**
     * @throws \Exception
     */
    public function testAlertActionHandlesInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Alert could not be processed');

        $this->alertProcessService->method('processAlert')->willReturn(null);

        $request = new Request([], [], [], [], [], [], 'Invalid JSON');
        $this->controller->alertAction($request);
    }
}