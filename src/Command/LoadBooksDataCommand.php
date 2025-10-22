<?php

namespace App\Command;

use App\Entity\Author;
use App\Entity\Book;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:load-books-data',
    description: 'Заполнение БД тестовыми данными для сущностей Author and Book',
)]
class LoadBooksDataCommand extends Command
{
    private const BATCH_SIZE = 1000;
    private const BOOKS_PER_AUTHOR = 100000;

    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $io->title('Loading Books Data');

        ini_set('memory_limit', '512M');
        
        $connection = $this->entityManager->getConnection();
        $config = $connection->getConfiguration();
        $config->setSQLLogger(null);

        $authors = $this->createAuthors($io);
        
        foreach ($authors as $index => $author) {
            $io->section(sprintf('Loading books for author: %s', $author->getName()));
            $this->createBooksForAuthor($author, $io);
        }

        $io->success('Data loading completed!');

        return Command::SUCCESS;
    }

    private function createAuthors(SymfonyStyle $io): array
    {
        $io->section('Creating authors...');
        
        $authorNames = [
            'Лев Толстой',
            'Федор Достоевский',
            'Антон Чехов',
        ];

        $authors = [];
        foreach ($authorNames as $name) {
            $author = new Author();
            $author->setName($name);
            
            $this->entityManager->persist($author);
            $authors[] = $author;
            
            $io->writeln(sprintf('Created author: %s', $name));
        }
        
        $this->entityManager->flush();
        $io->success(sprintf('%d authors created', count($authors)));

        return $authors;
    }

    private function createBooksForAuthor(Author $author, SymfonyStyle $io): void
    {
        $io->progressStart(self::BOOKS_PER_AUTHOR);

        $connection = $this->entityManager->getConnection();
        $now = (new \DateTimeImmutable())->format('Y-m-d H:i:s');
        
        $values = [];
        for ($i = 1; $i <= self::BOOKS_PER_AUTHOR; $i++) {
            $title = $connection->quote(sprintf('Book %d by %s', $i, $author->getName()));
            $isbn = $connection->quote($this->generateIsbn());
            $year = rand(1900, 2025);
            
            $values[] = sprintf(
                "(%s, %d, %s, %d, '%s', '%s')",
                $title,
                $author->getId(),
                $isbn,
                $year,
                $now,
                $now
            );

            if ($i % self::BATCH_SIZE === 0) {
                $sql = sprintf(
                    'INSERT INTO book (title, author_id, isbn, published_year, created_at, updated_at) VALUES %s',
                    implode(',', $values)
                );
                $connection->executeStatement($sql);
                $values = [];
                
                $io->progressAdvance(self::BATCH_SIZE);
            }
        }

        if (!empty($values)) {
            $sql = sprintf(
                'INSERT INTO book (title, author_id, isbn, published_year, created_at, updated_at) VALUES %s',
                implode(',', $values)
            );
            $connection->executeStatement($sql);
        }
        
        $io->progressFinish();
        $io->success(sprintf('%d books created for %s', self::BOOKS_PER_AUTHOR, $author->getName()));
    }

    private function generateIsbn(): string
    {
        return sprintf(
            '978-%d-%d-%d-%d',
            rand(0, 9),
            rand(1000, 9999),
            rand(1000, 9999),
            rand(0, 9)
        );
    }
}

