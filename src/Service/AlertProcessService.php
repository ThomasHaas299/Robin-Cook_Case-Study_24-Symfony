<?php

namespace App\Service;

use App\Entity\Alert;
use App\Entity\Enum\AlertStatus;
use App\Repository\AlertRepository;
use App\Repository\TaskRepository;
use App\Service\DTO\AlertDTO;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use RuntimeException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

readonly class AlertProcessService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private TaskRepository         $taskRepository,
        private AlertRepository        $alertRepository,
        private ValidatorInterface     $validator
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
            $task = $this->taskRepository->find($data->taskId);
            if (!$task) {
                throw new RuntimeException('Task not found');
            }

            $alert = $this->alertRepository->findOneBy(['task' => $task]) ?? new Alert();
            $alert->setTask($task);

            if ($alert->getResolvedAt() == null) {
                // set new values only if alert is not resolved
                $alert->setStatus($data->getStatus());
                if ($data->getStatus() === AlertStatus::RESOLVED) {
                    $alert->setResolvedAt(new DateTimeImmutable());
                }
                $alert->setMessage($data->message);
                $alert->setReason($data->reason);
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
    private function validateAndDecodeContent(string $content): AlertDTO
    {
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new RuntimeException('Invalid JSON');
        }

        $alertDTO = new AlertDTO();
        $alertDTO->taskId = $data['task_id'] ?? '';
        $alertDTO->status = $data['alert_type'] ?? null;
        $alertDTO->message = $data['message'] ?? null;
        $alertDTO->reason = $data['reason'] ?? null;

        $errors = $this->validator->validate($alertDTO);
        if (count($errors) > 0) {
            throw new RuntimeException((string) $errors);
        }

        return $alertDTO;
    }
}