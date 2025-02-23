<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250223160346 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE diagnostique_symptomes (diagnostique_id INT NOT NULL, symptomes_id INT NOT NULL, INDEX IDX_C0F67A1EB55F415 (diagnostique_id), INDEX IDX_C0F67A1E2902E187 (symptomes_id), PRIMARY KEY(diagnostique_id, symptomes_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE symptomes (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_apparition DATE NOT NULL, zones_corps VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1EB55F415 FOREIGN KEY (diagnostique_id) REFERENCES diagnostique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1E2902E187 FOREIGN KEY (symptomes_id) REFERENCES symptomes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE reponse DROP FOREIGN KEY FK_5FB6DEC72D6BA2D92D6BA2D9');
        $this->addSql('DROP TABLE reponse');
        $this->addSql('ALTER TABLE article DROP prix_article');
        $this->addSql('ALTER TABLE consultation CHANGE patient_id patient_id INT NOT NULL');
        $this->addSql('ALTER TABLE diagnostique DROP zone_corps, DROP date_symptomes, DROP status, DROP selected_symptoms, CHANGE dossier_medical_id dossier_medical_id INT DEFAULT NULL, CHANGE nom nom VARCHAR(255) NOT NULL, CHANGE description description VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE62A76ED395');
        $this->addSql('DROP INDEX IDX_3581EE62A76ED395 ON dossier_medical');
        $this->addSql('ALTER TABLE dossier_medical ADD patient_id INT DEFAULT NULL, DROP user_id');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE626B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_3581EE626B899279 ON dossier_medical (patient_id)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E4F31A84');
        $this->addSql('DROP INDEX IDX_B26681E4F31A84 ON evenement');
        $this->addSql('ALTER TABLE evenement CHANGE medecin_id user_id INT NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681EA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_B26681EA76ED395 ON evenement (user_id)');
        $this->addSql('ALTER TABLE medecin DROP FOREIGN KEY FK_1BDA53C6A76ED395');
        $this->addSql('DROP INDEX UNIQ_1BDA53C6A76ED395 ON medecin');
        $this->addSql('ALTER TABLE medecin DROP user_id');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EB4F31A84');
        $this->addSql('ALTER TABLE patient DROP FOREIGN KEY FK_1ADAD7EBA76ED395');
        $this->addSql('DROP INDEX UNIQ_1ADAD7EBA76ED395 ON patient');
        $this->addSql('DROP INDEX IDX_1ADAD7EB4F31A84 ON patient');
        $this->addSql('ALTER TABLE patient DROP medecin_id, DROP user_id, CHANGE telephone telephone INT NOT NULL');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D94F31A84');
        $this->addSql('DROP INDEX IDX_1FBFB8D94F31A84 ON prescription');
        $this->addSql('ALTER TABLE prescription ADD date_prscription DATE NOT NULL, DROP medecin_id, DROP titre, DROP date_prescription, CHANGE dossier_medical_id dossier_medical_id INT DEFAULT NULL, CHANGE diagnostique_id diagnostique_id INT DEFAULT NULL, CHANGE contenue contenue VARCHAR(255) NOT NULL');
        $this->addSql('ALTER TABLE reclamation ADD date_fin DATE NOT NULL, CHANGE patient_id patient_id INT NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE reponse (id INT AUTO_INCREMENT NOT NULL, reclamation_id INT NOT NULL, contenu VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`, date_de_reponse DATETIME NOT NULL, INDEX IDX_5FB6DEC72D6BA2D9 (reclamation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB COMMENT = \'\' ');
        $this->addSql('ALTER TABLE reponse ADD CONSTRAINT FK_5FB6DEC72D6BA2D92D6BA2D9 FOREIGN KEY (reclamation_id) REFERENCES reclamation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1EB55F415');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1E2902E187');
        $this->addSql('DROP TABLE diagnostique_symptomes');
        $this->addSql('DROP TABLE symptomes');
        $this->addSql('ALTER TABLE article ADD prix_article INT NOT NULL');
        $this->addSql('ALTER TABLE consultation CHANGE patient_id patient_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE diagnostique ADD zone_corps VARCHAR(255) NOT NULL, ADD date_symptomes DATE DEFAULT NULL, ADD status INT NOT NULL, ADD selected_symptoms VARCHAR(255) DEFAULT NULL, CHANGE dossier_medical_id dossier_medical_id INT NOT NULL, CHANGE nom nom VARCHAR(255) DEFAULT NULL, CHANGE description description VARCHAR(255) DEFAULT NULL');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE626B899279');
        $this->addSql('DROP INDEX UNIQ_3581EE626B899279 ON dossier_medical');
        $this->addSql('ALTER TABLE dossier_medical ADD user_id INT NOT NULL, DROP patient_id');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE62A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE INDEX IDX_3581EE62A76ED395 ON dossier_medical (user_id)');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681EA76ED395');
        $this->addSql('DROP INDEX IDX_B26681EA76ED395 ON evenement');
        $this->addSql('ALTER TABLE evenement CHANGE user_id medecin_id INT NOT NULL');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_B26681E4F31A84 ON evenement (medecin_id)');
        $this->addSql('ALTER TABLE medecin ADD user_id INT NOT NULL');
        $this->addSql('ALTER TABLE medecin ADD CONSTRAINT FK_1BDA53C6A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1BDA53C6A76ED395 ON medecin (user_id)');
        $this->addSql('ALTER TABLE patient ADD medecin_id INT NOT NULL, ADD user_id INT NOT NULL, CHANGE telephone telephone INT DEFAULT NULL');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EB4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE patient ADD CONSTRAINT FK_1ADAD7EBA76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('CREATE UNIQUE INDEX UNIQ_1ADAD7EBA76ED395 ON patient (user_id)');
        $this->addSql('CREATE INDEX IDX_1ADAD7EB4F31A84 ON patient (medecin_id)');
        $this->addSql('ALTER TABLE prescription ADD medecin_id INT NOT NULL, ADD titre VARCHAR(255) NOT NULL, ADD date_prescription DATETIME NOT NULL, DROP date_prscription, CHANGE dossier_medical_id dossier_medical_id INT NOT NULL, CHANGE diagnostique_id diagnostique_id INT NOT NULL, CHANGE contenue contenue LONGTEXT NOT NULL');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D94F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('CREATE INDEX IDX_1FBFB8D94F31A84 ON prescription (medecin_id)');
        $this->addSql('ALTER TABLE reclamation DROP date_fin, CHANGE patient_id patient_id INT DEFAULT NULL');
    }
}
