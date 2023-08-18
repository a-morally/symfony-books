<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230818022003 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CBE5A3317B00651C ON book');
        $this->addSql('ALTER TABLE book ADD thumbnail_filename VARCHAR(255) NOT NULL AFTER page_count, CHANGE status publishment_status VARCHAR(32) NOT NULL');
        $this->addSql('CREATE INDEX IDX_CBE5A33183C2CB7A ON book (publishment_status)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP INDEX IDX_CBE5A33183C2CB7A ON book');
        $this->addSql('ALTER TABLE book DROP thumbnail_filename, CHANGE publishment_status status VARCHAR(32) NOT NULL');
        $this->addSql('CREATE INDEX IDX_CBE5A3317B00651C ON book (status)');
    }
}
