<?php

namespace App\Tests\Service;

use App\Entity\Alert;
use App\Entity\Enum\AlertStatus;
use App\Entity\Task;
use App\Repository\AlertRepository;
use App\Service\AlertInvalidationService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Exception;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AlertInvalidationServiceTest extends TestCase
{
    /** @var MockObject&EntityManagerInterface */
    private EntityManagerInterface $entityManager;
    /** @var MockObject&AlertRepository */
    private AlertRepository $alertRepository;
    private AlertInvalidationService $service;
    private Task $task;

    /**
     * @return void
     */
    public function assertEntityManagerRollback(): void
    {
        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('rollback');
        $this->entityManager->expects($this->never())->method('persist');
        $this->entityManager->expects($this->never())->method('flush');
        $this->entityManager->expects($this->never())->method('commit');
    }

    /**
     * @throws Exception
     */
    protected function setUp(): void
    {
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->alertRepository = $this->createMock(AlertRepository::class);
        $this->service = new AlertInvalidationService($this->entityManager, $this->alertRepository);
        $this->task = $this->createMock(Task::class);
    }

    /**
     * @throws Exception
     * @throws \Exception
     */
    public function testInvalidateAlertSuccessfullyResolvesAlert(): void
    {
        $alert = $this->createMock(Alert::class);
        $alert->method('getStatus')->willReturn(AlertStatus::OPEN);

        $this->alertRepository->method('findOneBy')->with(['task' => $this->task])->willReturn($alert);
        $this->entityManager->expects($this->once())->method('beginTransaction');
        $this->entityManager->expects($this->once())->method('persist')->with($alert);
        $this->entityManager->expects($this->once())->method('flush');
        $this->entityManager->expects($this->once())->method('commit');

        $alert->expects($this->once())->method('setStatus')->with(AlertStatus::RESOLVED);
        $alert->expects($this->once())->method('setResolvedAt')->with($this->isInstanceOf(\DateTimeImmutable::class));

        $this->service->invalidateAlert($this->task);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidateAlertDoesNothingIfNoAlertFound(): void
    {
        $this->alertRepository->method('findOneBy')->with(['task' => $this->task])->willReturn(null);
        $this->assertEntityManagerRollback();

        $this->service->invalidateAlert($this->task);
    }

    /**
     * @throws \Exception
     * @throws Exception
     */
    public function testInvalidateAlertDoesNothingIfAlertAlreadyResolved(): void
    {
        $alert = $this->createMock(Alert::class);
        $alert->method('getStatus')->willReturn(AlertStatus::RESOLVED);

        $this->alertRepository->method('findOneBy')->with(['task' => $this->task])->willReturn($alert);
        $this->assertEntityManagerRollback();

        $this->service->invalidateAlert($this->task);
    }

    /**
     * @throws \Exception
     */
    public function testInvalidateAlertThrowsExceptionOnFailure(): void
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Failed to invalidate alert.');

        $this->alertRepository->method('findOneBy')->with(['task' => $this->task])->willThrowException(new \Exception('Some database error'));
        $this->assertEntityManagerRollback();

        $this->service->invalidateAlert($this->task);
    }
}
