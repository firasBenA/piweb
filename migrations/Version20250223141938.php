<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223141938 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diagnostique ADD prescription_id INT DEFAULT NULL, DROP status, DROP symptoms, CHANGE dossier_medical_id dossier_medical_id INT DEFAULT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE diagnostique ADD CONSTRAINT FK_38C9AFE993DB413D FOREIGN KEY (prescription_id) REFERENCES prescription (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_38C9AFE993DB413D ON diagnostique (prescription_id)');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1EB55F415 FOREIGN KEY (diagnostique_id) REFERENCES diagnostique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1E2902E187 FOREIGN KEY (symptomes_id) REFERENCES symptomes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dossier_medical CHANGE patient_id patient_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EB4F31A84');
        $this->addSql('DROP INDEX IDX_1ADAD7EB4F31A84 ON patient');
        $this->addSql('ALTER TABLE patient DROP medecin_id');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D94F31A84');
        $this->addSql('DROP INDEX IDX_1FBFB8D94F31A84 ON prescription');
        $this->addSql('ALTER TABLE prescription DROP medecin_id, DROP titre, CHANGE dossier_medical_id dossier_medical_id INT DEFAULT NULL, CHANGE diagnostique_id diagnostique_id INT DEFAULT NULL, CHANGE date_prescription date_prscription DATE NOT NULL');
        $this->addSql('ALTER TABLE user ADD prenom VARCHAR(255) NOT NULL, ADD specialite VARCHAR(255) DEFAULT NULL, DROP prénom, DROP mot_de_passe, DROP spécialité, CHANGE image_profil image_profil VARCHAR(255) DEFAULT NULL, CHANGE certificat certificat VARCHAR(255) DEFAULT NULL, CHANGE téléphone telephone INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE diagnostique DROP FOREIGN KEY FK_38C9AFE993DB413D');
        $this->addSql('DROP INDEX UNIQ_38C9AFE993DB413D ON diagnostique');
        $this->addSql('ALTER TABLE diagnostique ADD status INT NOT NULL, ADD symptoms JSON NOT NULL COMMENT \'(DC2Type:json)\', DROP prescription_id, CHANGE dossier_medical_id dossier_medical_id INT NOT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1EB55F415');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1E2902E187');
        $this->addSql('ALTER TABLE dossier_medical CHANGE patient_id patient_id INT NOT NULL');
        $this->addSql('ALTER TABLE patient ADD medecin_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB4F31A84 ON patient (medecin_id)');
        $this->addSql('ALTER TABLE prescription ADD medecin_id INT NOT NULL, ADD titre VARCHAR(255) NOT NULL, CHANGE dossier_medical_id dossier_medical_id INT NOT NULL, CHANGE diagnostique_id diagnostique_id INT NOT NULL, CHANGE date_prscription date_prescription DATE NOT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D94F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D94F31A84 ON prescription (medecin_id)');
        $this->addSql('ALTER TABLE user ADD mot_de_passe VARCHAR(255) NOT NULL, ADD spécialité VARCHAR(255) NOT NULL, DROP specialite, CHANGE image_profil image_profil VARCHAR(255) NOT NULL, CHANGE certificat certificat VARCHAR(255) NOT NULL, CHANGE prenom prénom VARCHAR(255) NOT NULL, CHANGE telephone téléphone INT NOT NULL');
    }
}
