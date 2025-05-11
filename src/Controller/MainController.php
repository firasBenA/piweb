<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Entity\User;
use App\Repository\MedecinRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Http\Attribute\IsGranted;


class MainController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(EntityManagerInterface $entityManager, TokenStorageInterface $tokenStorage, RouterInterface $router): Response
    {
        $token = $tokenStorage->getToken();
        $user = $token?->getUser();
        $dossierMedicalId = null;
    
        if ($user instanceof User) {
            $roles = $user->getRoles();
    
            // Check for admin first
            if (in_array('ROLE_ADMIN', $roles, true)) {
                return $this->redirectToRoute('admin_dashboard');
            }
    
            // Then check for doctor
            if (in_array('ROLE_MEDECIN', $roles, true)) {
                return $this->redirectToRoute('medecin_dashboard');
            }
    
            // Handle patient role
            if (in_array('ROLE_PATIENT', $roles, true)) {
                $dossierMedical = $entityManager->getRepository(DossierMedical::class)->findOneBy(['user' => $user]);
                if ($dossierMedical) {
                    $dossierMedicalId = $dossierMedical->getId();
                }
            }
        }
    
        return $this->render('main/index.html.twig', [
            'user' => $user,
            'dossierMedicalId' => $dossierMedicalId,
            'patients' => null,
        ]);
    }



    #[Route('/doctors', name: 'doctors_page')]
    public function doctors(): Response
    {
        return $this->render('main/doctors.html.twig');
    }

    #[Route('/evenement', name: 'evenement_page')]
    public function evenement(Security $security): Response
    {
        // Get the currently logged-in user
        $user = $security->getUser();

        // Pass the user to the template
        return $this->render('main/evenement.html.twig', [
            'user' => $user
        ]);
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

    #[Route('/admin/dashboard', name: 'adminDashboard_page')]
    public function admin(): Response
    {
        return $this->render('admin/index.html.twig');
    }




    #[Route('/map', name: 'app_map')]
    public function map(): Response
    {
        return $this->render('map.html.twig');
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
