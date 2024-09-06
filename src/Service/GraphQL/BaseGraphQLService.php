<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Entity\User;
use GraphQL\Error\UserError;
use Symfony\Component\Security\Core\User\UserInterface;

class BaseGraphQLService
{
    protected UserInterface|User $user;

    public function __construct()
    {
        $this->user = $this->security->getUser();
        if (!$this->user instanceof User) {
            throw new UserError('Internal error');
        }
    }
}
