<?php

namespace App\Controller;

use App\Entity\RendezVous;
use App\Entity\Patient;
use App\Entity\Medecin;
use App\Form\RendezVousType;
use App\Form\ModifConType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class ConsultationController extends AbstractController
{
    #[Route('/consultation', name: 'app_consultation')]
    public function index(): Response
    {
        return $this->render('consultation/index.html.twig', [
            'controller_name' => 'ConsultationController',
        ]);
    }

    #[Route('/listcon/{id}/', name: 'consultation_medecin_list')]
public function listForMedecin(Medecin $medecin, EntityManagerInterface $entityManager): Response
{
    $rendezVous = $entityManager->getRepository(RendezVous::class)->findBy(['medecin' => $medecin]);

    return $this->render('consultation/listcon.html.twig', [
        'medecin' => $medecin,
        'rendezVous' => $rendezVous,
    ]);
}



#[Route('/approuver/{id}', name: 'rendezvous_approuver')]
public function approuverRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
{
    
    $rendezVous->setStatut('Approuvé');
    
    $entityManager->flush();

    
    $this->addFlash('success', 'Le rendez-vous a été approuvé.');

    
    return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
}


#[Route('/refuser/{id}', name: 'rendezvous_refuser')]
public function refuserRendezVous(RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
{
    
    $rendezVous->setStatut('Refusé');
    $entityManager->flush();

    $this->addFlash('danger', 'Le rendez-vous a été refusé.');

    return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
}

#[Route('/modifier/{id}', name: 'rendezvous_modifier')]
    public function modifierRendezVous(Request $request, RendezVous $rendezVous, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ModifConType::class, $rendezVous);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $newDate = $rendezVous->getDate();

            if ($newDate < new \DateTime('today')) {
                $this->addFlash('error', 'La date choisie ne peut pas être antérieure à aujourd\'hui.');
                return $this->redirectToRoute('rendezvous_modifier', ['id' => $rendezVous->getId()]);
            }

            $rendezVous->setStatut('Approuvé');
            $entityManager->flush();

            $this->addFlash('success', 'Le rendez-vous a été modifié et approuvé.');

            return $this->redirectToRoute('consultation_medecin_list', ['id' => $rendezVous->getMedecin()->getId()]);
        }

        return $this->render('consultation/modifier.html.twig', [
            'form' => $form->createView(),
        ]);
    }

}
