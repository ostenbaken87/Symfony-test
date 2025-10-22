<?php

namespace App\Service;

use App\DTO\UpdateBookRequest;
use App\Entity\Book;
use App\Repository\AuthorRepository;
use Doctrine\ORM\EntityManagerInterface;

class BookService
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private AuthorRepository $authorRepository
    ) {
    }

    public function updateBook(Book $book, UpdateBookRequest $dto): array
    {
        $errors = [];

        if ($dto->hasTitle()) {
            if (empty($dto->title)) {
                $errors[] = 'Field "title" cannot be empty';
            } else {
                $book->setTitle($dto->title);
            }
        }

        if ($dto->hasAuthorId()) {
            $author = $this->authorRepository->find($dto->authorId);
            if (!$author) {
                $errors[] = 'Author not found';
            } else {
                $book->setAuthor($author);
            }
        }

        if ($dto->hasIsbn()) {
            $book->setIsbn($dto->isbn ?: null);
        }

        if ($dto->hasPublishedYear()) {
            $book->setPublishedYear($dto->publishedYear);
        }

        if (!empty($errors)) {
            return ['success' => false, 'errors' => $errors];
        }

        $this->entityManager->flush();

        return ['success' => true];
    }
}

