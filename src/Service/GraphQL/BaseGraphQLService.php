<?php

namespace App\Service\GraphQL;

use App\Service\CustomSecurity\Actions;
use App\Service\DTO\DTOService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class BaseGraphQLService
{

    public function __construct(
        protected readonly EntityManagerInterface   $manager,
        protected readonly Security                 $security,
        protected UserPasswordHasherInterface       $passwordHasher,
        protected DTOService                        $DTOService
    ) {}

    /**
     * @param string $method
     * @return bool
     * @throws AccessDeniedException
     */
    protected function checkAccess(string $method): bool
    {
        $current_user = $this->security->getUser();
        if (!Actions::check($current_user, $method)) {
            throw new AccessDeniedException("User {$current_user->getUserIdentifier()} has not access to $method");
        }
        return true;
    }
}