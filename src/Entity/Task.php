<?php

namespace App\Entity;

use App\Entity\Enum\TaskStatus;
use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
class Task
{
    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    #[Serializer\Groups(["task"])]
    private ?string $id = null;

    #[ORM\Column]
    #[Serializer\Groups(["task"])]
    private TaskStatus $status;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?DateTimeImmutable $createdAt;

    #[ORM\ManyToOne(targetEntity: Worker::class)]
    private ?Worker $worker = null;

    public function __construct()
    {
        $this->status = TaskStatus::NEW;
        $this->createdAt = new DateTimeImmutable();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): void
    {
        $this->name = $name;
    }

    public function getStatus(): TaskStatus
    {
        return $this->status;
    }

    public function getCreatedAt(): ?DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function getWorker(): ?Worker
    {
        return $this->worker;
    }

    public function setStatus(TaskStatus $status): void
    {
        $this->status = $status;
    }

    public function setWorker(?Worker $worker): void
    {
        $this->worker = $worker;
    }
}
