<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Repository\MedecinRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class MainController extends AbstractController
{
    #[Route('/', name: 'home_page')]
    public function index(): Response
    {
        return $this->render('main/index.html.twig');
    }

    #[Route('/doctors', name: 'doctors_page')]
    public function doctors(): Response
    {
        return $this->render('main/doctors.html.twig');
    }

    #[Route('/blog', name: 'blog_page')]
    public function blog(): Response
    {
        return $this->render('main/blog.html.twig');
    }

    #[Route('/blog_details', name: 'blog_details_page')]
    public function blog_details(): Response
    {
        return $this->render('main/blog_details.html.twig');
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

    #[Route('/login', name: 'login_page')]
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
