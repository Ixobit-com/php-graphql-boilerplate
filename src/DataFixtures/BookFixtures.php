<?php

namespace App\DataFixtures;

use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use App\Entity\Book;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Faker\Factory;
use Faker\Generator;
use PhpParser\Node\Expr\Array_;

class BookFixtures extends Fixture implements DependentFixtureInterface
{
    private Generator $faker;

    private static array $genres = ['Action', 'Comedy', 'Romance', 'Sci-fi', 'Programming'];

    public function __construct()
    {
        $this->faker = Factory::create();
    }

    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 100; $i++) {
            $manager->persist(
                $this->getFakeBook()
            );
        }
        $manager->flush();
    }

    private function getFakeBook(): Book
    {
        return (new Book())
            ->setTitle($this->faker->sentence())
            ->setAuthor($this->getReference(AuthorFixtures::REFERENCE))
            ->setGenre($this->faker->randomElement(self::$genres))
            ->setAverageRating($this->faker->numberBetween(1, 10))
            ->setCopiesSold($this->faker->numberBetween(10000, 10000000))
            ->setReleaseYear($this->faker->year())
            ->setSynopsis($this->faker->sentences(5, true));
    }

    public function getDependencies(): array
    {
        return [
            AuthorFixtures::class,
        ];
    }
}
