<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\GraphQL\DTO\Input\userUpdateInputDTO;
use App\GraphQL\DTO\Role;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Overblog\GraphQLBundle\Annotation as GQL;


#[Autoconfigure(public: true)]
#[GQL\Type(name: 'UserMutation')]
class UserMutationService extends BaseGraphQLService
{
    /**
     * @param int $id
     * @param userUpdateInputDTO $user
     * @return User
     * @throws \ReflectionException
     */
    #[GQL\Field(type: "User")]
    #[GQL\Access("isGranted('USER_UPDATE')")]
    #[GQL\Arg(name: "id", type: "ID!")]
    #[GQL\Arg(name: "user", type: "userUpdateInputDTO")]
    public function userUpdate(int $id, userUpdateInputDTO $user): User
    {

        $user_entity = $this->entityManager->getRepository(User::class)->find($id);
        if (! $user_entity instanceof User) {
            throw new EntityNotFoundException("User #{$id} not found");
        }

        if (
            !(
                // Superadmin has full access
                in_array(Role::ROLE_SUPERADMIN, $this->security->getUser()->getRoles()) or
                (
                    // additional check (is the current user is organization admin and updated user from his organization
                    in_array(Role::ROLE_ORGANIZATION_ADMIN, $this->security->getUser()->getRoles()) and false
                ) or
                // user update himself
                $this->security->getUser()->getUserIdentifier() === $user_entity->getUserIdentifier()
            )
        ) {
            throw new AccessDeniedException("User {$this->security->getUser()->getUserIdentifier()} has not access rights to update another user");
        }

        if (!empty($user->password)) {
            $user->password = $this->passwordHasher->hashPassword($user_entity, $user->password);
        }

        $this->DTOService->hydrateEntityFromDTO($user, $user_entity);

        $this->entityManager->persist($user_entity);
        $this->entityManager->flush();

        return $user_entity;
    }

}