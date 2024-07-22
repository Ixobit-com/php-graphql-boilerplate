<?php

namespace App\Entity\GraphQL\Language\AST\Node;

use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\ScalarType;

class DateTimeType extends ScalarType
{
    /**
     * @param \DateTimeInterface $value
     *
     * @return string
     */
    public function serialize($value): mixed
    {
        return $value->format('Y-m-d H:i:s');
    }

    /**
     * @param mixed $value
     *
     * @return \DateTimeInterface
     */
    public function parseValue($value): mixed
    {

        return new \DateTimeImmutable($value);
    }

    /**
     * @param Node $valueNode
     *
     * @return \DateTimeInterface
     */
    public function parseLiteral($valueNode, array $variables = null): mixed
    {
        return new \DateTimeImmutable($valueNode->value);
    }
}