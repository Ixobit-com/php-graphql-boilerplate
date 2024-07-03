<?php

namespace App\Service\GraphQL\User;

use App\Entity\Author;
use App\Entity\Book;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;
use Symfony\Bundle\SecurityBundle\Security;

class UserMutationService
{
    public function __construct(
        private EntityManagerInterface $manager,
        private Security $security,
    ) {}


    public function userUpdate(array $data): ?User
    {
        $user = $this->security->getUser();

        $user = $this->manager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);

        $hydrator = new DoctrineHydrator($this->manager);
        $hydrator->hydrate($data, $user);

        $this->manager->persist($user);
        $this->manager->flush();

        return $user;
    }
}