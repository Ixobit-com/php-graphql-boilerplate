<?php

namespace App\Service\GraphQL\User;

use App\Entity\User;
use App\GraphQL\DTO\avatarUploadInputDTO;
use App\GraphQL\DTO\userCreateInputDTO;
use App\GraphQL\DTO\userUpdateInputDTO;
use App\Service\CustomSecurity\Actions;
use App\Service\CustomSecurity\Roles;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityNotFoundException;
use phpDocumentor\Reflection\Types\Integer;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Overblog\GraphQLBundle\Annotation as GQL;

class UserMutationService extends BaseGraphQLService
{
    #[Actions(Actions::CREATE_USER)]
    public function userCreate(userCreateInputDTO $userCreateInputDTO): User
    {
        $this->checkAccess(__METHOD__);

        $user = new User();

        $this->DTOService->hydrateEntityFromDTO($userCreateInputDTO, $user);

        $user->setPassword($this->passwordHasher->hashPassword($user, $userCreateInputDTO->password));

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }


    #[Actions(Actions::UPDATE_USER)]
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

    /**
     * @deprecated - use REST way for file upload
     *
     * @param avatarUploadInputDTO $avatarUploadInputDTO
     * @param Request $request
     * @return string
     */
    public function userAvatarUpload(avatarUploadInputDTO $avatarUploadInputDTO, Request $request): string
    {

        $file = $request->files->get($avatarUploadInputDTO->file_name);
        if ($file instanceof File) {
            return $file->getFilename();
        }
        return 'Ok';
    }

}