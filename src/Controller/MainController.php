<?php

namespace App\Controller;

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

    #[Route('/diagnostique', name: 'diagnostique_page')]
    public function diagnostique(): Response
    {
        return $this->render('main/diagnostique.html.twig'); 
    }

    #[Route('/appointment', name: 'appointment_page')]
    public function appointment(): Response
    {
        return $this->render('main/appointment.html.twig'); 
    }
}
