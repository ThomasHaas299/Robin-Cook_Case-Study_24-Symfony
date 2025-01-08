<?php

namespace App\Service;

use App\Entity\Alert;
use App\Entity\Enum\AlertStatus;
use App\Repository\AlertRepository;
use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;

readonly class AlertProcessService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository         $taskRepository,
        private AlertRepository        $alertRepository
    )
    {
    }

    /**
     * @throws Exception
     */
    public function processAlert(string $content): ?Alert
    {
        $data = $this->validateAndDecodeContent($content);

        $this->entityManager->beginTransaction();

        try {
            $task = $this->taskRepository->find($data->task_id);
            if (!$task) {
                throw new RuntimeException('Task not found');
            }

            $alert = $this->alertRepository->findOneBy(['task' => $task]) ?? new Alert();
            $alert->setTask($task);

            if ($alert->getResolvedAt() == null) {
                // set new values only if alert is not resolved
                $alert->setStatus($data->alert_type);
                if ($data->alert_type === AlertStatus::RESOLVED) {
                    $alert->setResolvedAt(new DateTimeImmutable());
                }
                $alert->setMessage($data->message ?? null);
                $alert->setReason($data->reason ?? null);
            }

            $this->entityManager->persist($alert);
            $this->entityManager->flush();
            $this->entityManager->commit();

            return $alert;
        } catch (Exception $e) {
            $this->entityManager->rollback();
            throw $e;
        }
    }

    /**
     * @throws RuntimeException
     */
    private function validateAndDecodeContent(string $content): object
    {
        $data = json_decode($content);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON');
        }

        if (!isset($data->task_id)) {
            throw new RuntimeException('No Task ID provided');
        }

        $data->alert_type = AlertStatus::tryFrom($data->alert_type);

        if (!isset($data->alert_type)) {
            throw new RuntimeException('No valid Alert Type provided. Alert Type must be one of: ' . implode(', ', AlertStatus::values()) . '.');
        }

        return $data;
    }
}