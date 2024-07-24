<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO;

use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Input(name: 'paginationInputDTO')]
final class paginationInputDTO extends BaseDTO
{
    public const MAX_LIMIT = 10;

    #[GQL\InputField(type: 'Int')]
    #[GQL\Description('Max count of result records is '.self::MAX_LIMIT)]
    #[Assert\GreaterThanOrEqual(0)]
    #[Assert\LessThanOrEqual(self::MAX_LIMIT)]
    public int $limit = 10;

    #[GQL\InputField(type: 'Int')]
    #[Assert\GreaterThanOrEqual(0)]
    public int $offset = 0;
}
