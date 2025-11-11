<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251111101157 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fragment (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, language_id INT NOT NULL, snippet_fragment_id INT DEFAULT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, properties JSON NOT NULL COMMENT \'(DC2Type:json)\', html LONGTEXT DEFAULT NULL, snippet TINYINT(1) NOT NULL, template_name VARCHAR(255) DEFAULT NULL, INDEX IDX_CBAD15EC85564492 (create_user_id), INDEX IDX_CBAD15EC82F1BAF4 (language_id), INDEX IDX_CBAD15EC3A2FD8F6 (snippet_fragment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE fragment_child (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, parent_fragment_id INT NOT NULL, child_fragment_id INT NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_43F6C3C785564492 (create_user_id), INDEX IDX_43F6C3C73247C70E (parent_fragment_id), INDEX IDX_43F6C3C783EFB250 (child_fragment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE localized_unit (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, INDEX IDX_9B5A64AA85564492 (create_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE page (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, language_id INT NOT NULL, fragment_id INT NOT NULL, localized_unit_id INT NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, title VARCHAR(255) NOT NULL, path VARCHAR(255) NOT NULL, INDEX IDX_140AB62085564492 (create_user_id), INDEX IDX_140AB62082F1BAF4 (language_id), INDEX IDX_140AB620596BD57E (fragment_id), INDEX IDX_140AB62038810B54 (localized_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fragment ADD CONSTRAINT FK_CBAD15EC85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fragment ADD CONSTRAINT FK_CBAD15EC82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE fragment ADD CONSTRAINT FK_CBAD15EC3A2FD8F6 FOREIGN KEY (snippet_fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE fragment_child ADD CONSTRAINT FK_43F6C3C785564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fragment_child ADD CONSTRAINT FK_43F6C3C73247C70E FOREIGN KEY (parent_fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE fragment_child ADD CONSTRAINT FK_43F6C3C783EFB250 FOREIGN KEY (child_fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE localized_unit ADD CONSTRAINT FK_9B5A64AA85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62085564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62082F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB620596BD57E FOREIGN KEY (fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE page ADD CONSTRAINT FK_140AB62038810B54 FOREIGN KEY (localized_unit_id) REFERENCES localized_unit (id)');
        $this->addSql('ALTER TABLE email_message CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fragment DROP FOREIGN KEY FK_CBAD15EC85564492');
        $this->addSql('ALTER TABLE fragment DROP FOREIGN KEY FK_CBAD15EC82F1BAF4');
        $this->addSql('ALTER TABLE fragment DROP FOREIGN KEY FK_CBAD15EC3A2FD8F6');
        $this->addSql('ALTER TABLE fragment_child DROP FOREIGN KEY FK_43F6C3C785564492');
        $this->addSql('ALTER TABLE fragment_child DROP FOREIGN KEY FK_43F6C3C73247C70E');
        $this->addSql('ALTER TABLE fragment_child DROP FOREIGN KEY FK_43F6C3C783EFB250');
        $this->addSql('ALTER TABLE localized_unit DROP FOREIGN KEY FK_9B5A64AA85564492');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62085564492');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62082F1BAF4');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB620596BD57E');
        $this->addSql('ALTER TABLE page DROP FOREIGN KEY FK_140AB62038810B54');
        $this->addSql('DROP TABLE fragment');
        $this->addSql('DROP TABLE fragment_child');
        $this->addSql('DROP TABLE localized_unit');
        $this->addSql('DROP TABLE page');
        $this->addSql('ALTER TABLE email_message CHANGE data data JSON DEFAULT NULL COMMENT \'(DC2Type:json)\'');
        $this->addSql('ALTER TABLE user CHANGE roles roles JSON NOT NULL COMMENT \'(DC2Type:json)\'');
    }
}
