<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;

class UserQueryService
{
    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
    ) {}

    public function profile(): ?User
    {
        $user = $this->security->getUser();
        return $this->userRepository->findOneBy(['email' => $user->getUserIdentifier()]);
    }

}