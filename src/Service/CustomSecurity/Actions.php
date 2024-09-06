<?php

declare(strict_types=1);

namespace App\Service\CustomSecurity;

use App\Entity\GraphQL\Role\BaseRole;
use App\Entity\GraphQL\Role\ExtendedRole;
use App\Entity\GraphQL\Role\FullRole;

class Actions
{
    // Actions
    public const GET_USERS_LIST   = 'GET_USERS_LIST';
    public const GET_USER_INFO    = 'RETRIEVE_USER_INFO';
    public const USER_CREATE      = 'USER_CREATE';
    public const USER_UPDATE      = 'USER_UPDATE';

    // Roles to Actions
    public static array $actions = [
        FullRole::ROLE_SUPERADMIN => [
            // Have access for all actions
        ],
        ExtendedRole::ROLE_ADMIN => [
            self::GET_USERS_LIST,
            self::USER_UPDATE,
        ],
        BaseRole::ROLE_USER => [
            self::USER_UPDATE,
            self::GET_USER_INFO,
        ],
    ];
}
