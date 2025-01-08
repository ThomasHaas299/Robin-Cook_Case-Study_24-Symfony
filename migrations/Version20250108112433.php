<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250108112433 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE worker ADD current_task_id VARCHAR(36) DEFAULT NULL');
        $this->addSql('ALTER TABLE worker ADD CONSTRAINT FK_9FB2BF6227BB8403 FOREIGN KEY (current_task_id) REFERENCES task (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_9FB2BF6227BB8403 ON worker (current_task_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE worker DROP FOREIGN KEY FK_9FB2BF6227BB8403');
        $this->addSql('DROP INDEX UNIQ_9FB2BF6227BB8403 ON worker');
        $this->addSql('ALTER TABLE worker DROP current_task_id');
    }
}
