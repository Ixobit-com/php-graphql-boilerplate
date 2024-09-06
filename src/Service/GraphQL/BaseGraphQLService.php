<?php

declare(strict_types=1);

namespace App\Service\GraphQL;

use App\Entity\User;
use GraphQL\Error\UserError;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

abstract class BaseGraphQLService
{
    protected UserInterface|User $user;

    public function __construct()
    {
        if (!$this->security instanceof Security) {
            throw new \LogicException('Class extends BaseGraphqlService must have Security component');
        }
        $this->user = $this->security->getUser();
        if (!$this->user instanceof User) {
            throw new UserError('Internal error');
        }
    }
}
