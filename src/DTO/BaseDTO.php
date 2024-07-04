<?php

namespace App\DTO;

class BaseDTO
{
    public function __set(string $name, $value): void
    {
        throw new \InvalidArgumentException("Property '$name' is undefined in class " . self::class);
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}