-- Database Schema for Symfony Books Management System
-- MySQL 8.0

-- Author Table
CREATE TABLE `author` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `name` VARCHAR(255) NOT NULL,
  `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Book Table
CREATE TABLE `book` (
  `id` INT AUTO_INCREMENT NOT NULL,
  `author_id` INT NOT NULL,
  `title` VARCHAR(255) NOT NULL,
  `isbn` VARCHAR(50) DEFAULT NULL,
  `published_year` INT DEFAULT NULL,
  `created_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  `updated_at` DATETIME NOT NULL COMMENT '(DC2Type:datetime_immutable)',
  INDEX `IDX_CBE5A331F675F31B` (`author_id`),
  PRIMARY KEY(`id`)
) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB;

-- Foreign Key Constraint
ALTER TABLE `book` 
ADD CONSTRAINT `FK_CBE5A331F675F31B` 
FOREIGN KEY (`author_id`) REFERENCES `author` (`id`);

-- Notes:
-- - The author table contains information about book authors
-- - The book table contains information about books with a many-to-one relationship to authors
-- - Each book must have an author (NOT NULL constraint on author_id)
-- - Timestamps are automatically managed by Doctrine lifecycle callbacks

