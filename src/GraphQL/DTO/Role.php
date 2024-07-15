<?php

namespace App\GraphQL\DTO;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Enum(name: "Role")]
final readonly class Role {
    const ROLE_USER = "ROLE_USER";
    const ROLE_DRIVER = "ROLE_DRIVER";
    const ROLE_ORGANIZATION_ADMIN = "ROLE_ORGANIZATION_ADMIN";
    const ROLE_SUPERADMIN = "ROLE_SUPERADMIN";
}