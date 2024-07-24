<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\Role;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Enum(name: 'BaseRole')]
readonly class BaseRole
{
    #[GQL\Description('Common user role')]
    public const ROLE_USER                 = 'ROLE_USER';
    #[GQL\Description('Driver')]
    public const ROLE_DRIVER               = 'ROLE_DRIVER';
}
