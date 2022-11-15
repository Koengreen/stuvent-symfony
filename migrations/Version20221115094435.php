<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221115094435 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD opleiding_id INT NOT NULL');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649844BD0B0 FOREIGN KEY (opleiding_id) REFERENCES opleiding (id)');
        $this->addSql('CREATE INDEX IDX_8D93D649844BD0B0 ON user (opleiding_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649844BD0B0');
        $this->addSql('DROP INDEX IDX_8D93D649844BD0B0 ON user');
        $this->addSql('ALTER TABLE user DROP opleiding_id');
    }
}
