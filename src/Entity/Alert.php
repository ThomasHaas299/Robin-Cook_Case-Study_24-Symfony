<?php

namespace App\Entity;

use App\Entity\Enum\AlertStatus;
use App\Repository\AlertRepository;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: AlertRepository::class)]
class Alert
{
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: Task::class)]
    #[Serializer\Groups(["task"])]
    private Task $task;

    #[ORM\Column(length: 255)]
    #[Serializer\Groups(["alert"])]
    private AlertStatus $status;

    #[ORM\Column]
    private ?DateTimeImmutable $raisedAt = null;

    #[ORM\Column(nullable: true)]
    private ?DateTimeImmutable $resolvedAt = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $message = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $reason = null;

    public function getTask(): Task
    {
        return $this->task;
    }

    public function setTask(Task $task)
    {
        $this->task = $task;
    }

    public function getStatus(): AlertStatus
    {
        return $this->status;
    }

    public function setStatus(AlertStatus $status): void
    {
        if ($status === AlertStatus::OPEN) {
            $this->raisedAt = new DateTimeImmutable();
        }

        $this->status = $status;

    }

    public function getResolvedAt(): ?DateTimeImmutable
    {
        return $this->resolvedAt;
    }

    public function setResolvedAt(?DateTimeImmutable $resolvedAt): void
    {
        $this->resolvedAt = $resolvedAt;
    }

    public function getMessage(): ?string
    {
        return $this->message;
    }

    public function setMessage(?string $message): void
    {
        $this->message = $message;
    }

    public function getReason(): ?string
    {
        return $this->reason;
    }

    public function setReason(?string $reason): void
    {
        $this->reason = $reason;
    }
}
