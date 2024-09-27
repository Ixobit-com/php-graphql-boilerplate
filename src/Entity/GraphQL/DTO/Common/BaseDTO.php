<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\Common;

class BaseDTO
{
    public function __construct(?array $data = null)
    {
        if (is_array($data) and !empty($data)) {
            foreach ($data as $name => $value) {
                $this->$name = $value;
            }
        }
    }

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

    /**
     * @throws \JsonException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray(), JSON_THROW_ON_ERROR);
    }
}
