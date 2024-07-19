<?php

namespace App\GraphQL\DTO\Role;

use Overblog\GraphQLBundle\Annotation as GQL;

#[GQL\Enum(name: "BaseRole")]
readonly class BaseRole {

    #[GQL\Description('Common user role')]
    const ROLE_USER                 = "ROLE_USER";
    #[GQL\Description('Driver')]
    const ROLE_DRIVER               = "ROLE_DRIVER";

}