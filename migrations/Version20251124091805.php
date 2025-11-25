<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251124091805 extends AbstractMigration {
    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, language_id INT NOT NULL, fragment_id INT NOT NULL, localized_unit_id INT NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, title VARCHAR(255) NOT NULL, slug VARCHAR(255) NOT NULL, status VARCHAR(255) NOT NULL, INDEX IDX_23A0E6685564492 (create_user_id), INDEX IDX_23A0E6682F1BAF4 (language_id), INDEX IDX_23A0E66596BD57E (fragment_id), INDEX IDX_23A0E6638810B54 (localized_unit_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6685564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6682F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66596BD57E FOREIGN KEY (fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E6638810B54 FOREIGN KEY (localized_unit_id) REFERENCES localized_unit (id)');
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6685564492');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6682F1BAF4');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66596BD57E');
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E6638810B54');
        $this->addSql('DROP TABLE article');
    }
}
