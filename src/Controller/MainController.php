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

    #[Route('/diagnostique', name: 'diagnostique_page')]
    public function diagnostique(): Response
    {
        return $this->render('main/diagnostique.html.twig'); 
    }
}
