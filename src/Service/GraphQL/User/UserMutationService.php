<?php

namespace App\Service\GraphQL\User;

use App\DTO\userCreateInputDTO;
use App\DTO\userUpdateInputDTO;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\User\UserInterface;


class UserMutationService
{
    private UserInterface|null $user;

    private DoctrineHydrator $hydrator;

    public function __construct(
        private EntityManagerInterface $manager,
        private Security $security,
        private UserPasswordHasherInterface $passwordHasher
    ) {
        $this->user = $this->security->getUser();
        $this->hydrator = new DoctrineHydrator($this->manager);
    }

    public function userCreate(userCreateInputDTO $userCreateInputDTO): User
    {
        $user = new User();

        $this->hydrator->hydrate($userCreateInputDTO->toArray(), $user);

        $user->setPassword($this->passwordHasher->hashPassword($user, $userCreateInputDTO->password));

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

    public function userUpdate(userUpdateInputDTO $userUpdateInputDTO): User
    {
        $user = $this->manager->getRepository(User::class)->find($userUpdateInputDTO->id);
        if (! $user instanceof User) {
            throw new EntityNotFoundException("User #{$userUpdateInputDTO->id} not found");
        }

        if (!empty($userUpdateInputDTO->password)) {
            $userUpdateInputDTO->password = $this->passwordHasher->hashPassword($user, $userUpdateInputDTO->password);
        }
        $this->hydrator->hydrate($userUpdateInputDTO->toArray(), $user);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }

}