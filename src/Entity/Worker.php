<?php

namespace App\Entity;

use App\Repository\WorkerRepository;
use Doctrine\ORM\Mapping as ORM;
use Random\RandomException;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: WorkerRepository::class)]
class Worker implements UserInterface
{

    #[ORM\Id]
    #[ORM\Column(type: 'string', length: 36, unique: true)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private ?string $id = null;

    #[ORM\Column]
    private bool $disabled = false;

    #[ORM\Column(nullable: false)]
    private string $accessToken;

    /**
     * @throws RandomException
     */
    public function __construct()
    {
        $this->accessToken = bin2hex(random_bytes(32));
    }

    public function isDisabled(): bool
    {
        return $this->disabled;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }


    public function getRoles(): array
    {
        return ['ROLE_WORKER'];
    }

    public function eraseCredentials(): void
    {
    }

    public function getUserIdentifier(): string
    {
        return $this->id;
    }
}
