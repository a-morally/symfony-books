<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230817155542 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE book (id INT AUTO_INCREMENT NOT NULL, uniqueness_hash VARCHAR(32) NOT NULL, title VARCHAR(255) NOT NULL, short_description LONGTEXT NOT NULL, long_description LONGTEXT NOT NULL, isbn VARCHAR(32) NOT NULL, page_count INT NOT NULL, thumbnail_url VARCHAR(255) NOT NULL, status VARCHAR(32) NOT NULL, published_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_CBE5A3313D5F354F (uniqueness_hash), INDEX IDX_CBE5A3317B00651C (status), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_book_category (book_id INT NOT NULL, book_category_id INT NOT NULL, INDEX IDX_7A5A379416A2B381 (book_id), INDEX IDX_7A5A379440B1D29E (book_category_id), PRIMARY KEY(book_id, book_category_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_author (id INT AUTO_INCREMENT NOT NULL, uniqueness_hash VARCHAR(32) NOT NULL, name VARCHAR(255) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_9478D3453D5F354F (uniqueness_hash), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_author_book (book_author_id INT NOT NULL, book_id INT NOT NULL, INDEX IDX_7DFD6492E4DBE55D (book_author_id), INDEX IDX_7DFD649216A2B381 (book_id), PRIMARY KEY(book_author_id, book_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE book_category (id INT AUTO_INCREMENT NOT NULL, uniqueness_hash VARCHAR(32) NOT NULL, title VARCHAR(255) NOT NULL, is_default TINYINT(1) NOT NULL, updated_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_1FB30F983D5F354F (uniqueness_hash), INDEX IDX_1FB30F98F5628617 (is_default), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE book_book_category ADD CONSTRAINT FK_7A5A379416A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_book_category ADD CONSTRAINT FK_7A5A379440B1D29E FOREIGN KEY (book_category_id) REFERENCES book_category (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_author_book ADD CONSTRAINT FK_7DFD6492E4DBE55D FOREIGN KEY (book_author_id) REFERENCES book_author (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE book_author_book ADD CONSTRAINT FK_7DFD649216A2B381 FOREIGN KEY (book_id) REFERENCES book (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE book_book_category DROP FOREIGN KEY FK_7A5A379416A2B381');
        $this->addSql('ALTER TABLE book_book_category DROP FOREIGN KEY FK_7A5A379440B1D29E');
        $this->addSql('ALTER TABLE book_author_book DROP FOREIGN KEY FK_7DFD6492E4DBE55D');
        $this->addSql('ALTER TABLE book_author_book DROP FOREIGN KEY FK_7DFD649216A2B381');
        $this->addSql('DROP TABLE book');
        $this->addSql('DROP TABLE book_book_category');
        $this->addSql('DROP TABLE book_author');
        $this->addSql('DROP TABLE book_author_book');
        $this->addSql('DROP TABLE book_category');
    }
}
