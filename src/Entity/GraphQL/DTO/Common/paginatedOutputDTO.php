<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\Common;

use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\Validator\Constraints as Assert;

#[GQL\Type(name: 'paginatedOutputDTO')]
class paginatedOutputDTO extends BaseDTO
{
    #[GQL\Field(type: 'Int')]
    #[Assert\GreaterThanOrEqual(0)]
    public int $limit;

    #[GQL\Field(type: 'Int')]
    #[Assert\GreaterThanOrEqual(0)]
    public int $offset = 0;

    #[GQL\Field(type: 'Int')]
    #[Assert\GreaterThanOrEqual(0)]
    public int $total;
}
