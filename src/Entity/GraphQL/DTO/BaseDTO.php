<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO;

class BaseDTO
{
    /**
     * Disable create undefined properties.
     */
    public function __set(string $name, $value): void
    {
        throw new \InvalidArgumentException("Property '$name' is undefined in class ".self::class);
    }

    public function toArray(): array
    {
        return (array) $this;
    }
}
