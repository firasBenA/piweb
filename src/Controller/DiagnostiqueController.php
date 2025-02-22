<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Symptomes;
use App\Form\DiagnostiqueType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;  // <-- Correct import here
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final class DiagnostiqueController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/diagnose', name: 'diagnose_form', methods: ['GET', 'POST'])]
    public function diagnoseForm(Request $request, HttpClientInterface $httpClient): \Symfony\Component\HttpFoundation\Response
    {

        $id = $request->query->get('id');


        if (!$id) {
            return new JsonResponse(['error' => 'ID manquant'], 400);
        }

        $dossierMedical = $this->entityManager->getRepository(DossierMedical::class)->find($id);
        if (!$dossierMedical) {
            return new JsonResponse(['error' => 'Dossier médical introuvable'], 404);
        }

        // Define the symptoms dictionary
        $symptomsDict = [
            'itching' => 0,
            'skin_rash' => 1,
            'nodal_skin_eruptions' => 2,
            'continuous_sneezing' => 3,
            'shivering' => 4,
            'chills' => 5,
            'joint_pain' => 6,
            'stomach_pain' => 7,
            'acidity' => 8,
            'ulcers_on_tongue' => 9,
            'muscle_wasting' => 10,
            'vomiting' => 11,
            'burning_micturition' => 12,
            'spotting_urination' => 13,
            'fatigue' => 14,
            'weight_gain' => 15,
            'anxiety' => 16,
            'cold_hands_and_feets' => 17,
            'mood_swings' => 18,
            'weight_loss' => 19,
            'restlessness' => 20,
            'lethargy' => 21,
            'patches_in_throat' => 22,
            'irregular_sugar_level' => 23,
            'cough' => 24,
            'high_fever' => 25,
            'sunken_eyes' => 26,
            'breathlessness' => 27,
            'sweating' => 28,
            'dehydration' => 29,
            'indigestion' => 30,
            'headache' => 31,
            'yellowish_skin' => 32,
            'dark_urine' => 33,
            'nausea' => 34,
            'loss_of_appetite' => 35,
            'pain_behind_the_eyes' => 36,
            'back_pain' => 37,
            'constipation' => 38,
            'abdominal_pain' => 39,
            'diarrhoea' => 40,
            'mild_fever' => 41,
            'yellow_urine' => 42,
            'yellowing_of_eyes' => 43
        ];


        // Create the Diagnostique entity
        $diagnostique = new Diagnostique();

        // Create the form
        $form = $this->createForm(DiagnostiqueType::class, $diagnostique, [
            'symptoms_dict' => $symptomsDict
        ]);

        $form->handleRequest($request);

        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {

            $symptoms = $form->get('selectedSymptoms')->getData();
            try {
                // 4️⃣ Envoi des symptômes à l'API Flask
                $response = $httpClient->request('POST', 'http://127.0.0.1:5000/predict', [
                    'json' => ['symptoms' => $symptoms]
                ]);

                if ($response->getStatusCode() !== 200) {
                    return new JsonResponse(['error' => 'Erreur API Flask', 'status' => $response->getStatusCode()], 500);
                }

                $diagnosisData = $response->toArray();
                $diagnosisName = $diagnosisData['disease'] ?? 'Maladie inconnue';
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Échec de communication avec l\'API Flask', 'details' => $e->getMessage()], 500);
            }

            $zoneCorps = $form->get('zoneCorps')->getData();
            $dateSymptomes = $form->get('dateSymptomes')->getData();

            if ($dateSymptomes instanceof \DateTimeInterface || $dateSymptomes === null) {
                $diagnostique->setDateSymptomes($dateSymptomes);
            } else {
                $diagnostique->setDateSymptomes(new \DateTime()); // Default to today if necessary
            }
            $diagnostique->setNom($diagnosisName);
            $diagnostique->setStatus(0);
            $diagnostique->setDossierMedical($dossierMedical); // Associate DossierMedical
            $diagnostique->setDateDiagnostique(new \DateTime());
            $diagnostique->setSelectedSymptoms($symptoms);
            $diagnostique->setZoneCorps($zoneCorps);
            $diagnostique->setDescription($diagnosisData['description'] ?? 'Pas de description disponible.');

            // Persist the Diagnostique entity to the database
            $this->entityManager->persist($diagnostique);
            $this->entityManager->flush();

            // Redirect to another route after successful submission
            return $this->redirect($request->getUri());
        }

        // Render the form in the template
        return $this->render('main/diagnostique.html.twig', [
            'form' => $form->createView(),
            'symptoms_dict' => $symptomsDict,
            'dossierMedical' => $dossierMedical,
        ]);
    }


    #[Route('/diagnose-api', name: 'diagnose', methods: ['POST'])] // ✅ Autoriser POST
    public function diagnose(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): JsonResponse
    {
        // 1️⃣ Récupération de l'ID depuis l'URL
        $id = $request->query->get('id');

        // 2️⃣ Vérification de l'existence du dossier médical
        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->find($id);

        if (!$dossierMedical) {
            return new JsonResponse(['error' => 'Dossier médical introuvable'], 404);
        }

        // 3️⃣ Récupération des symptômes envoyés
        $data = json_decode($request->getContent(), true);
        $symptoms = $data['symptoms'] ?? [];

        // Vérifier que c'est bien un tableau avant de le convertir en string
        if (is_array($symptoms)) {
            $symptoms = implode(',', $symptoms);
        }
        $dateSymptomes = $data['dateSymptomes'];

        if (empty($symptoms)) {
            return new JsonResponse(['error' => 'Aucun symptôme fourni'], 400);
        }

        try {
            // 4️⃣ Envoi des symptômes à l'API Flask
            $response = $httpClient->request('POST', 'http://127.0.0.1:5000/predict', [
                'json' => ['symptoms' => $symptoms]
            ]);

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['error' => 'Erreur API Flask', 'status' => $response->getStatusCode()], 500);
            }

            $diagnosisData = $response->toArray();
            $diagnosisName = $diagnosisData['disease'] ?? 'Maladie inconnue';
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Échec de communication avec l\'API Flask', 'details' => $e->getMessage()], 500);
        }

        $zoneCorps = $data['zoneCorps'] ?? 'unknown';

        // 5️⃣ Enregistrement du diagnostic en base de données
        $diagnosis = new Diagnostique();
        $diagnosis->setNom($diagnosisName);
        $diagnosis->setDateDiagnostique(new \DateTime());
        $diagnosis->setDossierMedical($dossierMedical);
        $diagnosis->setDescription($diagnosisData['description'] ?? 'Pas de description disponible.');
        $diagnosis->setStatus(0);
        $diagnosis->setSelectedSymptoms($symptoms);
        $diagnosis->setZoneCorps($zoneCorps);
        if ($dateSymptomes) {
            try {
                $dateSymptomes = new \DateTime($dateSymptomes);
            } catch (\Exception $e) {
                return new JsonResponse(['error' => 'Invalid date format. Expected YYYY-MM-DD.'], 400);
            }
            $diagnosis->setDateSymptomes($dateSymptomes);
        }

        $entityManager->persist($diagnosis);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Diagnostic enregistré avec succès',
            'disease' => $diagnosisName,
            'dossierMedicalId' => $dossierMedical->getId() // Correction ici
        ]);
    }
}
