<?php

namespace App\Service\CustomSecurity;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\User\UserInterface;

class CustomSecurity
{

    const ROLE_SUPERADMIN = "ROLE_SUPERADMIN";
    const ROLE_ORGANIZATION_ADMIN = "ROLE_ORGANIZATION_ADMIN";
    const ROLE_USER = "ROLE_USER";
    const ROLE_DRIVER = "ROLE_DRIVER";


    static array $actions = [
        self::ROLE_ORGANIZATION_ADMIN => [
            "App\Service\GraphQL\User\UserQueryService::users",
        ],
        self::ROLE_USER => [
            "App\Service\GraphQL\User\UserQueryService::user",
        ],
        self::ROLE_DRIVER => [

        ]
    ];

    /**
     * @param UserInterface $user
     * @param $action
     * @return bool
     * @throws AccessDeniedException
     */
    public function checkOrThrowException(UserInterface $user, $action): bool
    {
        $roles = $user->getRoles();
        if (!empty($roles) and in_array(self::ROLE_SUPERADMIN, $roles)) {
            return true;
        }

        if (!empty($roles)) {
            foreach ($roles as $role) {
                if (in_array($action, self::$actions[$role])) {
                    return true;
                }
            }
        }

        throw new AccessDeniedException("User '{$user->getUserIdentifier()}' have not permissions to access '{$action}' action");
    }
}