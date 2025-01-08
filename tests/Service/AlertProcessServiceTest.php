<?php

namespace App\Tests\Service;

use App\Entity\Alert;
use App\Entity\Enum\AlertStatus;
use App\Entity\Task;
use App\Repository\AlertRepository;
use App\Repository\TaskRepository;
use App\Service\AlertProcessService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use RuntimeException;

class AlertProcessServiceTest extends TestCase
{
    /** @var EntityManagerInterface&MockObject $entityManager */
    private EntityManagerInterface $entityManager;
    /** @var TaskRepository&MockObject $taskRepository */
    private TaskRepository $taskRepository;
    /** @var AlertRepository&MockObject $alertRepository */
    private AlertRepository $alertRepository;
    /** @var ValidatorInterface&MockObject $validator */
    private ValidatorInterface $validator;
    private AlertProcessService $service;
    private string $jsonContent;

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->alertRepository = $this->createMock(AlertRepository::class);
        $this->validator = $this->createMock(ValidatorInterface::class);

        $this->service = new AlertProcessService(
            $this->entityManager,
            $this->taskRepository,
            $this->alertRepository,
            $this->validator
        );

        $this->jsonContent = (string)json_encode([
            'task_id' => '123',
            'alert_type' => AlertStatus::OPEN->value,
            'message' => 'Test message',
            'reason' => 'Test reason',
        ]);

    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testProcessAlertSuccessfullyCreatesOrUpdatesAlert(): void
    {
        $task = $this->createMock(Task::class);
        $alert = $this->createMock(Alert::class);

        $this->taskRepository->method('find')->willReturn($task);
        $this->alertRepository->method('findOneBy')->willReturn($alert);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('persist')->with($this->isInstanceOf(Alert::class));
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('commit');

        $result = $this->service->processAlert($this->jsonContent);

        $this->assertInstanceOf(Alert::class, $result);
    }

    /**
     * @throws \Exception
     */
    public function testProcessAlertThrowsExceptionForInvalidJson(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid JSON');

        $invalidJsonContent = '{invalid json}';

        $this->service->processAlert($invalidJsonContent);
    }

    /**
     * @throws \Exception
     */
    public function testProcessAlertThrowsExceptionWhenTaskNotFound(): void
    {
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Task not found');

        $this->taskRepository->method('find')->willReturn(null);
        $this->validator->method('validate')->willReturn(new ConstraintViolationList());

        $this->service->processAlert($this->jsonContent);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testProcessAlertThrowsExceptionForValidationErrors(): void
    {
        $this->expectException(RuntimeException::class);

        $violationList = new ConstraintViolationList([
            $this->createMock(ConstraintViolation::class),
        ]);
        $this->validator->method('validate')->willReturn($violationList);

        $this->service->processAlert($this->jsonContent);

    }
}