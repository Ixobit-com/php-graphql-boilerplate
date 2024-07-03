<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class UserQueryService
{
    private UserInterface $user;

    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
    ) {
        $this->user = $this->security->getUser();
    }

    public function user(): ?User
    {
        return $this->userRepository->findOneBy(['email' => $this->user->getUserIdentifier()]);
    }

    public function users(int $limit, int $offset): array
    {
        return $this->userRepository->findBy([], [], $limit, $offset);
    }

}