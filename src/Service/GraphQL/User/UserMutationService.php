<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\GraphQL\DTO\Input\avatarUploadInputDTO;
use App\GraphQL\DTO\Input\userCreateInputDTO;
use App\GraphQL\DTO\Input\userUpdateInputDTO;
use App\Service\CustomSecurity\Actions;
use App\Service\CustomSecurity\Roles;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityNotFoundException;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Overblog\GraphQLBundle\Annotation as GQL;


#[Autoconfigure(public: true)]
#[GQL\Type(name: 'UserMutation')]
class UserMutationService extends BaseGraphQLService
{
    /**
     * @param userCreateInputDTO $user
     * @return User
     * @throws \ReflectionException
     */
    #[Actions(Actions::USER_CREATE)]
    #[GQL\Mutation]
    #[GQL\Arg(name: "user", type: "userCreateInputDTO")]
    public function userCreate(userCreateInputDTO $user): User
    {
        $this->checkAccess(__METHOD__);

        $newUser = new User();

        $this->DTOService->hydrateEntityFromDTO($user, $newUser);

        if (isset($user->password)) {
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $user->password));
        }
        $this->manager->persist($newUser);
        $this->manager->flush();

        return $newUser;
    }


    #[Actions(Actions::USER_UPDATE)]
    public function userUpdate(int $id, userUpdateInputDTO $userUpdateInputDTO): User
    {
        $this->checkAccess(__METHOD__);

        $user = $this->manager->getRepository(User::class)->find($id);
        if (! $user instanceof User) {
            throw new EntityNotFoundException("User #{$id} not found");
        }

        if (
            !(
                // Superadmin has full access
                in_array(Roles::ROLE_SUPERADMIN, $this->security->getUser()->getRoles()) or
                (
                    // additional check (is the current user is organization admin and updated user from his organization
                    in_array(Roles::ROLE_ORGANIZATION_ADMIN, $this->security->getUser()->getRoles()) and false
                ) or
                // user update himself
                $this->security->getUser()->getUserIdentifier() === $user->getUserIdentifier()
            )
        ) {
            throw new AccessDeniedException("User {$this->security->getUser()->getUserIdentifier()} has not access rights to update another user");
        }

        if (!empty($userUpdateInputDTO->password)) {
            $userUpdateInputDTO->password = $this->passwordHasher->hashPassword($user, $userUpdateInputDTO->password);
        }

        $this->DTOService->hydrateEntityFromDTO($userUpdateInputDTO, $user);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

}