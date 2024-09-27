<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\DTO\User\Output;

use App\Entity\GraphQL\DTO\Common\paginatedOutputDTO;
use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Type(name: 'usersOutputDTO')]
class usersOutputDTO extends paginatedOutputDTO
{
    #[GQL\Field(type: '[User]')]
    #[GQL\Description('Users list')]
    public array $users;
}
