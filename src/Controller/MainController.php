<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class MainController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, RouterInterface $router): Response
    {
        $token = $tokenStorage->getToken();
        $user = $token?->getUser();
        $dossierMedicalId = null;  // Default value for DossierMedical

        // Check if the user is logged in
        if ($user) {
            // If the user is a patient and not already on the 'home_page'
            if (in_array('ROLE_PATIENT', $user->getRoles())) {
                // Directly fetch the DossierMedical related to the user if the user is a patient
                $dossierMedical = $entityManager->getRepository(DossierMedical::class)->findOneBy(['user' => $user]);

                if ($dossierMedical) {
                    $dossierMedicalId = $dossierMedical->getId();
                }

                // Avoid redirect loop for patients: Only redirect if the user isn't already on the home page
                if ($router->getContext()->getPathInfo() !== $router->generate('home_page')) {
                    return $this->redirectToRoute('home_page');
                }
            }

            // Logic for doctors
            if (in_array('ROLE_MEDECIN', $user->getRoles())) {
                // Perform doctor-specific actions here, like fetching patient lists or something relevant for medecin
                // Example:
                $patients = $entityManager->getRepository(User::class)->findBy(['roles' => 'ROLE_PATIENT']);

                // Redirect the doctor to a different page (e.g., the doctor dashboard)
                return $this->redirectToRoute('medecin_dashboard'); // Replace 'doctor_dashboard' with your actual route for doctors
            }
        }

        // If no user is logged in or any other condition is met
        return $this->render('main/index.html.twig', [
            'user' => $user,
            'dossierMedicalId' => $dossierMedicalId,
            'patients' => isset($patients) ? $patients : null, // Pass patients data if the user is a doctor
        ]);
    }



    #[Route('/doctors', name: 'doctors_page')]
    public function doctors(): Response
    {
        return $this->render('main/doctors.html.twig');
    }

    #[Route('/evenement', name: 'evenement_page')]
    public function evenement(): Response
    {
        return $this->render('main/evenement.html.twig');
    }

    #[Route('/article', name: 'article_page')]
    public function article(): Response
    {
        return $this->render('main/article.html.twig');
    }

    #[Route('/reclamation', name: 'reclamation_page')]
    public function reclamation(): Response
    {
        return $this->render('main/reclamation.html.twig');
    }

    #[Route('/appointment', name: 'appointment_page')]
    public function appointment(): Response
    {
        return $this->render('main/appointment.html.twig');
    }

    #[Route('/createAccount', name: 'createAccount_page')]
    public function createAccount(): Response
    {
        return $this->render('main/createAccount.html.twig');
    }

    #[Route('/Login', name: 'createAccount_page')]
    public function Login(): Response
    {
        return $this->render('main/login.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }

    #[Route('/forgotPassword', name: 'forgotPassword_page')]
    public function forgotPassword(): Response
    {
        return $this->render('main/forgotPassword.html.twig', [
            'controller_name' => 'MedecinController',
        ]);
    }







    //////////////////////////
    #[Route('/formMed/{id}', name: 'formMed_page')]
    public function formMed(int $id, MedecinRepository $medecinRepository): Response
    {
        // Retrieve the medecin (doctor) from the database using the ID passed in the URL
        $medecin = $medecinRepository->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Medecin not found!');
        }
        dump($medecin);

        // Return the response and pass the medecin variable to the Twig template
        return $this->render('main/form.html.twig', [
            'medecin' => $medecin,
        ]);
    }
}
