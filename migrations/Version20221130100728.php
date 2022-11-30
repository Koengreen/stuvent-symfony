<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221130100728 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_events ADD event_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE user_events ADD CONSTRAINT FK_36D54C7771F7E88B FOREIGN KEY (event_id) REFERENCES event (id)');
        $this->addSql('CREATE INDEX IDX_36D54C7771F7E88B ON user_events (event_id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_events DROP FOREIGN KEY FK_36D54C7771F7E88B');
        $this->addSql('DROP INDEX IDX_36D54C7771F7E88B ON user_events');
        $this->addSql('ALTER TABLE user_events DROP event_id');
    }
}
