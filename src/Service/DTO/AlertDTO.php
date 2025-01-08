<?php

namespace App\Service\DTO;

use App\Entity\Enum\AlertStatus;
use Symfony\Component\Validator\Constraints as Assert;

class AlertDTO
{
    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $taskId;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    #[Assert\Choice(callback: 'getStatuses', message: 'Invalid alert type. Allowed values are: {{ choices }}.')]
    public string $status;

    #[Assert\NotBlank]
    #[Assert\Type('string')]
    public string $message;

    #[Assert\Type('string')]
    public ?string $reason;

    public function getStatus(): AlertStatus
    {
        $alertStatus = AlertStatus::tryFrom($this->status);
        if (null === $alertStatus) {
            throw new \InvalidArgumentException('Invalid alert type');
        }

        return $alertStatus;
    }

    /**
     * @return array<string>
     */
    public static function getStatuses(): array
    {
        return AlertStatus::values();
    }
}
