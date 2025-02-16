<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250214191136 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1EB55F415');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1E2902E187');
        $this->addSql('DROP TABLE diagnostique_symptomes');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diagnostique_symptomes (diagnostique_id INT NOT NULL, symptomes_id INT NOT NULL, INDEX IDX_C0F67A1EB55F415 (diagnostique_id), INDEX IDX_C0F67A1E2902E187 (symptomes_id), PRIMARY KEY(diagnostique_id, symptomes_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1EB55F415 FOREIGN KEY (diagnostique_id) REFERENCES diagnostique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1E2902E187 FOREIGN KEY (symptomes_id) REFERENCES symptomes (id) ON DELETE CASCADE');
    }
}
