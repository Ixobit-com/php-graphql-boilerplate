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
    private Generator $faker;

    private string $passwordHash;

    private static array $roles = [ExtendedRole::ROLE_ORGANIZATION_ADMIN, BaseRole::ROLE_DRIVER];

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

        $orgadmin = (new User())
            ->setLogin('admin')
            ->setRoles([ExtendedRole::ROLE_ORGANIZATION_ADMIN])
            ->setProfile($this->getFakeProfile());
        $orgadmin->setPassword($this->passwordHasher->hashPassword($orgadmin, 'password'));
        $manager->persist($orgadmin);

        $superadmin = (new User())
            ->setLogin('superadmin')
            ->setRoles([FullRole::ROLE_SUPERADMIN])
            ->setProfile($this->getFakeProfile());
        $superadmin->setPassword($this->passwordHasher->hashPassword($superadmin, 'password'));
        $manager->persist($superadmin);

        $driver = (new User())
            ->setLogin('driver')
            ->setRoles([BaseRole::ROLE_DRIVER])
            ->setProfile($this->getFakeProfile());
        $driver->setPassword($this->passwordHasher->hashPassword($driver, 'password'));
        $manager->persist($driver);

        $manager->flush();
    }

    private function getFakeUser(): User
    {
        $user = (new User())
            ->setLogin($this->faker->userName())
            ->setRoles([self::$roles[array_rand(self::$roles)]])
            ->setProfile($this->getFakeProfile());
        if (empty($this->passwordHash)) {
            $this->passwordHash = $this->passwordHasher->hashPassword($user, 'password');
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
