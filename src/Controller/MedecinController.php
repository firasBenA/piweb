<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedecinController extends AbstractController
{
    #[Route('/medecin/dashboard', name: 'medecinDashboard_page')]
    public function index(): Response
    {
        return $this->render('medecin/index.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }

    #[Route('/createAccount', name: 'createAccount_page')]
    public function createAccount(): Response
    {
        return $this->render('medecin/createAccount.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }

    #[Route('/login', name: 'login_page')]
    public function Login(): Response
    {
        return $this->render('medecin/login.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }

    #[Route('/forgotPassword', name: 'forgotPassword_page')]
    public function forgotPassword(): Response
    {
        return $this->render('medecin/forgotPassword.html.twig', [
            'controller_name' => 'MedecinController',
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
