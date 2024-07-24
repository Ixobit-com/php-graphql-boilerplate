<?php

declare(strict_types=1);

namespace App\Service\CustomSecurity;

use App\Entity\GraphQL\Role\FullRole;

class Actions
{
    // Actions
    // @TODO move to database
    public const RETRIEVE_USERS_LIST   = 'RETRIEVE_USERS_LIST';
    public const RETRIEVE_USER_INFO    = 'RETRIEVE_USER_INFO';
    public const USER_CREATE           = 'USER_CREATE';
    public const USER_UPDATE           = 'USER_UPDATE';

    // Roles to Actions
    // @TODO move to database
    public static array $actions = [
        FullRole::ROLE_SUPERADMIN => [
            // Have access for all actions
        ],
        FullRole::ROLE_ORGANIZATION_ADMIN => [
            self::RETRIEVE_USERS_LIST,
        ],
        FullRole::ROLE_USER => [
            self::USER_UPDATE,
            self::RETRIEVE_USER_INFO,
        ],
        FullRole::ROLE_DRIVER => [],
    ];
}
