<?php

declare(strict_types=1);

namespace App\Service\GraphQL\User;

use App\Entity\GraphQL\DTO\User\Input\userUpdateInputDTO;
use App\Entity\GraphQL\Role\ExtendedRole;
use App\Entity\GraphQL\Role\FullRole;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Service\DTO\DTOService;
use App\Service\GraphQL\BaseGraphQLService;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Mapping\MappingException;
use Overblog\GraphQLBundle\Annotation as GQL;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Core\Role\RoleHierarchyInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'UserMutation')]
class UserMutationService extends BaseGraphQLService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected readonly Security $security,
        protected LoggerInterface $logger,
        private readonly RoleHierarchyInterface $roleHierarchy,
        private readonly UserPasswordHasherInterface $passwordHasher,
        private readonly DTOService $DTOService,
    ) {
        parent::__construct();
    }

    /**
     * Update basic user info.
     *  - login
     *  - password
     *  - profile info.
     *
     * @throws \ReflectionException
     * @throws MappingException
     */
    #[GQL\Field(type: 'User')]
    #[GQL\Access("isGranted('USER_UPDATE')")]
    #[GQL\Arg(name: 'login', type: 'String!')]
    #[GQL\Arg(name: 'user', type: 'userUpdateInputDTO')]
    public function userUpdate(string $login, userUpdateInputDTO $user): User
    {
        $user_entity = $this->entityManager->getRepository(User::class)->findOneBy([
            'login' => $login,
        ]);
        if (!$user_entity instanceof User) {
            $this->logger->error(
                sprintf(
                    "userUpdate: User '%s' not found",
                    $login
                )
            );
            throw new EntityNotFoundException('User not found');
        }

        $roles = $this->roleHierarchy->getReachableRoleNames($this->security->getUser()->getRoles());
        if (
            !(
                // Superadmin has full access
                in_array(FullRole::ROLE_SUPERADMIN, $roles)
                // admin with USER_UPDATE permissions
                or in_array(ExtendedRole::ROLE_ADMIN, $roles)
                // user update himself
                or $this->security->getUser()->getUserIdentifier() === $user_entity->getUserIdentifier()
            )
        ) {
            $this->logger->error(
                sprintf(
                    "User '%s' has not access rights to update user '%s'",
                    $this->security->getUser()->getUserIdentifier(),
                    $login
                )
            );
            throw new AccessDeniedException(sprintf("User '%s' has not access rights to update user %s", $this->security->getUser()->getUserIdentifier(), $login));
        }

        if (!empty($user->password)) { // hash new password
            $user->password = $this->passwordHasher->hashPassword($user_entity, $user->password);
        }

        if (!empty($user->login)) { // try to change user identifier: remove old refresh token. User must authorize again.
            $old_user_identifier = $user_entity->getUserIdentifier();
            $refresh_token       = $this->entityManager->getRepository(RefreshToken::class)->findOneBy(['username' => $old_user_identifier]);
            $this->entityManager->remove($refresh_token);
            $this->logger->info(
                sprintf(
                    "User try to change username from '%s' to '%s'; Remove old refresh token.",
                    $old_user_identifier,
                    $user->login
                )
            );
        }

        $this->DTOService->hydrateEntityFromDTO($user, $user_entity, [
            'login'    => ['property' => 'Login'],
            'password' => ['property' => 'Password'],
            'profile'  => ['property' => 'Profile',
                'map'                 => [
                    'first_name' => ['property' => 'FirstName'],
                    'last_name'  => ['property' => 'LastName'],
                    'email'      => ['property' => 'Email'],
                ],
            ],
        ]);

        $this->entityManager->persist($user_entity);
        $this->entityManager->flush();

        return $user_entity;
    }
}
