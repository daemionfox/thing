<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220913020456 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE vendor (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, registrationdate DATETIME DEFAULT NULL, taxid VARCHAR(255) DEFAULT NULL, products_and_services LONGTEXT DEFAULT NULL, rating VARCHAR(255) DEFAULT NULL, is_mature TINYINT(1) NOT NULL, website VARCHAR(255) DEFAULT NULL, twitter VARCHAR(255) DEFAULT NULL, table_request_type VARCHAR(255) DEFAULT NULL, seating_requests LONGTEXT DEFAULT NULL, neighbor_requests LONGTEXT DEFAULT NULL, other_requests LONGTEXT DEFAULT NULL, createdon DATETIME NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_address (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, street1 VARCHAR(255) DEFAULT NULL, stree2 VARCHAR(255) DEFAULT NULL, city VARCHAR(255) DEFAULT NULL, state VARCHAR(10) DEFAULT NULL, postal VARCHAR(10) DEFAULT NULL, country VARCHAR(255) DEFAULT NULL, createdon DATETIME NOT NULL, UNIQUE INDEX UNIQ_133957EEF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_category (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, category VARCHAR(255) NOT NULL, is_primary TINYINT(1) NOT NULL, createdon DATETIME NOT NULL, INDEX IDX_DB5F1230F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_contact (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, first_name VARCHAR(255) NOT NULL, last_name VARCHAR(255) NOT NULL, badge_name VARCHAR(255) NOT NULL, badge_number VARCHAR(255) DEFAULT NULL, email_address VARCHAR(255) NOT NULL, createdon DATETIME NOT NULL, UNIQUE INDEX UNIQ_5215DE57F603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE vendor_image (id INT AUTO_INCREMENT NOT NULL, vendor_id INT NOT NULL, image_path VARCHAR(255) NOT NULL, createdon DATETIME NOT NULL, INDEX IDX_D37B061FF603EE73 (vendor_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE vendor_address ADD CONSTRAINT FK_133957EEF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
        $this->addSql('ALTER TABLE vendor_category ADD CONSTRAINT FK_DB5F1230F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
        $this->addSql('ALTER TABLE vendor_contact ADD CONSTRAINT FK_5215DE57F603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
        $this->addSql('ALTER TABLE vendor_image ADD CONSTRAINT FK_D37B061FF603EE73 FOREIGN KEY (vendor_id) REFERENCES vendor (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE vendor_address DROP FOREIGN KEY FK_133957EEF603EE73');
        $this->addSql('ALTER TABLE vendor_category DROP FOREIGN KEY FK_DB5F1230F603EE73');
        $this->addSql('ALTER TABLE vendor_contact DROP FOREIGN KEY FK_5215DE57F603EE73');
        $this->addSql('ALTER TABLE vendor_image DROP FOREIGN KEY FK_D37B061FF603EE73');
        $this->addSql('DROP TABLE vendor');
        $this->addSql('DROP TABLE vendor_address');
        $this->addSql('DROP TABLE vendor_category');
        $this->addSql('DROP TABLE vendor_contact');
        $this->addSql('DROP TABLE vendor_image');
    }
}
