<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\GraphQL\DTO\Input\userUpdateInputDTO;
use App\GraphQL\DTO\Role\BaseRole;
use App\GraphQL\DTO\Role\ExtendedRole;
use App\GraphQL\DTO\Role\FullRole;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityNotFoundException;
use Overblog\GraphQLBundle\Annotation as GQL;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;


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
            $this->logger->error(
                sprintf(
                    "userUpdate: User #%s not found",
                    $id
                )
            );
            throw new EntityNotFoundException("User not found");
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($this->security->getUser()->getRoles());
        if (
            !(
                // Superadmin has full access
                in_array(FullRole::ROLE_SUPERADMIN, $roles) or
                (
                    // additional check (is the current user is organization admin and updated user from his organization
                    in_array(ExtendedRole::ROLE_ORGANIZATION_ADMIN, $roles) and true
                ) or
                // user update himself
                $this->security->getUser()->getUserIdentifier() === $user_entity->getUserIdentifier()
            )
        ) {
            $this->logger->error(
                sprintf(
                    "User '%s' has not access rights to update user #%i",
                    $this->security->getUser()->getUserIdentifier(),
                    $id
                )
            );
            throw new AccessDeniedException(
                sprintf(
                    "User '%s' has not access rights to update user #%i",
                    $this->security->getUser()->getUserIdentifier(),
                    $id
                )
            );
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