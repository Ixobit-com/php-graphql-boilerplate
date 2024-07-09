<?php

namespace App\Service\CustomSecurity;

use Symfony\Component\Security\Core\User\UserInterface;

class Roles
{
    const ROLE_SUPERADMIN           = "ROLE_SUPERADMIN";
    const ROLE_ORGANIZATION_ADMIN   = "ROLE_ORGANIZATION_ADMIN";
    const ROLE_USER                 = "ROLE_USER";
    const ROLE_DRIVER               = "ROLE_DRIVER";

//    public function __construct() {}
//
//    static function check(UserInterface $user, string $method): bool
//    {
//        $roles = $user->getRoles();
//        if (in_array(self::ROLE_SUPERADMIN, $roles)) {
//            return true; // Super admin have access to all
//        }
//
//        $reflection = new \ReflectionMethod($method);
//        foreach ($reflection->getAttributes(self::class) as $attribute) {
//            foreach ($attribute->getArguments() as $role) {
//                if (in_array($role, $user->getRoles())) {
//                    return true;
//                }
//            }
//        }
//        return false;
//    }
}