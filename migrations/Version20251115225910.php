<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115225910 extends AbstractMigration {
    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fragment_link (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, parent_id INT NOT NULL, child_id INT NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, position INT DEFAULT NULL, name VARCHAR(255) NOT NULL, INDEX IDX_18C4A42F85564492 (create_user_id), INDEX IDX_18C4A42F727ACA70 (parent_id), INDEX IDX_18C4A42FDD62C21B (child_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE fragment_link ADD CONSTRAINT FK_18C4A42F85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fragment_link ADD CONSTRAINT FK_18C4A42F727ACA70 FOREIGN KEY (parent_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE fragment_link ADD CONSTRAINT FK_18C4A42FDD62C21B FOREIGN KEY (child_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE fragment_child DROP FOREIGN KEY FK_43F6C3C785564492');
        $this->addSql('ALTER TABLE fragment_child DROP FOREIGN KEY FK_43F6C3C73247C70E');
        $this->addSql('ALTER TABLE fragment_child DROP FOREIGN KEY FK_43F6C3C783EFB250');
        $this->addSql('DROP TABLE fragment_child');
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE fragment_child (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, parent_fragment_id INT NOT NULL, child_fragment_id INT NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, INDEX IDX_43F6C3C783EFB250 (child_fragment_id), INDEX IDX_43F6C3C785564492 (create_user_id), INDEX IDX_43F6C3C73247C70E (parent_fragment_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE fragment_child ADD CONSTRAINT FK_43F6C3C785564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE fragment_child ADD CONSTRAINT FK_43F6C3C73247C70E FOREIGN KEY (parent_fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE fragment_child ADD CONSTRAINT FK_43F6C3C783EFB250 FOREIGN KEY (child_fragment_id) REFERENCES fragment (id)');
        $this->addSql('ALTER TABLE fragment_link DROP FOREIGN KEY FK_18C4A42F85564492');
        $this->addSql('ALTER TABLE fragment_link DROP FOREIGN KEY FK_18C4A42F727ACA70');
        $this->addSql('ALTER TABLE fragment_link DROP FOREIGN KEY FK_18C4A42FDD62C21B');
        $this->addSql('DROP TABLE fragment_link');
    }
}
