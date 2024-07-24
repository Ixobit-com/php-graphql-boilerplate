<?php

declare(strict_types=1);

namespace App\Service\GraphQL\Auth;

use App\Entity\GraphQL\DTO\User\Input\userRegistrationInputDTO;
use App\Entity\User;
use App\Service\GraphQL\BaseGraphQLService;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthMutation')]
#[GQL\Access("hasRole('PUBLIC_ACCESS')")]
class AuthMutationService extends BaseGraphQLService
{
    /**
     * Registration new user.
     *
     * @throws \ReflectionException
     */
    #[GQL\Mutation]
    #[GQL\Arg(name: 'user', type: 'userRegistrationInputDTO')]
    public function userRegistration(userRegistrationInputDTO $user): User
    {
        $newUser = new User();

        $this->DTOService->hydrateEntityFromDTO($user, $newUser);

        if (isset($user->password)) {
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $user->password));
        }
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        $this->logger->info(
            sprintf(
                "New user is registered: '%s'",
                $newUser->getUserIdentifier()
            )
        );

        return $newUser;
    }
}
