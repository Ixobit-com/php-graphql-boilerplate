<?php

namespace App\Service\GraphQL\Auth;

use App\Entity\User;
use App\GraphQL\DTO\Input\userRegistrationInputDTO;
use App\Service\GraphQL\BaseGraphQLService;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Overblog\GraphQLBundle\Annotation as GQL;


#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthMutation')]
#[GQL\Access("hasRole('PUBLIC_ACCESS')")]
class AuthMutationService extends BaseGraphQLService
{
    /**
     * @param userRegistrationInputDTO $user
     * @return User
     * @throws \ReflectionException
     */

    #[GQL\Mutation]
    #[GQL\Arg(name: "user", type: "userRegistrationInputDTO")]
    public function userRegistration(userRegistrationInputDTO $user): User
    {

        $newUser = new User();

        $this->DTOService->hydrateEntityFromDTO($user, $newUser);

        if (isset($user->password)) {
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $user->password));
        }
        $this->entityManager->persist($newUser);
        $this->entityManager->flush();

        return $newUser;
    }

}