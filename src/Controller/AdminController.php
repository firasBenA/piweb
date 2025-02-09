<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class AdminController extends AbstractController
{
    #[Route('/admin/dashboard', name: 'adminDashboard_page')]
    public function index(): Response
    {
        return $this->render('admin/index.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/createAccount', name: 'createAccount_page')]
    public function createAccount(): Response
    {
        return $this->render('admin/createAccount.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    #[Route('/login', name: 'login_page')]
    public function Login(): Response
    {
        return $this->render('admin/login.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }
    #[Route('/forgotPassword', name: 'forgotPassword_page')]
    public function forgotPassword(): Response
    {
        return $this->render('admin/forgotPassword.html.twig', [
            'controller_name' => 'AdminController',
        ]);
    }

    
}
