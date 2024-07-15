<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\GraphQL\DTO\Input\authInputDTO;
use App\GraphQL\DTO\Input\paginationInputDTO;
use App\Service\CustomSecurity\Actions;
use App\Service\GraphQL\BaseGraphQLService;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\User\UserInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'UserQuery')]
class UserQueryService extends BaseGraphQLService
{

    #[GQL\Field(type: "User!")]
    #[Actions(Actions::RETRIEVE_USER_INFO)]
    public function user(): ?User
    {
        $this->checkAccess(__METHOD__);
        return $this->manager->getRepository(User::class)->findOneBy(['email' => $this->security->getUser()->getUserIdentifier()]);
    }

    #[Actions(Actions::RETRIEVE_USERS_LIST)]
    #[GQL\Field(type: "[User]")]
    #[GQL\Arg(name: "pagination", type: "paginationInputDTO")]
    public function users(paginationInputDTO $pagination): array
    {
        $this->checkAccess(__METHOD__);
        // Limit users for current organization only

        return $this->manager->getRepository(User::class)->findBy([], [], $pagination->limit, $pagination->offset);
    }

}