<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220610102608 extends AbstractMigration {
	
	public function getDescription(): string {
		return '';
	}
	
	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE file (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, extension VARCHAR(5) NOT NULL, mime_type VARCHAR(100) NOT NULL, purpose ENUM(\'user_avatar\') NOT NULL COMMENT \'(DC2Type:enum_file_purpose)\', private_key VARCHAR(32) NOT NULL, source_type ENUM(\'http_upload\', \'local\') NOT NULL COMMENT \'(DC2Type:enum_file_source)\', source_name VARCHAR(255) DEFAULT NULL, source_url VARCHAR(511) DEFAULT NULL, expire_date DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', parent_id INT DEFAULT NULL, position SMALLINT NOT NULL, output_name VARCHAR(255) DEFAULT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, INDEX IDX_8C9F361085564492 (create_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, _key VARCHAR(255) NOT NULL, locale VARCHAR(7) NOT NULL, primary_code VARCHAR(7) NOT NULL, region_code VARCHAR(7) NOT NULL, enabled TINYINT(1) NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, INDEX IDX_D4DB71B585564492 (create_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, create_user_id INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, activation_date DATETIME DEFAULT NULL, activation_expire_date DATETIME DEFAULT NULL, activation_key VARCHAR(32) DEFAULT NULL, recover_request_date DATETIME DEFAULT NULL, recovery_key VARCHAR(32) DEFAULT NULL, disabled TINYINT(1) NOT NULL, timezone VARCHAR(20) NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D64982F1BAF4 (language_id), INDEX IDX_8D93D64985564492 (create_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE "file" ADD CONSTRAINT "FK_8C9F361085564492" FOREIGN KEY ("create_user_id") REFERENCES "user" ("id")');
		$this->addSql('ALTER TABLE language ADD CONSTRAINT FK_D4DB71B585564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
		$this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64982F1BAF4 FOREIGN KEY (language_id) REFERENCES language (id)');
		$this->addSql('ALTER TABLE "user" ADD CONSTRAINT "FK_8D93D64985564492" FOREIGN KEY ("create_user_id") REFERENCES "user" ("id")');
	}
	
	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64982F1BAF4');
		$this->addSql('ALTER TABLE file DROP FOREIGN KEY FK_8C9F361085564492');
		$this->addSql('ALTER TABLE language DROP FOREIGN KEY FK_D4DB71B585564492');
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985564492');
		$this->addSql('DROP TABLE file');
		$this->addSql('DROP TABLE language');
		$this->addSql('DROP TABLE user');
    }
}
