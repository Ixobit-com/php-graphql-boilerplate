<?php

declare(strict_types=1);

namespace App\Service\GraphQL\User;

use App\Entity\GraphQL\DTO\Common\paginationInputDTO;
use App\Entity\User;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'UserQuery')]
#[GQL\Access('isFullyAuthenticated()')]
class UserQueryService extends BaseGraphQLService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Security $security,
    ) {
        parent::__construct();
    }

    #[GQL\Field(type: 'User!')]
    #[GQL\Description('Get authenticated user info')]
    public function user(): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['login' => $this->user->getUserIdentifier()]);
    }

    #[GQL\Field(type: '[User]')]
    #[GQL\Arg(name: 'pagination', type: 'paginationInputDTO')]
    #[GQL\Access("isGranted('GET_USERS_LIST')")]
    public function users(paginationInputDTO $pagination): array
    {
        return $this->entityManager->getRepository(User::class)->findBy([], [], $pagination->limit, $pagination->offset);
    }
}
