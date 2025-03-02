<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Entity\Symptomes;
use App\Entity\User;
use App\Form\DiagnostiqueType;
use App\Repository\DiagnostiqueRepository;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;  // <-- Correct import here
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\HttpClient\Exception\ClientExceptionInterface;
use Symfony\Contracts\HttpClient\Exception\ServerExceptionInterface;
use Symfony\Contracts\HttpClient\HttpClientInterface;
use Knp\Snappy\Pdf;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\Response;


final class DiagnostiqueController extends AbstractController
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/diagnose', name: 'diagnose_form', methods: ['GET', 'POST'])]
    public function diagnoseForm(Request $request, HttpClientInterface $httpClient, EntityManagerInterface $entityManager): \Symfony\Component\HttpFoundation\Response
    {

        $user = $this->getUser();
        // Check if a user is logged in
        if (!$user instanceof UserInterface) {
            throw $this->createAccessDeniedException('You are not logged in.');
        }

        $medecins = $entityManager->getRepository(User::class)->findAll();

        $locations = [];
        foreach ($medecins as $medecin) {
            if ($medecin->getLatitude() && $medecin->getLongitude()) {
                $locations[] = [
                    'nom' => $medecin->getNom(),
                    'latitude' => $medecin->getLatitude(),
                    'longitude' => $medecin->getLongitude(),
                ];
            }
        }

        $userRepository = $entityManager->getRepository(User::class);


        $id = $request->query->get('id');



        if (!$id) {
            return new JsonResponse(['error' => 'ID manquant'], 400);
        }

        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->find($id);
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
            'yellowing_of_eyes' => 43,
            'acute_liver_failure' => 44,
            'fluid_overload' => 45,
            'swelling_of_stomach' => 46,
            'swelled_lymph_nodes' => 47,
            'malaise' => 48,
            'blurred_and_distorted_vision' => 49,
            'phlegm' => 50,
            'throat_irritation' => 51,
            'redness_of_eyes' => 52,
            'sinus_pressure' => 53,
            'runny_nose' => 54,
            'congestion' => 55,
            'chest_pain' => 56,
            'weakness_in_limbs' => 57,
            'fast_heart_rate' => 58,
            'pain_during_bowel_movements' => 59,
            'pain_in_anal_region' => 60,
            'bloody_stool' => 61,
            'irritation_in_anus' => 62,
            'neck_pain' => 63,
            'dizziness' => 64,
            'cramps' => 65,
            'bruising' => 66,
            'obesity' => 67,
            'swollen_legs' => 68,
            'swollen_blood_vessels' => 69,
            'puffy_face_and_eyes' => 70,
            'enlarged_thyroid' => 71,
            'brittle_nails' => 72,
            'swollen_extremities' => 73,
            'excessive_hunger' => 74,
            'extra_marital_contacts' => 75,
            'drying_and_tingling_lips' => 76,
            'slurred_speech' => 77,
            'knee_pain' => 78,
            'hip_joint_pain' => 79,
            'muscle_weakness' => 80,
            'stiff_neck' => 81,
            'swelling_joints' => 82,
            'movement_stiffness' => 83,
            'spinning_movements' => 84,
            'loss_of_balance' => 85,
            'unsteadiness' => 86,
            'weakness_of_one_body_side' => 87,
            'loss_of_smell' => 88,
            'bladder_discomfort' => 89,
            'foul_smell_of_urine' => 90,
            'continuous_feel_of_urine' => 91,
            'passage_of_gases' => 92,
            'internal_itching' => 93,
            'toxic_look_typhos' => 94,
            'depression' => 95,
            'irritability' => 96,
            'muscle_pain' => 97,
            'altered_sensorium' => 98,
            'red_spots_over_body' => 99,
            'belly_pain' => 100,
            'abnormal_menstruation' => 101,
            'dischromic_patches' => 102,
            'watering_from_eyes' => 103,
            'increased_appetite' => 104,
            'polyuria' => 105,
            'family_history' => 106,
            'mucoid_sputum' => 107,
            'rusty_sputum' => 108,
            'lack_of_concentration' => 109,
            'visual_disturbances' => 110,
            'receiving_blood_transfusion' => 111,
            'receiving_unsterile_injections' => 112,
            'coma' => 113,
            'stomach_bleeding' => 114,
            'distention_of_abdomen' => 115,
            'history_of_alcohol_consumption' => 116,
            'fluid_overload_1' => 117,
            'blood_in_sputum' => 118,
            'prominent_veins_on_calf' => 119,
            'palpitations' => 120,
            'painful_walking' => 121,
            'pus_filled_pimples' => 122,
            'blackheads' => 123,
            'scurring' => 124,
            'skin_peeling' => 125,
            'silver_like_dusting' => 126,
            'small_dents_in_nails' => 127,
            'inflammatory_nails' => 128,
            'blister' => 129,
            'red_sore_around_nose' => 130,
            'yellow_crust_ooze' => 131
        ];

        // Create the Diagnostique entity
        $diagnostique = new Diagnostique();
        $diagnostique->setDateSymptomes(new \DateTime());

        // Create the form
        $form = $this->createForm(DiagnostiqueType::class, $diagnostique, [
            'symptoms_dict' => $symptomsDict,
        ]);




        $form->handleRequest($request);
        $diagnostique->setDateDiagnostique(new \DateTime());
        // Check if the form is submitted and valid
        if ($form->isSubmitted() && $form->isValid()) {

            $symptoms = $form->get('selectedSymptoms')->getData();
            $medecinId = $form->get('medecin')->getData();
            if (!$medecinId) {
                return new JsonResponse(['error' => 'Doctor ID is missing'], 400);
            }
            $medecin = $entityManager->getRepository(User::class)->find($medecinId);
            if (!$medecin) {
                return new JsonResponse(['error' => 'Doctor not found'], 404);
            }

            try {
                // 4️⃣ Envoi des symptômes à l'API Flask
                $response = $httpClient->request('POST', 'http://127.0.0.1:5000/predict', [
                    'json' => ['symptoms' => $symptoms],
                    'headers' => [
                        'Cache-Control' => 'no-cache'
                    ]
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
            $diagnostique->setSelectedSymptoms($symptoms);
            $diagnostique->setZoneCorps($zoneCorps);
            $diagnostique->setDescription($diagnosisData['description'] ?? 'Pas de description disponible.');
            $diagnostique->setPatient($user);
            $diagnostique->setMedecin($medecin);


            // Persist the Diagnostique entity to the database
            $this->entityManager->persist($diagnostique);
            $this->entityManager->flush();

            // Redirect to another route after successful submission
            
            dd($request->request->all()); 

            return $this->redirectToRoute('diagnose_form');
        }



        // Render the form in the template
        return $this->render('main/diagnostique.html.twig', [
            'form' => $form->createView(),
            'symptoms_dict' => $symptomsDict,
            'dossierMedical' => $dossierMedical,
            'user' => $user,
            'diagnostique' => $diagnostique,
            'medecinLocations' => $locations,
            'medecins' => $medecins
        ]);
    }


    #[Route('/diagnose-api', name: 'diagnose', methods: ['POST'])] // ✅ Autoriser POST
    public function diagnose(Request $request, EntityManagerInterface $entityManager, HttpClientInterface $httpClient): JsonResponse
    {

        $user = $this->getUser();
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
                'json' => ['symptoms' => $data['symptoms']]
            ]);

            dump($symptoms); // Vérifie comment les symptômes sont envoyés
            dump($response->toArray());
            dump($response);

            if ($response->getStatusCode() !== 200) {
                return new JsonResponse(['error' => 'Erreur API Flask', 'status' => $response->getStatusCode()], 500);
            }

            $diagnosisData = $response->toArray();
            $diagnosisName = $diagnosisData['disease'] ?? 'Maladie inconnue';
        } catch (\Exception $e) {
            return new JsonResponse(['error' => 'Échec de communication avec l\'API Flask', 'details' => $e->getMessage()], 500);
        }

        $zoneCorps = $data['zoneCorps'] ?? 'unknown';
        $medecinId = $data['medecin'] ?? null;  // Get the medecin ID from the request

        // 5️⃣ Enregistrement du diagnostic en base de données
        $diagnosis = new Diagnostique();
        $diagnosis->setNom($diagnosisName);
        $diagnosis->setDateDiagnostique(new \DateTime());
        $diagnosis->setDossierMedical($dossierMedical);
        $diagnosis->setDescription($diagnosisData['description'] ?? 'Pas de description disponible.');
        $diagnosis->setStatus(0);
        $diagnosis->setSelectedSymptoms($symptoms);
        $diagnosis->setZoneCorps($zoneCorps);
        $diagnosis->setPatient($user);

        // Get the doctor (medecin) by ID if it is provided
        if ($medecinId) {
            $medecin = $entityManager->getRepository(User::class)->find($medecinId);
            if ($medecin) {
                $diagnosis->setMedecin($medecin);  // Set the selected doctor
            } else {
                return new JsonResponse(['error' => 'Médecin introuvable'], 404);  // If no doctor found
            }
        }


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

        $this->addFlash('success', 'Your diagnosis has been submitted successfully!');
        return new JsonResponse([
            'message' => 'Diagnostic enregistré avec succès',
            'disease' => $diagnosisName,
            'dossierMedicalId' => $dossierMedical->getId()
        ]);
    }


    #[Route('/diagnostique/delete/{id}', name: 'app_diagnostique_delete', methods: ['POST'])]
    public function delete(int $id, DiagnostiqueRepository $diagnostiqueRepository, EntityManagerInterface $em): Response
    {
        $diagnostique = $diagnostiqueRepository->find($id);

        if ($diagnostique) {

            $em->remove($diagnostique);
            $em->flush();
        }

        return $this->redirectToRoute('prescriptionAdmin');
    }



    #[Route('/diagnostique/pdf/{id}', name: 'diagnostique_pdf')]
    public function generatePdf(Diagnostique $diagnostique, Pdf $pdf): Response
    {
        // Convert the Twig template to HTML
        $html = $this->renderView('diagnostique/pdf.html.twig', [
            'diagnostique' => $diagnostique,
        ]);

        // Generate PDF from HTML
        $pdfContent = $pdf->getOutputFromHtml($html);

        // Create a response to download the PDF
        $response = new Response($pdfContent);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'diagnosis_' . $diagnostique->getId() . '.pdf'
        ));

        return $response;
    }

    #[Route('/get-doctors', name: 'get_doctors', methods: ['POST'])]
    public function getDoctors(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        // Check if disease is provided
        if (!isset($data['disease']) || empty($data['disease'])) {
            return new JsonResponse(['error' => 'Disease is required'], 400);
        }

        $disease = $data['disease'];

        // Get doctors based on specialite matching the disease
        $doctors = $entityManager->getRepository(User::class)->findBy(['specialite' => $disease]);

        if (!$doctors) {
            return new JsonResponse([], 200); // Return empty array if no doctors found
        }

        // Format response
        $response = [];
        foreach ($doctors as $doctor) {
            $response[] = [
                'nom' => $doctor->getNom(),
                'specialite' => $doctor->getSpecialite(),
                'telephone' => $doctor->getTelephone(),
                'id' => $doctor->getId(),
            ];
        }

        return new JsonResponse($response, 200);
    }
}
