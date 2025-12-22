<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251221131400 extends AbstractMigration {
    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE fragment_file (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              fragment_id INT NOT NULL,
              file_id INT NOT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              name VARCHAR(255) NOT NULL,
              INDEX IDX_A2F70BCE85564492 (create_user_id),
              INDEX IDX_A2F70BCE596BD57E (fragment_id),
              INDEX IDX_A2F70BCE93CB796C (file_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment_file
            ADD
              CONSTRAINT FK_A2F70BCE85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment_file
            ADD
              CONSTRAINT FK_A2F70BCE596BD57E FOREIGN KEY (fragment_id) REFERENCES fragment (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment_file
            ADD
              CONSTRAINT FK_A2F70BCE93CB796C FOREIGN KEY (file_id) REFERENCES file (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              file
            CHANGE
              purpose purpose VARCHAR(255) NOT NULL,
            CHANGE
              source_type source_type VARCHAR(255) NOT NULL,
            CHANGE
              storage storage VARCHAR(255) NOT NULL
        SQL
        );
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE fragment_file DROP FOREIGN KEY FK_A2F70BCE85564492');
        $this->addSql('ALTER TABLE fragment_file DROP FOREIGN KEY FK_A2F70BCE596BD57E');
        $this->addSql('ALTER TABLE fragment_file DROP FOREIGN KEY FK_A2F70BCE93CB796C');
        $this->addSql('DROP TABLE fragment_file');
        $this->addSql(<<<'SQL'
            ALTER TABLE
              file
            CHANGE
              purpose purpose ENUM('user_avatar') NOT NULL COMMENT '(DC2Type:enum_file_purpose)',
            CHANGE
              source_type source_type ENUM('http_upload', 'local') NOT NULL COMMENT '(DC2Type:enum_file_source)',
            CHANGE
              storage storage ENUM('local') NOT NULL COMMENT '(DC2Type:enum_file_storage)'
        SQL
        );
    }
}
