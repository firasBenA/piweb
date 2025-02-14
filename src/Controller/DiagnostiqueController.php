<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
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
    // Route for displaying the form (GET request)
    #[Route('/diagnose', name: 'diagnose_form', methods: ['GET'])]
    public function diagnoseForm(): \Symfony\Component\HttpFoundation\Response
    {
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

        return $this->render('diagnostique/index.html.twig', [
            'symptoms_dict' => $symptomsDict
        ]);
    }

    // Route for processing the diagnosis (POST request)
    #[Route('/diagnose', name: 'diagnose_api', methods: ['POST'])]
    public function diagnose(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $symptoms = $data['symptoms'] ?? [];
        $dossierMedicalId = $data['dossierMedicalId'] ?? null;

        if (empty($symptoms)) {
            return new JsonResponse(['error' => 'No symptoms provided'], 400);
        }

        if (!$dossierMedicalId) {
            return new JsonResponse(['error' => 'No dossierMedicalId provided'], 400);
        }

        // Retrieve the DossierMedical entity
        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->find($dossierMedicalId);
        if (!$dossierMedical) {
            return new JsonResponse(['error' => 'Dossier Medical not found'], 404);
        }

        try {
            // Call Flask API
            $response = $httpClient->request('POST', 'http://127.0.0.1:5000/predict', [
                'json' => ['symptoms' => $symptoms]
            ]);

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['error' => 'Flask API error', 'status' => $response->getStatusCode()], 500);
            }

            $diagnosisData = $response->toArray();
            $diagnosisName = $diagnosisData['disease'] ?? 'Unknown Disease';
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Failed to communicate with Flask API', 'details' => $e->getMessage()], 500);
        }

        // Save diagnosis to the database
        $diagnosis = new Diagnostique();
        $diagnosis->setNom($diagnosisName);
        $diagnosis->setDateDiagnostique(new \DateTime());
        $diagnosis->setDossierMedical($dossierMedical);
        $diagnosis->setDescription($diagnosisData['description'] ?? 'No description available.');
        $diagnosis->setStatus(0);
        
        $entityManager->persist($diagnosis);
        $entityManager->flush();

        return new JsonResponse([
            'message' => 'Diagnosis saved successfully',
            'disease' => $diagnosisName,
            'dossierMedicalId' => $dossierMedicalId
        ]);
    }
}
