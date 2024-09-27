<?php

declare(strict_types=1);

namespace App\Entity\GraphQL\Role;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Enum(name: 'Role')]
final readonly class FullRole extends ExtendedRole
{
    #[GQL\Description('System administrator')]
    public const ROLE_SUPERADMIN           = 'ROLE_SUPERADMIN';
}
