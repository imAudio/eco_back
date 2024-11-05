<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105125355 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE friend (id INT AUTO_INCREMENT NOT NULL, sent_id INT NOT NULL, receiver_id INT NOT NULL, state VARCHAR(255) NOT NULL, INDEX IDX_55EEAC61438A69E4 (sent_id), INDEX IDX_55EEAC61CD53EDB6 (receiver_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE hand (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, card_id INT NOT NULL, party_id INT NOT NULL, INDEX IDX_2762428FA76ED395 (user_id), INDEX IDX_2762428F4ACC9A20 (card_id), INDEX IDX_2762428F213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE player (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, party_id INT NOT NULL, point INT NOT NULL, INDEX IDX_98197A65A76ED395 (user_id), INDEX IDX_98197A65213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE river (id INT AUTO_INCREMENT NOT NULL, party_id INT NOT NULL, card_id INT DEFAULT NULL, INDEX IDX_F5E3672B213C1059 (party_id), INDEX IDX_F5E3672B4ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61438A69E4 FOREIGN KEY (sent_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE friend ADD CONSTRAINT FK_55EEAC61CD53EDB6 FOREIGN KEY (receiver_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hand ADD CONSTRAINT FK_2762428FA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE hand ADD CONSTRAINT FK_2762428F4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE hand ADD CONSTRAINT FK_2762428F213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE player ADD CONSTRAINT FK_98197A65213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE river ADD CONSTRAINT FK_F5E3672B213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('ALTER TABLE river ADD CONSTRAINT FK_F5E3672B4ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY FK_55EEAC61438A69E4');
        $this->addSql('ALTER TABLE friend DROP FOREIGN KEY FK_55EEAC61CD53EDB6');
        $this->addSql('ALTER TABLE hand DROP FOREIGN KEY FK_2762428FA76ED395');
        $this->addSql('ALTER TABLE hand DROP FOREIGN KEY FK_2762428F4ACC9A20');
        $this->addSql('ALTER TABLE hand DROP FOREIGN KEY FK_2762428F213C1059');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65A76ED395');
        $this->addSql('ALTER TABLE player DROP FOREIGN KEY FK_98197A65213C1059');
        $this->addSql('ALTER TABLE river DROP FOREIGN KEY FK_F5E3672B213C1059');
        $this->addSql('ALTER TABLE river DROP FOREIGN KEY FK_F5E3672B4ACC9A20');
        $this->addSql('DROP TABLE friend');
        $this->addSql('DROP TABLE hand');
        $this->addSql('DROP TABLE player');
        $this->addSql('DROP TABLE river');
    }
}
