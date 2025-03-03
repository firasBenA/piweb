<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303011533 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit ADD ventes INT DEFAULT 0 NOT NULL, CHANGE creer_le creer_le DATETIME DEFAULT NULL, CHANGE maj_le maj_le DATETIME DEFAULT NULL, CHANGE image image VARCHAR(255) DEFAULT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE produit DROP ventes, CHANGE creer_le creer_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE maj_le maj_le DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', CHANGE image image VARCHAR(255) NOT NULL');
    }
}
