<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\Prescription;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(Security $security): Response
    {
        // Get the logged-in user
        $user = $security->getUser();

        return $this->render('admin/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/admin/prescription', name: 'prescriptionAdmin')]
    public function prescription(Security $security, EntityManagerInterface $entityManager): Response
    {

        $user = $security->getUser();

        // Retrieve the 'prescriptions' related to the logged-in Medecin
        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findAll();
        $prescriptions = $entityManager->getRepository(Prescription::class)->findAll();


        return $this->render('admin/prescription.html.twig', [
            'user' => $user,
            'diagnostiques' => $diagnostiques,
            'prescriptions' => $prescriptions
        ]);
    }
}
