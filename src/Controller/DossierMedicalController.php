<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Repository\DossierMedicalRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/dossiers')]
class DossierMedicalController extends AbstractController
{
    #[Route('', name: 'create_dossier', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        // Decode the JSON request body
        $data = json_decode($request->getContent(), true);

        // Create a new DossierMedical instance
        $dossier = new DossierMedical();
        $dossier->setDateCreation(new \DateTime($data['date_creation']));

        // Find the Patient by ID
        $patient = $entityManager->getRepository(Patient::class)->find($data['patient']);
        if (!$patient) {
            return $this->json(['error' => 'Patient not found'], 404);
        }

        // Set the patient to the dossier
        $dossier->setPatient($patient);

        // Persist and flush to the database
        $entityManager->persist($dossier);
        $entityManager->flush();

        // Return a JSON response with the newly created dossier
        return $this->json($dossier, 201);
    }
}
