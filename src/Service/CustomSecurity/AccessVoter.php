<?php

declare(strict_types=1);

namespace App\Service\CustomSecurity;

use App\Entity\GraphQL\Role\FullRole;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessVoter extends Voter
{
    public function __construct(
        private readonly RoleHierarchyInterface $roleHierarchy
    ) {
    }

    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    protected function voteOnAttribute(string $requested_action, mixed $subject, TokenInterface $token): bool
    {
        /** @var UserInterface $user */
        $user  = $token->getUser();
        $roles = $this->roleHierarchy->getReachableRoleNames($user->getRoles()); // Get all roles by hierarchy

        $allowed_actions = [];
        foreach ($roles as $role) {
            if (FullRole::ROLE_SUPERADMIN === $role) {
                return true;
            } // ROLE_SUPERADMIN have access for all actions
            $allowed_actions = array_merge($allowed_actions, Actions::$actions[$role]);
        }
        $allowed_actions = array_unique($allowed_actions);

        return in_array($requested_action, $allowed_actions);
    }
}
