<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250227085018 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE article_likes (article_id INT NOT NULL, user_id INT NOT NULL, INDEX IDX_3E7D3C6C7294869C (article_id), INDEX IDX_3E7D3C6CA76ED395 (user_id), PRIMARY KEY(article_id, user_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article_likes ADD CONSTRAINT FK_3E7D3C6C7294869C FOREIGN KEY (article_id) REFERENCES article (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE article_likes ADD CONSTRAINT FK_3E7D3C6CA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article_likes DROP FOREIGN KEY FK_3E7D3C6C7294869C');
        $this->addSql('ALTER TABLE article_likes DROP FOREIGN KEY FK_3E7D3C6CA76ED395');
        $this->addSql('DROP TABLE article_likes');
    }
}
