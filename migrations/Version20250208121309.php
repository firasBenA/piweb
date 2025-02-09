<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250208121309 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE admin (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE article (id INT AUTO_INCREMENT NOT NULL, evenement_id INT DEFAULT NULL, titre VARCHAR(255) NOT NULL, contenue VARCHAR(255) NOT NULL, image VARCHAR(255) NOT NULL, prix_article INT NOT NULL, commantaire VARCHAR(255) NOT NULL, nb_jaime INT NOT NULL, INDEX IDX_23A0E66FD02F13 (evenement_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consultation (id INT AUTO_INCREMENT NOT NULL, rendez_vous_id INT NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, date DATE NOT NULL, prix INT NOT NULL, type_consultation VARCHAR(255) NOT NULL, UNIQUE INDEX UNIQ_964685A691EF7EAA (rendez_vous_id), INDEX IDX_964685A66B899279 (patient_id), INDEX IDX_964685A64F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE consultation_his (id INT AUTO_INCREMENT NOT NULL, consultation_id INT DEFAULT NULL, date DATE NOT NULL, type VARCHAR(255) NOT NULL, prix INT NOT NULL, notes_med VARCHAR(255) NOT NULL, traitement VARCHAR(255) NOT NULL, duree INT NOT NULL, UNIQUE INDEX UNIQ_B96CEB5862FF6CDF (consultation_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diagnostique (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT DEFAULT NULL, patient_id INT DEFAULT NULL, medecin_id INT DEFAULT NULL, prescription_id INT DEFAULT NULL, date_diagnostique DATE NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, INDEX IDX_38C9AFE97750B79F (dossier_medical_id), INDEX IDX_38C9AFE96B899279 (patient_id), INDEX IDX_38C9AFE94F31A84 (medecin_id), UNIQUE INDEX UNIQ_38C9AFE993DB413D (prescription_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE diagnostique_symptomes (diagnostique_id INT NOT NULL, symptomes_id INT NOT NULL, INDEX IDX_C0F67A1EB55F415 (diagnostique_id), INDEX IDX_C0F67A1E2902E187 (symptomes_id), PRIMARY KEY(diagnostique_id, symptomes_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE dossier_medical (id INT AUTO_INCREMENT NOT NULL, patient_id INT DEFAULT NULL, date_prescription DATE NOT NULL, UNIQUE INDEX UNIQ_3581EE626B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE evenement (id INT AUTO_INCREMENT NOT NULL, medecin_id INT NOT NULL, nom VARCHAR(255) NOT NULL, contenue VARCHAR(255) NOT NULL, type VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, lieux_event VARCHAR(255) NOT NULL, date_event DATE NOT NULL, INDEX IDX_B26681E4F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE medecin (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, specialite VARCHAR(255) NOT NULL, certificat VARCHAR(255) NOT NULL, adresse VARCHAR(255) NOT NULL, age INT NOT NULL, sexe VARCHAR(255) NOT NULL, image_de_profil VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE notification (id INT AUTO_INCREMENT NOT NULL, message VARCHAR(255) NOT NULL, date DATE NOT NULL, type VARCHAR(255) NOT NULL, destinataire VARCHAR(255) NOT NULL, statut VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE patient (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, prenom VARCHAR(255) NOT NULL, email VARCHAR(255) NOT NULL, mot_de_passe VARCHAR(255) NOT NULL, age INT NOT NULL, telephone INT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE prescription (id INT AUTO_INCREMENT NOT NULL, dossier_medical_id INT DEFAULT NULL, diagnostique_id INT DEFAULT NULL, contenue VARCHAR(255) NOT NULL, date_prscription DATE NOT NULL, INDEX IDX_1FBFB8D97750B79F (dossier_medical_id), UNIQUE INDEX UNIQ_1FBFB8D9B55F415 (diagnostique_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE reclamation (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, sujet VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_debut DATE NOT NULL, date_fin DATE NOT NULL, etat VARCHAR(255) NOT NULL, INDEX IDX_CE6064046B899279 (patient_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE rendez_vous (id INT AUTO_INCREMENT NOT NULL, patient_id INT NOT NULL, medecin_id INT NOT NULL, date DATE NOT NULL, statut VARCHAR(255) NOT NULL, type_rdv VARCHAR(255) NOT NULL, cause VARCHAR(255) NOT NULL, INDEX IDX_65E8AA0A6B899279 (patient_id), INDEX IDX_65E8AA0A4F31A84 (medecin_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE symptomes (id INT AUTO_INCREMENT NOT NULL, nom VARCHAR(255) NOT NULL, description VARCHAR(255) NOT NULL, date_apparition DATE NOT NULL, zones_corps VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', available_at DATETIME NOT NULL COMMENT \'(DC2Type:datetime_immutable)\', delivered_at DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE article ADD CONSTRAINT FK_23A0E66FD02F13 FOREIGN KEY (evenement_id) REFERENCES evenement (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A691EF7EAA FOREIGN KEY (rendez_vous_id) REFERENCES rendez_vous (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A66B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE consultation ADD CONSTRAINT FK_964685A64F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE consultation_his ADD CONSTRAINT FK_B96CEB5862FF6CDF FOREIGN KEY (consultation_id) REFERENCES consultation (id)');
        $this->addSql('ALTER TABLE diagnostique ADD CONSTRAINT FK_38C9AFE97750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE diagnostique ADD CONSTRAINT FK_38C9AFE96B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE diagnostique ADD CONSTRAINT FK_38C9AFE94F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE diagnostique ADD CONSTRAINT FK_38C9AFE993DB413D FOREIGN KEY (prescription_id) REFERENCES prescription (id)');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1EB55F415 FOREIGN KEY (diagnostique_id) REFERENCES diagnostique (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE diagnostique_symptomes ADD CONSTRAINT FK_C0F67A1E2902E187 FOREIGN KEY (symptomes_id) REFERENCES symptomes (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE dossier_medical ADD CONSTRAINT FK_3581EE626B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE evenement ADD CONSTRAINT FK_B26681E4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D97750B79F FOREIGN KEY (dossier_medical_id) REFERENCES dossier_medical (id)');
        $this->addSql('ALTER TABLE prescription ADD CONSTRAINT FK_1FBFB8D9B55F415 FOREIGN KEY (diagnostique_id) REFERENCES diagnostique (id)');
        $this->addSql('ALTER TABLE reclamation ADD CONSTRAINT FK_CE6064046B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A6B899279 FOREIGN KEY (patient_id) REFERENCES patient (id)');
        $this->addSql('ALTER TABLE rendez_vous ADD CONSTRAINT FK_65E8AA0A4F31A84 FOREIGN KEY (medecin_id) REFERENCES medecin (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE article DROP FOREIGN KEY FK_23A0E66FD02F13');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A691EF7EAA');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A66B899279');
        $this->addSql('ALTER TABLE consultation DROP FOREIGN KEY FK_964685A64F31A84');
        $this->addSql('ALTER TABLE consultation_his DROP FOREIGN KEY FK_B96CEB5862FF6CDF');
        $this->addSql('ALTER TABLE diagnostique DROP FOREIGN KEY FK_38C9AFE97750B79F');
        $this->addSql('ALTER TABLE diagnostique DROP FOREIGN KEY FK_38C9AFE96B899279');
        $this->addSql('ALTER TABLE diagnostique DROP FOREIGN KEY FK_38C9AFE94F31A84');
        $this->addSql('ALTER TABLE diagnostique DROP FOREIGN KEY FK_38C9AFE993DB413D');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1EB55F415');
        $this->addSql('ALTER TABLE diagnostique_symptomes DROP FOREIGN KEY FK_C0F67A1E2902E187');
        $this->addSql('ALTER TABLE dossier_medical DROP FOREIGN KEY FK_3581EE626B899279');
        $this->addSql('ALTER TABLE evenement DROP FOREIGN KEY FK_B26681E4F31A84');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D97750B79F');
        $this->addSql('ALTER TABLE prescription DROP FOREIGN KEY FK_1FBFB8D9B55F415');
        $this->addSql('ALTER TABLE reclamation DROP FOREIGN KEY FK_CE6064046B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A6B899279');
        $this->addSql('ALTER TABLE rendez_vous DROP FOREIGN KEY FK_65E8AA0A4F31A84');
        $this->addSql('DROP TABLE admin');
        $this->addSql('DROP TABLE article');
        $this->addSql('DROP TABLE consultation');
        $this->addSql('DROP TABLE consultation_his');
        $this->addSql('DROP TABLE diagnostique');
        $this->addSql('DROP TABLE diagnostique_symptomes');
        $this->addSql('DROP TABLE dossier_medical');
        $this->addSql('DROP TABLE evenement');
        $this->addSql('DROP TABLE medecin');
        $this->addSql('DROP TABLE notification');
        $this->addSql('DROP TABLE patient');
        $this->addSql('DROP TABLE prescription');
        $this->addSql('DROP TABLE reclamation');
        $this->addSql('DROP TABLE rendez_vous');
        $this->addSql('DROP TABLE symptomes');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
