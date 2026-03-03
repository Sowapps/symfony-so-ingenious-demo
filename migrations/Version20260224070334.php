<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260224070334 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE user_api_token (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              user_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              ip VARCHAR(60) NOT NULL,
              token_hash VARCHAR(255) NOT NULL,
              expire_date DATETIME DEFAULT NULL,
              last_use_date DATETIME DEFAULT NULL,
              UNIQUE INDEX UNIQ_7B42780FB3BC57DA (token_hash),
              INDEX IDX_7B42780F85564492 (create_user_id),
              INDEX IDX_7B42780FA76ED395 (user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_api_token
            ADD
              CONSTRAINT FK_7B42780F85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL);
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user_api_token
            ADD
              CONSTRAINT FK_7B42780FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)
        SQL);
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user_api_token DROP FOREIGN KEY FK_7B42780F85564492');
        $this->addSql('ALTER TABLE user_api_token DROP FOREIGN KEY FK_7B42780FA76ED395');
        $this->addSql('DROP TABLE user_api_token');
    }
}
