<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250509110145 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE panier DROP FOREIGN KEY panier_ibfk_1');
        $this->addSql('DROP TABLE panier');
        $this->addSql('DROP TABLE personne');
        $this->addSql('DROP TABLE produit');
        $this->addSql('ALTER TABLE consultation ADD patient_id INT NOT NULL, ADD medecin_id INT NOT NULL');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A66B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_964685A66B899279 ON consultation (patient_id)');
        $this->addSql('CREATE INDEX IDX_964685A64F31A84 ON consultation (medecin_id)');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_medecin');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_patient');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_medecin');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_patient');
        $this->addSql('ALTER TABLE rendez_vous CHANGE patient_id patient_id INT NOT NULL, CHANGE medecin_id medecin_id INT NOT NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES user (id)');
        $this->addSql('DROP INDEX fk_patient ON rendez_vous');
        $this->addSql('CREATE INDEX IDX_65E8AA0A6B899279 ON rendez_vous (patient_id)');
        $this->addSql('DROP INDEX fk_medecin ON rendez_vous');
        $this->addSql('CREATE INDEX IDX_65E8AA0A4F31A84 ON rendez_vous (medecin_id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_medecin FOREIGN KEY (medecin_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_patient FOREIGN KEY (patient_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE reponse DROP rating');
        $this->addSql('ALTER TABLE user CHANGE user_type user_type VARCHAR(255) NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE panier (id INT AUTO_INCREMENT NOT NULL, userId INT NOT NULL, creeLe DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, majLe DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, INDEX userId (userId), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE personne (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(250) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, prenom VARCHAR(250) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, age INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('CREATE TABLE produit (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_general_ci`, description TEXT CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, prix DOUBLE PRECISION NOT NULL, creerLe DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, majLe DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, stock INT NOT NULL, image VARCHAR(255) CHARACTER SET utf8mb4 DEFAULT NULL COLLATE `utf8mb4_general_ci`, ventes INT DEFAULT 0, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_general_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE panier ADD CONSTRAINT panier_ibfk_1 FOREIGN KEY (userId) REFERENCES user (id)');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A66B899279');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('DROP INDEX IDX_964685A66B899279 ON consultation');
        $this->addSql('DROP INDEX IDX_964685A64F31A84 ON consultation');
        $this->addSql('ALTER TABLE consultation DROP patient_id, DROP medecin_id');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4F31A84');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4F31A84');
        $this->addSql('ALTER TABLE rendez_vous CHANGE patient_id patient_id INT DEFAULT NULL, CHANGE medecin_id medecin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_medecin FOREIGN KEY (medecin_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_patient FOREIGN KEY (patient_id) REFERENCES user (id) ON DELETE SET NULL');
        $this->addSql('DROP INDEX idx_65e8aa0a6b899279 ON rendez_vous');
        $this->addSql('CREATE INDEX FK_patient ON rendez_vous (patient_id)');
        $this->addSql('DROP INDEX idx_65e8aa0a4f31a84 ON rendez_vous');
        $this->addSql('CREATE INDEX FK_medecin ON rendez_vous (medecin_id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE reponse ADD rating INT DEFAULT 0');
        $this->addSql('ALTER TABLE user CHANGE user_type user_type VARCHAR(255) DEFAULT NULL');
    }
}
