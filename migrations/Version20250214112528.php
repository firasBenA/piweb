<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214112528 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE evenement_article (evenement_id INT NOT NULL, article_id INT NOT NULL, INDEX IDX_13F19BAFD02F13 (evenement_id), INDEX IDX_13F19BA7294869C (article_id), PRIMARY KEY(evenement_id, article_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE evenement_article ADD CONSTRAINT FK_13F19BAFD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE evenement_article ADD CONSTRAINT FK_13F19BA7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE evenement_article DROP FOREIGN KEY FK_13F19BAFD02F13');
        $this->addSql('ALTER TABLE evenement_article DROP FOREIGN KEY FK_13F19BA7294869C');
        $this->addSql('DROP TABLE evenement_article');
    }
}
