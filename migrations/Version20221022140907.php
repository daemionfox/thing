<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20221022140907 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vote_event (id INT AUTO_INCREMENT NOT NULL, created_by_id INT NOT NULL, name VARCHAR(255) NOT NULL, staff_votes INT NOT NULL, max_vendor_votes INT DEFAULT NULL, starts_on DATETIME DEFAULT NULL, ends_on DATETIME DEFAULT NULL, created_on DATETIME NOT NULL, INDEX IDX_6AC7686CB03A8386 (created_by_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vote_item (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, vote_event_id INT NOT NULL, vendor_id INT NOT NULL, votes INT NOT NULL, created_on DATETIME NOT NULL, INDEX IDX_A879D42FA76ED395 (user_id), INDEX IDX_A879D42F8FA841A2 (vote_event_id), INDEX IDX_A879D42FF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vote_event ADD CONSTRAINT FK_6AC7686CB03A8386 FOREIGN KEY (created_by_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote_item ADD CONSTRAINT FK_A879D42FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vote_item ADD CONSTRAINT FK_A879D42F8FA841A2 FOREIGN KEY (vote_event_id) REFERENCES vote_event (id)');
        $this->addSql('ALTER TABLE vote_item ADD CONSTRAINT FK_A879D42FF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vote_event DROP FOREIGN KEY FK_6AC7686CB03A8386');
        $this->addSql('ALTER TABLE vote_item DROP FOREIGN KEY FK_A879D42FA76ED395');
        $this->addSql('ALTER TABLE vote_item DROP FOREIGN KEY FK_A879D42F8FA841A2');
        $this->addSql('ALTER TABLE vote_item DROP FOREIGN KEY FK_A879D42FF603EE73');
        $this->addSql('DROP TABLE vote_event');
        $this->addSql('DROP TABLE vote_item');
    }
}
