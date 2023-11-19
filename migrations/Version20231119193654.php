<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20231119193654 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vendor_note (id INT AUTO_INCREMENT NOT NULL, owner_id INT NOT NULL, vendor_id INT NOT NULL, createdon DATETIME NOT NULL, message LONGTEXT DEFAULT NULL, type VARCHAR(255) DEFAULT NULL, INDEX IDX_E570FFCB7E3C61F9 (owner_id), INDEX IDX_E570FFCBF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vendor_note ADD CONSTRAINT FK_E570FFCB7E3C61F9 FOREIGN KEY (owner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE vendor_note ADD CONSTRAINT FK_E570FFCBF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vendor_note DROP FOREIGN KEY FK_E570FFCB7E3C61F9');
        $this->addSql('ALTER TABLE vendor_note DROP FOREIGN KEY FK_E570FFCBF603EE73');
        $this->addSql('DROP TABLE vendor_note');
    }
}
