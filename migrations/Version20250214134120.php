<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214134120 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_bout (id INT AUTO_INCREMENT NOT NULL, categorie_id INT DEFAULT NULL, nom VARCHAR(255) NOT NULL, description LONGTEXT NOT NULL, prix NUMERIC(10, 0) NOT NULL, stock INT NOT NULL, image VARCHAR(255) NOT NULL, cree_le DATETIME NOT NULL, maj_le DATETIME NOT NULL, INDEX IDX_4682D31EBCF5E72D (categorie_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_bout ADD CONSTRAINT FK_4682D31EBCF5E72D FOREIGN KEY (categorie_id) REFERENCES categorie_art_bout (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_bout DROP FOREIGN KEY FK_4682D31EBCF5E72D');
        $this->addSql('DROP TABLE article_bout');
    }
}
