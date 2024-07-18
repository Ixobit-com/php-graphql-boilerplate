<?php

namespace App\Service\CustomSecurity;

use App\GraphQL\DTO\Role;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

class AccessVoter extends Voter
{

    /**
     * @inheritDoc
     */
    protected function supports(string $attribute, mixed $subject): bool
    {
        return true;
    }

    /**
     * @inheritDoc
     */
    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        /** @var UserInterface $user */
        $user = $token->getUser();

        $actions = [];
        foreach ($user->getRoles() as $role) {
            if ($role === Role::ROLE_SUPERADMIN) return true; // ROLE_SUPERADMIN have access for all actions
            $actions = array_merge($actions, Actions::$actions[$role]);
        }
        $actions = array_unique($actions);

        return in_array($attribute, $actions);
    }
}