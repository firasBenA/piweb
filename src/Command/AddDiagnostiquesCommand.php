<?php

namespace App\Command;

use App\Entity\Diagnostique;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class AddDiagnostiquesCommand extends Command
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        parent::__construct();
        $this->entityManager = $entityManager;
    }

    protected static $defaultName = 'app:add-diagnostiques';

    protected function configure()
    {
        $this->setDescription('Add diagnostiques to the database');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        // Disease list from user input
        $diseaseList = [
            15 => 'Fungal infection',
            4 => 'Allergy',
            16 => 'GERD',
            9 => 'Chronic cholestasis',
            14 => 'Drug Reaction',
            33 => 'Peptic ulcer disease',
            1 => 'AIDS',
            12 => 'Diabetes',
            17 => 'Gastroenteritis',
            6 => 'Bronchial Asthma',
            23 => 'Hypertension',
            30 => 'Migraine',
            7 => 'Cervical spondylosis',
            32 => 'Paralysis (brain hemorrhage)',
            28 => 'Jaundice',
            29 => 'Malaria',
            8 => 'Chicken pox',
            11 => 'Dengue',
            37 => 'Typhoid',
            40 => 'Hepatitis A',
            19 => 'Hepatitis B',
            20 => 'Hepatitis C',
            21 => 'Hepatitis D',
            22 => 'Hepatitis E',
            3 => 'Alcoholic hepatitis',
            36 => 'Tuberculosis',
            10 => 'Common Cold',
            34 => 'Pneumonia',
            13 => 'Dimorphic hemorrhoids(piles)',
            18 => 'Heart attack',
            39 => 'Varicose veins',
            26 => 'Hypothyroidism',
            24 => 'Hyperthyroidism',
            25 => 'Hypoglycemia',
            31 => 'Osteoarthritis',
            5 => 'Arthritis',
            0 => '(vertigo) Paroxysmal Positional Vertigo',
            2 => 'Acne',
            38 => 'Urinary tract infection',
            35 => 'Psoriasis',
            27 => 'Impetigo'
        ];

        foreach ($diseaseList as $id => $name) {
            $diagnostique = new Diagnostique();
            $diagnostique->setNom($name);
            $diagnostique->setDescription("Description for {$name}"); // You can add more detailed descriptions if necessary
            
            // Set current date as date_diagnostique
            $diagnostique->setDateDiagnostique(new \DateTime()); // Or any specific date you prefer

            // Persist the Diagnostique entity
            $this->entityManager->persist($diagnostique);
        }

        // Flush all the persisted records to the database
        $this->entityManager->flush();

        $output->writeln('Diagnostiques added successfully!');

        return Command::SUCCESS;
    }
}
