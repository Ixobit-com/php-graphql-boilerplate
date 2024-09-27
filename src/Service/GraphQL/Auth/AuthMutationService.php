<?php

declare(strict_types=1);

namespace App\Service\GraphQL\Auth;

use App\Entity\GraphQL\DTO\User\Input\userRegistrationInputDTO;
use App\Entity\GraphQL\Role\BaseRole;
use App\Entity\User;
use App\Service\DTO\DTOService;
use Doctrine\ORM\EntityManagerInterface;
use Overblog\GraphQLBundle\Annotation as GQL;
use Overblog\GraphQLBundle\Error\UserError;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Attribute\Autoconfigure;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Autoconfigure(public: true)]
#[GQL\Type(name: 'AuthMutation')]
#[GQL\Access("hasRole('PUBLIC_ACCESS')")]
class AuthMutationService
{
    public function __construct(
        protected readonly EntityManagerInterface $entityManager,
        protected DTOService $DTOService,
        protected LoggerInterface $logger,
        protected UserPasswordHasherInterface $passwordHasher,
        protected TranslatorInterface $translator,
    ) {
    }

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

        try {
            $this->DTOService->hydrateEntityFromDTO($user, $newUser, [
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
        } catch (\ReflectionException $e) {
            $this->logger->error($e->getMessage());
            throw new UserError('Internal error');
        }

        if (isset($user->password)) {
            $newUser->setPassword($this->passwordHasher->hashPassword($newUser, $user->password));
        }

        $newUser->setRoles([BaseRole::ROLE_USER]);

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
