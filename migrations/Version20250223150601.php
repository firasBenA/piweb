<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223150601 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD prenom VARCHAR(255) NOT NULL, ADD specialite VARCHAR(255) DEFAULT NULL, DROP prénom, DROP mot_de_passe, DROP spécialité, CHANGE image_profil image_profil VARCHAR(255) DEFAULT NULL, CHANGE certificat certificat VARCHAR(255) DEFAULT NULL, CHANGE téléphone telephone INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE user ADD mot_de_passe VARCHAR(255) NOT NULL, ADD spécialité VARCHAR(255) NOT NULL, DROP specialite, CHANGE image_profil image_profil VARCHAR(255) NOT NULL, CHANGE certificat certificat VARCHAR(255) NOT NULL, CHANGE prenom prénom VARCHAR(255) NOT NULL, CHANGE telephone téléphone INT NOT NULL');
    }
}
