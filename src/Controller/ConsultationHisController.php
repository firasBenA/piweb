<?php

// src/Controller/ConsultationHisController.php

namespace App\Controller;

use App\Entity\ConsultationHis;
use App\Entity\User;
use App\Form\ConsultationHisType; // Formulaire créé pour ConsultationHis
use App\Repository\ConsultationHisRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ConsultationHisController extends AbstractController
{
    #[Route('/conhis', name: 'consultationhis_create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        // Créer un nouvel objet ConsultationHis
        $consultationHis = new ConsultationHis();
        $consultationHis->setDate(new \DateTime());
        $consultationHis->setType("Consultation");
        $consultationHis->setPrix("80");

        // Créer et traiter le formulaire
        $form = $this->createForm(ConsultationHisType::class, $consultationHis);

        // Traitement du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Enregistrer l'entité ConsultationHis en base de données
            $em->persist($consultationHis);
            $em->flush();

            // Message de succès
            $this->addFlash('success', 'Consultation historique créé avec succès.');

            // Redirection vers une page (par exemple, la liste des consultations historiques)
            return $this->redirectToRoute('consultation_medecin_list');
        }

        return $this->render('consultation_his/hist.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // Optionnel : une méthode pour afficher la liste des consultations historiques
    #[Route('/consultationhis', name: 'consultationhis_list')]
    public function list(ConsultationHisRepository $repository): Response
    {
        // Récupérer toutes les consultations historiques
        $consultationsHis = $repository->findAll();

        return $this->render('consultationhis/list.html.twig', [
            'consultationsHis' => $consultationsHis,
        ]);
    }
}
