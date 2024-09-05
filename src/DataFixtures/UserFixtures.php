<?php

declare(strict_types=1);

namespace App\DataFixtures;

use App\Entity\GraphQL\Role\BaseRole;
use App\Entity\GraphQL\Role\ExtendedRole;
use App\Entity\GraphQL\Role\FullRole;
use App\Entity\Profile;
use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Faker\Generator;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    public const DEFAULT_PASSWORD          = 'password';
    public const DEFAULT_USER_LOGIN        = 'user';
    public const DEFAULT_ADMIN_LOGIN       = 'admin';
    public const DEFAULT_SUPER_ADMIN_LOGIN = 'superadmin';

    private Generator $faker;
    private string $passwordHash;
    private static array $roles = [ExtendedRole::ROLE_ADMIN, BaseRole::ROLE_USER];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    ) {
        $this->faker    = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; ++$i) {
            $manager->persist(
                $this->getFakeUser()
            );
        }

        $user = (new User())
            ->setLogin(self::DEFAULT_USER_LOGIN)
            ->setRoles([BaseRole::ROLE_USER])
            ->setProfile($this->getFakeProfile());
        $user->setPassword($this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD));
        $manager->persist($user);

        $orgadmin = (new User())
            ->setLogin(self::DEFAULT_ADMIN_LOGIN)
            ->setRoles([ExtendedRole::ROLE_ADMIN])
            ->setProfile($this->getFakeProfile());
        $orgadmin->setPassword($this->passwordHasher->hashPassword($orgadmin, self::DEFAULT_PASSWORD));
        $manager->persist($orgadmin);

        $superadmin = (new User())
            ->setLogin(self::DEFAULT_SUPER_ADMIN_LOGIN)
            ->setRoles([FullRole::ROLE_SUPERADMIN])
            ->setProfile($this->getFakeProfile());
        $superadmin->setPassword($this->passwordHasher->hashPassword($superadmin, self::DEFAULT_PASSWORD));
        $manager->persist($superadmin);

        $manager->flush();
    }

    private function getFakeUser(): User
    {
        $user = (new User())
            ->setLogin($this->faker->userName())
            ->setRoles([self::$roles[array_rand(self::$roles)]])
            ->setProfile($this->getFakeProfile());
        if (empty($this->passwordHash)) {
            $this->passwordHash = $this->passwordHasher->hashPassword($user, self::DEFAULT_PASSWORD);
        }
        $user->setPassword($this->passwordHash);

        return $user;
    }

    private function getFakeProfile(): Profile
    {
        return (new Profile())
            ->setFirstName($this->faker->firstName())
            ->setLastName($this->faker->lastName())
            ->setEmail($this->faker->email());
    }
}
