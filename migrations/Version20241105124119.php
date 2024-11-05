<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105124119 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE party (id INT AUTO_INCREMENT NOT NULL, winner_id INT DEFAULT NULL, code VARCHAR(255) NOT NULL, turn INT NOT NULL, INDEX IDX_89954EE05DFCD4B8 (winner_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE play (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, card_id INT DEFAULT NULL, party_id INT NOT NULL, INDEX IDX_5E89DEBAA76ED395 (user_id), INDEX IDX_5E89DEBA4ACC9A20 (card_id), INDEX IDX_5E89DEBA213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE party ADD CONSTRAINT FK_89954EE05DFCD4B8 FOREIGN KEY (winner_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBAA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBA4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE play ADD CONSTRAINT FK_5E89DEBA213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE party DROP FOREIGN KEY FK_89954EE05DFCD4B8');
        $this->addSql('ALTER TABLE play DROP FOREIGN KEY FK_5E89DEBAA76ED395');
        $this->addSql('ALTER TABLE play DROP FOREIGN KEY FK_5E89DEBA4ACC9A20');
        $this->addSql('ALTER TABLE play DROP FOREIGN KEY FK_5E89DEBA213C1059');
        $this->addSql('DROP TABLE party');
        $this->addSql('DROP TABLE play');
    }
}
