<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

final class InscriptionController extends AbstractController
{
    #[Route('/dashboard', name: 'medecinDashboard_page')]
    public function index(): Response
    {
        return $this->render('medecin/index.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }

    #[Route('/createAccount', name: 'createAccount_page')]
    public function createAccount(): Response
    {
        return $this->render('Inscription/createAccount.html.twig', [
            'controller_name' => 'InscriptionController',
        ]);
    }

    // src/Controller/SecurityController.php

#[Route('/login', name: 'app_login')]
public function login(AuthenticationUtils $authenticationUtils): Response
{
    // Si l'utilisateur est déjà connecté, affichez un message en fonction de son rôle
    if ($this->getUser()) {
        if (in_array('ROLE_ADMIN', $this->getUser()->getRoles())) {
            return new Response('Vous êtes connecté en tant qu\'administrateur.', Response::HTTP_OK);
        } elseif (in_array('ROLE_MEDECIN', $this->getUser()->getRoles())) {
            return new Response('Vous êtes connecté en tant que médecin.', Response::HTTP_OK);
        } else {
            return new Response('Vous êtes connecté en tant que patient.', Response::HTTP_OK);
        }
    }

    // Récupérer l'erreur de connexion s'il y en a une
    $error = $authenticationUtils->getLastAuthenticationError();
    // Récupérer le dernier nom d'utilisateur saisi par l'utilisateur
    $lastUsername = $authenticationUtils->getLastUsername();

    // Rendre le template Twig du formulaire de connexion
    return $this->render('Inscription/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
    ]);
}

    #[Route('/logout', name: 'app_logout')]
    public function logout(): void
    {
        throw new \LogicException('This method can be blank - it will be intercepted by the logout key on your firewall.');
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
