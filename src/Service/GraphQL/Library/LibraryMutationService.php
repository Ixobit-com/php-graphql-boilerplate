<?php

namespace App\Service\GraphQL\Library;

use App\Entity\Author;
use App\Entity\Book;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use GraphQL\Error\Error;
use Doctrine\Laminas\Hydrator\DoctrineObject as DoctrineHydrator;

class LibraryMutationService
{
    public function __construct(
        private EntityManagerInterface $manager
    ) {}

    public function createAuthor(array $authorDetails): Author
    {
        $author = (new Author())
            ->setName($authorDetails['name'])
            ->setDateOfBirth(DateTime::createFromFormat('Y-m-d', $authorDetails['dateOfBirth']))
            ->setBio($authorDetails['bio']);

        $this->manager->persist($author);
        $this->manager->flush();

        return $author;
    }

    public function updateBook(int $bookId, array $newDetails): Book
    {
        $em = $this->manager->getRepository(Book::class);
        /** @var Book $book */
        $book = $em->find($bookId);

        if (is_null($book)) {
            throw new Error("Could not find book for specified ID");
        }

        $hydrator = new DoctrineHydrator($this->manager);
        $hydrator->hydrate($newDetails, $book);

        $this->manager->persist($book);
        $this->manager->flush();

        return $book;
    }
}