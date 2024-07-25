<?php

declare(strict_types=1);

namespace App\Service\GraphQL\User;

use App\Entity\GraphQL\DTO\paginationInputDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'UserQuery')]
#[GQL\Access('isFullyAuthenticated()')]
class UserQueryService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Security $security,
    ) {
    }

    #[GQL\Field(type: 'User!')]
    #[GQL\Access("isGranted('RETRIEVE_USER_INFO')")]
    #[GQL\Description('Get authenticated user info')]
    public function user(): ?User
    {
        return $this->entityManager->getRepository(User::class)->findOneBy(['login' => $this->security->getUser()->getUserIdentifier()]);
    }

    #[GQL\Field(type: '[User]')]
    #[GQL\Arg(name: 'pagination', type: 'paginationInputDTO')]
    #[GQL\Access("isGranted('RETRIEVE_USERS_LIST')")]
    public function users(paginationInputDTO $pagination): array
    {
        // @TODO Limit users for current organization only
        return $this->entityManager->getRepository(User::class)->findBy([], [], $pagination->limit, $pagination->offset);
    }
}
