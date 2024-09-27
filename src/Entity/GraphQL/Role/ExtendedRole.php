<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\Role;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Enum(name: 'ExtendedRole')]
readonly class ExtendedRole extends BaseRole
{
    #[GQL\Description('Organization administrator')]
    public const ROLE_ADMIN   = 'ROLE_ADMIN';
}
