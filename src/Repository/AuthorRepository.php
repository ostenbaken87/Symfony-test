<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function getBookCountForAuthor(int $authorId): int
    {
        return (int) $this->createQueryBuilder('a')
            ->select('COUNT(b.id)')
            ->leftJoin('a.books', 'b')
            ->where('a.id = :authorId')
            ->setParameter('authorId', $authorId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function findAllWithBookCountQuery(): Query
    {
        return $this->createQueryBuilder('a')
            ->select('a', 'COUNT(b.id) as HIDDEN bookCount')
            ->leftJoin('a.books', 'b')
            ->groupBy('a.id')
            ->orderBy('a.id', 'ASC')
            ->getQuery();
    }

    public function getAuthorsWithBookCounts(array $authors): array
    {
        $result = [];
        foreach ($authors as $author) {
            $result[] = [
                'author' => $author,
                'bookCount' => $this->getBookCountForAuthor($author->getId())
            ];
        }
        return $result;
    }
}
