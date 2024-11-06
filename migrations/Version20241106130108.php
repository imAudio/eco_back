<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20241106130108 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE chat_party (id INT AUTO_INCREMENT NOT NULL, user_id INT NOT NULL, party_id INT NOT NULL, content VARCHAR(255) NOT NULL, INDEX IDX_88E8A37BA76ED395 (user_id), INDEX IDX_88E8A37B213C1059 (party_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE chat_party ADD CONSTRAINT FK_88E8A37BA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE chat_party ADD CONSTRAINT FK_88E8A37B213C1059 FOREIGN KEY (party_id) REFERENCES party (id)');
        $this->addSql('DROP TABLE pollution_event');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE pollution_event (id INT AUTO_INCREMENT NOT NULL, evenement VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, type_pollution VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, indice_pollution INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE chat_party DROP FOREIGN KEY FK_88E8A37BA76ED395');
        $this->addSql('ALTER TABLE chat_party DROP FOREIGN KEY FK_88E8A37B213C1059');
        $this->addSql('DROP TABLE chat_party');
    }
}
