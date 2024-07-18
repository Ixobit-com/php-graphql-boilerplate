<?php

namespace App\Service\CustomSecurity;

use App\GraphQL\DTO\Role;
use Symfony\Component\Security\Core\User\UserInterface;

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
        Role::ROLE_SUPERADMIN => [
            // Have access for all actions
        ],
        Role::ROLE_ORGANIZATION_ADMIN => [
            self::RETRIEVE_USERS_LIST,
        ],
        Role::ROLE_USER => [
            self::USER_UPDATE,
            self::RETRIEVE_USER_INFO
        ],
        Role::ROLE_DRIVER => []
    ];

}