<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220605004511 extends AbstractMigration {
	
	public function getDescription(): string {
		return '';
	}
	
	public function up(Schema $schema): void {
		// this up() migration is auto-generated, please modify it to your needs
		$this->addSql('CREATE TABLE language (id INT AUTO_INCREMENT NOT NULL, create_user_id INT DEFAULT NULL, _key VARCHAR(255) NOT NULL, locale VARCHAR(7) NOT NULL, primary_code VARCHAR(7) NOT NULL, region_code VARCHAR(7) NOT NULL, enabled TINYINT(1) NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, INDEX IDX_D4DB71B585564492 (create_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, language_id INT NOT NULL, create_user_id INT DEFAULT NULL, version INT DEFAULT 1 NOT NULL, email VARCHAR(180) NOT NULL, roles LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', password VARCHAR(255) NOT NULL, name VARCHAR(50) NOT NULL, activation_date DATETIME DEFAULT NULL, activation_expire_date DATETIME DEFAULT NULL, activation_key VARCHAR(32) DEFAULT NULL, recover_request_date DATETIME DEFAULT NULL, recovery_key VARCHAR(32) DEFAULT NULL, disabled TINYINT(1) NOT NULL, timezone VARCHAR(20) NOT NULL, create_date DATETIME NOT NULL COMMENT \'(DC2Type:datetimetz_immutable)\', create_ip VARCHAR(60) NOT NULL, UNIQUE INDEX UNIQ_8D93D649E7927C74 (email), INDEX IDX_8D93D64982F1BAF4 (language_id), INDEX IDX_8D93D64985564492 (create_user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('CREATE TABLE "messenger_messages" ("id" BIGINT AUTO_INCREMENT NOT NULL, "body" LONGTEXT NOT NULL, "headers" LONGTEXT NOT NULL, "queue_name" VARCHAR(190) NOT NULL, "created_at" DATETIME NOT NULL, "available_at" DATETIME NOT NULL, "delivered_at" DATETIME DEFAULT NULL, "INDEX" IDX_75EA56E0FB7336F0 (queue_name), "INDEX" IDX_75EA56E0E3BD61CE (available_at), "INDEX" IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY("id")) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
		$this->addSql('ALTER TABLE "language" ADD CONSTRAINT "FK_D4DB71B585564492" FOREIGN KEY ("create_user_id") REFERENCES "user" ("id")');
		$this->addSql('ALTER TABLE "user" ADD CONSTRAINT "FK_8D93D64982F1BAF4" FOREIGN KEY ("language_id") REFERENCES "language" ("id")');
		$this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D64985564492 FOREIGN KEY (create_user_id) REFERENCES user (id)');
	}
	
	public function down(Schema $schema): void {
		// this down() migration is auto-generated, please modify it to your needs
		$this->addSql('ALTER TABLE "user" DROP FOREIGN KEY FK_8D93D64982F1BAF4');
		$this->addSql('ALTER TABLE language DROP FOREIGN KEY FK_D4DB71B585564492');
		$this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D64985564492');
		$this->addSql('DROP TABLE language');
		$this->addSql('DROP TABLE user');
		$this->addSql('DROP TABLE messenger_messages');
    }
}
