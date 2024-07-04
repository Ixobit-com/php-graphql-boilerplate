<?php

namespace App\Service\GraphQL\User;

use App\DTO\paginationInputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CustomSecurity\CustomSecurity;
use Symfony\Bundle\SecurityBundle\Security;


class UserQueryService
{
    public function __construct(
        private UserRepository $userRepository,
        private Security $security,
        private CustomSecurity $customSecurity
    ) {}

    public function user(): ?User
    {
        return $this->userRepository->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]);
    }

    public function users(paginationInputDTO $pagination): array
    {
        $this->customSecurity->checkOrThrowException(user: $this->security->getUser(), action: __METHOD__);

        return $this->userRepository->findBy([], [], $pagination->limit, $pagination->offset);
    }

}