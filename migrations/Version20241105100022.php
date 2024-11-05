<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241105100022 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE card (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, value INT NOT NULL, capacity VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE combo (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, value INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE related (id INT AUTO_INCREMENT NOT NULL, id_card_id INT NOT NULL, id_combo_id INT NOT NULL, INDEX IDX_6057709094513350 (id_card_id), INDEX IDX_60577090BBBE6B76 (id_combo_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE related ADD CONSTRAINT FK_6057709094513350 FOREIGN KEY (id_card_id) REFERENCES card (id)');
        $this->addSql('ALTER TABLE related ADD CONSTRAINT FK_60577090BBBE6B76 FOREIGN KEY (id_combo_id) REFERENCES combo (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE related DROP FOREIGN KEY FK_6057709094513350');
        $this->addSql('ALTER TABLE related DROP FOREIGN KEY FK_60577090BBBE6B76');
        $this->addSql('DROP TABLE card');
        $this->addSql('DROP TABLE combo');
        $this->addSql('DROP TABLE related');
    }
}
