<?php

namespace App\Service\CustomSecurity;

use App\Entity\GraphQL\Role\FullRole;

class Actions
{
    // Actions
    // @TODO move to database
    const RETRIEVE_USERS_LIST   = "RETRIEVE_USERS_LIST";
    const RETRIEVE_USER_INFO    = "RETRIEVE_USER_INFO";
    const USER_CREATE           = "USER_CREATE";
    const USER_UPDATE           = "USER_UPDATE";

    // Roles to Actions
    // @TODO move to database
    static array $actions = [
        FullRole::ROLE_SUPERADMIN => [
            // Have access for all actions
        ],
        FullRole::ROLE_ORGANIZATION_ADMIN => [
            self::RETRIEVE_USERS_LIST,
        ],
        FullRole::ROLE_USER => [
            self::USER_UPDATE,
            self::RETRIEVE_USER_INFO
        ],
        FullRole::ROLE_DRIVER => []
    ];

}