<?php

namespace App\Service\GraphQL\User;

use App\DTO\paginationInputDTO;
use App\Entity\User;
use App\Repository\UserRepository;
use App\Service\CustomSecurity\Roles;
use App\Service\CustomSecurity\Actions;
use App\Service\GraphQL\BaseGraphQLService;


class UserQueryService extends BaseGraphQLService
{
    #[Actions(Actions::RETRIEVE_USER_INFO)]
    public function user(): ?User
    {
        $this->checkAccess(__METHOD__);
        return $this->manager->getRepository(User::class)->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]);
    }

    #[Actions(Actions::RETRIEVE_USERS_LIST)]
    public function users(paginationInputDTO $pagination): array
    {
        $this->checkAccess(__METHOD__);
        // Limit users for current organization only

        return $this->manager->getRepository(User::class)->findBy([], [], $pagination->limit, $pagination->offset);
    }

}