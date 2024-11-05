<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105103237 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE combo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE related (id INT AUTO_INCREMENT NOT NULL, combo_id INT DEFAULT NULL, card_id INT NOT NULL, INDEX IDX_60577090EB6587E3 (combo_id), INDEX IDX_605770904ACC9A20 (card_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE related ADD CONSTRAINT FK_60577090EB6587E3 FOREIGN KEY (combo_id) REFERENCES combo (id)');
        $this->addSql('ALTER TABLE related ADD CONSTRAINT FK_605770904ACC9A20 FOREIGN KEY (card_id) REFERENCES card (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE related DROP FOREIGN KEY FK_60577090EB6587E3');
        $this->addSql('ALTER TABLE related DROP FOREIGN KEY FK_605770904ACC9A20');
        $this->addSql('DROP TABLE combo');
        $this->addSql('DROP TABLE related');
    }
}
