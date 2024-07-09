<?php

namespace App\Service\CustomSecurity;

use Symfony\Component\Security\Core\User\UserInterface;

#[\Attribute] class Actions
{
    // Actions
    // @TODO move to database
    const RETRIEVE_USERS_LIST   = "RETRIEVE_USERS_LIST";
    const RETRIEVE_USER_INFO    = "RETRIEVE_USER_INFO";
    const CREATE_USER           = "CREATE_USER";
    const UPDATE_USER           = "UPDATE_USER";

    // Roles to Actions
    // @TODO move to database
    static array $actions = [
        Roles::ROLE_SUPERADMIN => [
            // Have access for all actions
        ],
        Roles::ROLE_ORGANIZATION_ADMIN => [
            self::RETRIEVE_USERS_LIST,
            self::CREATE_USER
        ],
        Roles::ROLE_USER => [
            self::UPDATE_USER,
            self::RETRIEVE_USER_INFO
        ],
        Roles::ROLE_DRIVER => []
    ];

    static function check(UserInterface $user, string $method): bool
    {
        $actions = [];
        foreach ($user->getRoles() as $role) {
            if ($role === Roles::ROLE_SUPERADMIN) return true; // ROLE_SUPERADMIN have access for all actions
            $actions = array_merge($actions, self::$actions[$role]);
        }
        $actions = array_unique($actions);

        $reflection = new \ReflectionMethod($method);
        foreach ($reflection->getAttributes(self::class) as $attribute) {
            foreach ($attribute->getArguments() as $action) {
                if (in_array($action, $actions)) {
                    return true;
                }
            }
        }
        return false;
    }
}