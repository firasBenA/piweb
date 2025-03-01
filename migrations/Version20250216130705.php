<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250216130705 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE panier_article_boutique (panier_id INT NOT NULL, article_boutique_id INT NOT NULL, INDEX IDX_4B04BF6FF77D927C (panier_id), INDEX IDX_4B04BF6F31E41181 (article_boutique_id), PRIMARY KEY(panier_id, article_boutique_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE panier_article_boutique ADD CONSTRAINT FK_4B04BF6FF77D927C FOREIGN KEY (panier_id) REFERENCES panier (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE panier_article_boutique ADD CONSTRAINT FK_4B04BF6F31E41181 FOREIGN KEY (article_boutique_id) REFERENCES article_boutique (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier_article_boutique DROP FOREIGN KEY FK_4B04BF6FF77D927C');
        $this->addSql('ALTER TABLE panier_article_boutique DROP FOREIGN KEY FK_4B04BF6F31E41181');
        $this->addSql('DROP TABLE panier_article_boutique');
    }
}
