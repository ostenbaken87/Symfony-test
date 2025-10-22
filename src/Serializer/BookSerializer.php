<?php

namespace App\Serializer;

use App\Entity\Book;

class BookSerializer
{
    public function toArray(Book $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => [
                'id' => $book->getAuthor()->getId(),
                'name' => $book->getAuthor()->getName(),
            ],
            'isbn' => $book->getIsbn(),
            'published_year' => $book->getPublishedYear(),
            'created_at' => $book->getCreatedAt()->format('Y-m-d H:i:s'),
            'updated_at' => $book->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    public function toArrayShort(Book $book): array
    {
        return [
            'id' => $book->getId(),
            'title' => $book->getTitle(),
            'author' => [
                'id' => $book->getAuthor()->getId(),
                'name' => $book->getAuthor()->getName(),
            ],
            'isbn' => $book->getIsbn(),
            'published_year' => $book->getPublishedYear(),
            'updated_at' => $book->getUpdatedAt()->format('Y-m-d H:i:s'),
        ];
    }

    public function toArrayCollection(array $books): array
    {
        return array_map(fn(Book $book) => $this->toArray($book), $books);
    }
}

