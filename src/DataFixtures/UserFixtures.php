<?php

namespace App\DataFixtures;

use App\Entity\Profile;
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

        $admin = (new User())
                ->setEmail("admin@mail.com")
        ->setRoles(["ROLE_SUPERADMIN", "ROLE_USER"])
        ->setProfile(
            (new Profile())
                ->setFirstName("Admin")
                ->setLastName("Admin"));
        $admin->setPassword($this->passwordHasher->hashPassword($admin, 'admin'));
        $manager->persist($admin);

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
