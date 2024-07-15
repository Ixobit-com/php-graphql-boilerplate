<?php

namespace App\GraphQL\DTO;

class BaseDTO
{
    /**
     * Disable create undefined properties
     *
     * @param string $name
     * @param $value
     * @return void
     */
    public function __set(string $name, $value): void
    {
        throw new \InvalidArgumentException("Property '$name' is undefined in class " . self::class);
    }

    public function toArray(): array
    {
        return (array)$this;
    }
}