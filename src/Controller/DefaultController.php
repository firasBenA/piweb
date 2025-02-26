<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    /**
     * @Route("/test-route", name="test_route")
     */
    public function testRoute()
    {
        return $this->json(['status' => 'Route is working']);
    }
}
