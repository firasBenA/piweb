<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\Medecin;
use App\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedecinController extends AbstractController
{
    #[Route('/medecin/dashboard/{id}', name: 'medecinDashboard_page')]
    public function dashboard(int $id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the doctor (medecin) by its ID

        $medecin = $entityManager->getRepository(Medecin::class)->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        // Fetch patients related to the doctor (medecin)
        $patients = $entityManager->getRepository(Patient::class)->findBy(['medecin' => $medecin]);

        return $this->render('medecin/index.html.twig', [
            'medecin' => $medecin,
            'patients' => $patients,
        ]);
    }

    #[Route('/formMed', name: 'formMed_page')]
    public function formMed(): Response
    {
        return $this->render('medecin/form.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }
}
