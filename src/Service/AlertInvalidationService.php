<?php

namespace App\Service;

use App\Entity\Enum\AlertStatus;
use App\Entity\Task;
use App\Repository\AlertRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Throwable;

readonly class AlertInvalidationService
{

    public function __construct(
        private EntityManagerInterface $entityManager,
        private AlertRepository        $alertRepository,
    )
    {
    }

    /**
     * @throws Exception
     */
    public function invalidateAlert(Task $task): void
    {
        $this->entityManager->beginTransaction();
        try {

            $alert = $this->alertRepository->findOneBy(['task' => $task]);
            if (!$alert) {
                // do nothing if alert is not found
                $this->entityManager->rollback();
                return;
            }
            $alert->setStatus(AlertStatus::RESOLVED);
            $alert->setResolvedAt(new DateTimeImmutable());

            $this->entityManager->persist($alert);
            $this->entityManager->flush();

            $this->entityManager->commit();
        } catch (Throwable $exception) {
            $this->entityManager->rollback();
            throw new RuntimeException('Failed to invalidate alert.', 0, $exception);
        }
    }
}
