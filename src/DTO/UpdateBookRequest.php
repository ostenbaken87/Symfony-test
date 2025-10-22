<?php

namespace App\DTO;

use Symfony\Component\Validator\Constraints as Assert;

class UpdateBookRequest
{
    #[Assert\NotBlank(message: 'Field "id" is required')]
    #[Assert\Positive(message: 'Field "id" must be positive')]
    public ?int $id = null;

    #[Assert\NotBlank(message: 'Field "title" cannot be empty', groups: ['title'])]
    #[Assert\Length(max: 255, maxMessage: 'Field "title" cannot be longer than {{ limit }} characters')]
    public ?string $title = null;

    #[Assert\Positive(message: 'Field "author_id" must be positive', groups: ['author'])]
    public ?int $authorId = null;

    #[Assert\Length(max: 50, maxMessage: 'Field "isbn" cannot be longer than {{ limit }} characters')]
    public ?string $isbn = null;

    #[Assert\Range(
        min: 1000,
        max: 2025,
        notInRangeMessage: 'Field "published_year" must be between {{ min }} and {{ max }}'
    )]
    public ?int $publishedYear = null;

    private array $providedFields = [];

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = $data['id'] ?? null;

        if (array_key_exists('title', $data)) {
            $dto->title = $data['title'];
            $dto->providedFields[] = 'title';
        }

        if (array_key_exists('author_id', $data)) {
            $dto->authorId = $data['author_id'];
            $dto->providedFields[] = 'authorId';
        }

        if (array_key_exists('isbn', $data)) {
            $dto->isbn = $data['isbn'];
            $dto->providedFields[] = 'isbn';
        }

        if (array_key_exists('published_year', $data)) {
            $dto->publishedYear = $data['published_year'] ? (int)$data['published_year'] : null;
            $dto->providedFields[] = 'publishedYear';
        }

        return $dto;
    }

    public function hasTitle(): bool
    {
        return in_array('title', $this->providedFields, true);
    }

    public function hasAuthorId(): bool
    {
        return in_array('authorId', $this->providedFields, true);
    }

    public function hasIsbn(): bool
    {
        return in_array('isbn', $this->providedFields, true);
    }

    public function hasPublishedYear(): bool
    {
        return in_array('publishedYear', $this->providedFields, true);
    }
}

