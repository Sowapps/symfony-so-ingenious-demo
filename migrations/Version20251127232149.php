<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251127232149 extends AbstractMigration {
    public function getDescription(): string {
        return '';
    }

    public function up(Schema $schema): void {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql(<<<'SQL'
            CREATE TABLE email_message (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              subscription_id INT NOT NULL,
              to_user_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              send_date DATETIME DEFAULT NULL,
              open_date DATETIME DEFAULT NULL,
              online_expire_date DATETIME DEFAULT NULL,
              private_key VARCHAR(32) NOT NULL,
              from_user_email VARCHAR(100) NOT NULL,
              from_user_name VARCHAR(50) NOT NULL,
              to_user_email VARCHAR(100) NOT NULL,
              to_user_name VARCHAR(50) DEFAULT NULL,
              subject VARCHAR(124) NOT NULL,
              body_html LONGTEXT DEFAULT NULL,
              body_text LONGTEXT DEFAULT NULL,
              purpose ENUM(
                'user_registration', 'user_recover',
                'contact'
              ) NOT NULL COMMENT '(DC2Type:enum_email_purpose)',
              data JSON DEFAULT NULL COMMENT '(DC2Type:json)',
              template_html VARCHAR(255) DEFAULT NULL,
              INDEX IDX_B7D58B085564492 (create_user_id),
              INDEX IDX_B7D58B09A1887DC (subscription_id),
              INDEX IDX_B7D58B029F6EE60 (to_user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE email_subscription (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              disabled_message_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              private_key VARCHAR(32) DEFAULT NULL,
              purpose ENUM(
                'user_registration', 'user_recover',
                'contact'
              ) NOT NULL COMMENT '(DC2Type:enum_email_purpose)',
              email VARCHAR(100) NOT NULL,
              disabled TINYINT(1) NOT NULL,
              disabled_date DATETIME DEFAULT NULL,
              INDEX IDX_F6D5828085564492 (create_user_id),
              UNIQUE INDEX UNIQ_F6D58280C88D811C (disabled_message_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE file (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              name VARCHAR(255) NOT NULL,
              extension VARCHAR(5) NOT NULL,
              mime_type VARCHAR(100) NOT NULL,
              purpose ENUM('user_avatar') NOT NULL COMMENT '(DC2Type:enum_file_purpose)',
              private_key VARCHAR(32) NOT NULL,
              source_type ENUM('http_upload', 'local') NOT NULL COMMENT '(DC2Type:enum_file_source)',
              source_name VARCHAR(255) DEFAULT NULL,
              source_url VARCHAR(511) DEFAULT NULL,
              expire_date DATETIME DEFAULT NULL,
              parent_id INT DEFAULT NULL,
              position SMALLINT NOT NULL,
              storage ENUM('local') NOT NULL COMMENT '(DC2Type:enum_file_storage)',
              path VARCHAR(255) DEFAULT NULL,
              output_name VARCHAR(255) DEFAULT NULL,
              INDEX IDX_8C9F361085564492 (create_user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE fragment (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              language_id INT NOT NULL,
              localized_unit_id INT NOT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              name VARCHAR(255) NOT NULL,
              properties JSON NOT NULL COMMENT '(DC2Type:json)',
              snippet TINYINT(1) NOT NULL,
              template_name VARCHAR(255) DEFAULT NULL,
              purpose VARCHAR(255) DEFAULT NULL,
              dtype VARCHAR(255) NOT NULL,
              slug VARCHAR(255) DEFAULT NULL,
              status VARCHAR(255) DEFAULT NULL,
              publish_date DATETIME DEFAULT NULL,
              routing VARCHAR(255) DEFAULT NULL,
              title VARCHAR(255) DEFAULT NULL,
              excerpt VARCHAR(511) DEFAULT NULL,
              INDEX IDX_CBAD15EC85564492 (create_user_id),
              INDEX IDX_CBAD15EC82F1BAF4 (language_id),
              INDEX IDX_CBAD15EC38810B54 (localized_unit_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE fragment_link (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              parent_id INT NOT NULL,
              child_id INT NOT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              position INT DEFAULT NULL,
              name VARCHAR(255) NOT NULL,
              INDEX IDX_18C4A42F85564492 (create_user_id),
              INDEX IDX_18C4A42F727ACA70 (parent_id),
              INDEX IDX_18C4A42FDD62C21B (child_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE language (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              _key VARCHAR(255) NOT NULL,
              locale VARCHAR(7) NOT NULL,
              primary_code VARCHAR(7) NOT NULL,
              region_code VARCHAR(7) NOT NULL,
              enabled TINYINT(1) NOT NULL,
              INDEX IDX_D4DB71B585564492 (create_user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE localized_unit (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              INDEX IDX_9B5A64AA85564492 (create_user_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE route (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              language_id INT NOT NULL,
              localized_unit_id INT NOT NULL,
              fragment_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              path VARCHAR(255) NOT NULL,
              dtype VARCHAR(255) NOT NULL,
              menu_name VARCHAR(255) DEFAULT NULL,
              routing VARCHAR(255) DEFAULT NULL,
              item_purpose VARCHAR(255) DEFAULT NULL,
              item_criteria JSON DEFAULT NULL COMMENT '(DC2Type:query_criteria)',
              target_path VARCHAR(255) DEFAULT NULL,
              INDEX IDX_2C4207985564492 (create_user_id),
              INDEX IDX_2C4207982F1BAF4 (language_id),
              INDEX IDX_2C4207938810B54 (localized_unit_id),
              INDEX IDX_2C42079596BD57E (fragment_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE slot_fragment (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              fragment_unit_id INT NOT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              slot VARCHAR(255) NOT NULL,
              INDEX IDX_1AF8747F85564492 (create_user_id),
              INDEX IDX_1AF8747F562BC9CD (fragment_unit_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            CREATE TABLE user (
              id INT AUTO_INCREMENT NOT NULL,
              create_user_id INT DEFAULT NULL,
              language_id INT NOT NULL,
              avatar_id INT DEFAULT NULL,
              create_date DATETIME NOT NULL COMMENT '(DC2Type:datetimetz_immutable)',
              create_ip VARCHAR(60) NOT NULL,
              version INT DEFAULT 1 NOT NULL,
              email VARCHAR(180) NOT NULL,
              roles JSON NOT NULL COMMENT '(DC2Type:json)',
              password VARCHAR(255) NOT NULL,
              name VARCHAR(50) NOT NULL,
              activation_date DATETIME DEFAULT NULL,
              activation_expire_date DATETIME DEFAULT NULL,
              activation_key VARCHAR(32) DEFAULT NULL,
              recover_request_date DATETIME DEFAULT NULL,
              recovery_key VARCHAR(32) DEFAULT NULL,
              disabled TINYINT(1) NOT NULL,
              timezone VARCHAR(20) NOT NULL,
              UNIQUE INDEX UNIQ_8D93D649E7927C74 (email),
              INDEX IDX_8D93D64985564492 (create_user_id),
              INDEX IDX_8D93D64982F1BAF4 (language_id),
              UNIQUE INDEX UNIQ_8D93D64986383B10 (avatar_id),
              PRIMARY KEY(id)
            ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              email_message
            ADD
              CONSTRAINT FK_B7D58B085564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              email_message
            ADD
              CONSTRAINT FK_B7D58B09A1887DC FOREIGN KEY (subscription_id) REFERENCES email_subscription (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              email_message
            ADD
              CONSTRAINT FK_B7D58B029F6EE60 FOREIGN KEY (to_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              email_subscription
            ADD
              CONSTRAINT FK_F6D5828085564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              email_subscription
            ADD
              CONSTRAINT FK_F6D58280C88D811C FOREIGN KEY (disabled_message_id) REFERENCES email_message (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              file
            ADD
              CONSTRAINT FK_8C9F361085564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment
            ADD
              CONSTRAINT FK_CBAD15EC85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment
            ADD
              CONSTRAINT FK_CBAD15EC82F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment
            ADD
              CONSTRAINT FK_CBAD15EC38810B54 FOREIGN KEY (localized_unit_id) REFERENCES localized_unit (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment_link
            ADD
              CONSTRAINT FK_18C4A42F85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment_link
            ADD
              CONSTRAINT FK_18C4A42F727ACA70 FOREIGN KEY (parent_id) REFERENCES fragment (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              fragment_link
            ADD
              CONSTRAINT FK_18C4A42FDD62C21B FOREIGN KEY (child_id) REFERENCES fragment (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              language
            ADD
              CONSTRAINT FK_D4DB71B585564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              localized_unit
            ADD
              CONSTRAINT FK_9B5A64AA85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              route
            ADD
              CONSTRAINT FK_2C4207985564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              route
            ADD
              CONSTRAINT FK_2C4207982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              route
            ADD
              CONSTRAINT FK_2C4207938810B54 FOREIGN KEY (localized_unit_id) REFERENCES localized_unit (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              route
            ADD
              CONSTRAINT FK_2C42079596BD57E FOREIGN KEY (fragment_id) REFERENCES fragment (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              slot_fragment
            ADD
              CONSTRAINT FK_1AF8747F85564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              slot_fragment
            ADD
              CONSTRAINT FK_1AF8747F562BC9CD FOREIGN KEY (fragment_unit_id) REFERENCES localized_unit (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user
            ADD
              CONSTRAINT FK_8D93D64985564492 FOREIGN KEY (create_user_id) REFERENCES user (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user
            ADD
              CONSTRAINT FK_8D93D64982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)
        SQL
        );
        $this->addSql(<<<'SQL'
            ALTER TABLE
              user
            ADD
              CONSTRAINT FK_8D93D64986383B10 FOREIGN KEY (avatar_id) REFERENCES file (id)
        SQL
        );
    }

    public function down(Schema $schema): void {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY FK_B7D58B085564492');
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY FK_B7D58B09A1887DC');
        $this->addSql('ALTER TABLE email_message DROP FOREIGN KEY FK_B7D58B029F6EE60');
        $this->addSql('ALTER TABLE email_subscription DROP FOREIGN KEY FK_F6D5828085564492');
        $this->addSql('ALTER TABLE email_subscription DROP FOREIGN KEY FK_F6D58280C88D811C');
        $this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F361085564492');
        $this->addSql('ALTER TABLE fragment DROP FOREIGN KEY FK_CBAD15EC85564492');
        $this->addSql('ALTER TABLE fragment DROP FOREIGN KEY FK_CBAD15EC82F1BAF4');
        $this->addSql('ALTER TABLE fragment DROP FOREIGN KEY FK_CBAD15EC38810B54');
        $this->addSql('ALTER TABLE fragment_link DROP FOREIGN KEY FK_18C4A42F85564492');
        $this->addSql('ALTER TABLE fragment_link DROP FOREIGN KEY FK_18C4A42F727ACA70');
        $this->addSql('ALTER TABLE fragment_link DROP FOREIGN KEY FK_18C4A42FDD62C21B');
        $this->addSql('ALTER TABLE language DROP FOREIGN KEY FK_D4DB71B585564492');
        $this->addSql('ALTER TABLE localized_unit DROP FOREIGN KEY FK_9B5A64AA85564492');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C4207985564492');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C4207982F1BAF4');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C4207938810B54');
        $this->addSql('ALTER TABLE route DROP FOREIGN KEY FK_2C42079596BD57E');
        $this->addSql('ALTER TABLE slot_fragment DROP FOREIGN KEY FK_1AF8747F85564492');
        $this->addSql('ALTER TABLE slot_fragment DROP FOREIGN KEY FK_1AF8747F562BC9CD');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985564492');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64982F1BAF4');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64986383B10');
        $this->addSql('DROP TABLE email_message');
        $this->addSql('DROP TABLE email_subscription');
        $this->addSql('DROP TABLE file');
        $this->addSql('DROP TABLE fragment');
        $this->addSql('DROP TABLE fragment_link');
        $this->addSql('DROP TABLE language');
        $this->addSql('DROP TABLE localized_unit');
        $this->addSql('DROP TABLE route');
        $this->addSql('DROP TABLE slot_fragment');
        $this->addSql('DROP TABLE user');
    }
}
