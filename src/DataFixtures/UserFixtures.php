<?php

namespace App\DataFixtures;

use App\Entity\Profile;
use App\Service\CustomSecurity\Roles;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\User;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Faker\Generator;
use PhpParser\Node\Expr\Array_;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixtures extends Fixture
{
    private Generator $faker;

    private string $passwordHash;

    private static array $roles = ['ROLE_SUPERADMIN', 'ROLE_ORGANIZATION_ADMIN', 'ROLE_DRIVER'];

    public function __construct(
        private UserPasswordHasherInterface $passwordHasher
    )
    {
        $this->faker    = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++) {
            $manager->persist(
                $this->getFakeUser()
            );
        }

        $orgadmin = (new User())
                ->setEmail("admin@example.com")
        ->setRoles([Roles::ROLE_ORGANIZATION_ADMIN, Roles::ROLE_USER])
        ->setProfile(
            (new Profile())
                ->setFirstName("Admin")
                ->setLastName("Admin"));
        $orgadmin->setPassword($this->passwordHasher->hashPassword($orgadmin, 'password'));
        $manager->persist($orgadmin);

        $superadmin = (new User())
            ->setEmail("superadmin@example.com")
            ->setRoles([Roles::ROLE_SUPERADMIN, Roles::ROLE_USER])
            ->setProfile(
                (new Profile())
                    ->setFirstName("Super")
                    ->setLastName("Super"));
        $superadmin->setPassword($this->passwordHasher->hashPassword($superadmin, 'password'));
        $manager->persist($superadmin);

        $manager->flush();
    }

    private function getFakeUser(): User
    {
        $user = (new User())
            ->setEmail($this->faker->email())
            ->setRoles([self::$roles[array_rand(self::$roles)], "ROLE_USER"])
            ->setProfile(
                (new Profile())
                    ->setFirstName($this->faker->firstName())
                    ->setLastName($this->faker->lastName()));
        if (empty($this->passwordHash)) {
            $this->passwordHash = $this->passwordHasher->hashPassword($user, 'password');
        }
        $user->setPassword($this->passwordHash);
        return $user;
    }

}
