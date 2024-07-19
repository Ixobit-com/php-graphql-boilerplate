<?php

namespace App\GraphQL\DTO\Role;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Enum(name: "ExtendedRole")]
readonly class ExtendedRole extends BaseRole {
    #[GQL\Description('Organization administrator')]
    const ROLE_ORGANIZATION_ADMIN   = "ROLE_ORGANIZATION_ADMIN";

}