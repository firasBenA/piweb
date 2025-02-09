<?php

namespace App\Controller;

use App\Entity\Prescription;
use App\Entity\DossierMedical;
use App\Entity\Diagnostique;
use App\Repository\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/prescription')]
class PrescriptionController extends AbstractController
{
    #[Route('/', name: 'prescription_index', methods: ['GET'])]
    public function index(PrescriptionRepository $prescriptionRepository): Response
    {
        return $this->json($prescriptionRepository->findAll());
    }

    #[Route('/{id}', name: 'prescription_show', methods: ['GET'])]
    public function show(Prescription $prescription): Response
    {
        return $this->json($prescription);
    }

    #[Route('/create', name: 'prescription_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        
        $prescription = new Prescription();
        $prescription->setContenue($data['contenue']);
        $prescription->setDatePrscription(new \DateTime($data['date_prscription']));
        
        if (isset($data['dossierMedicalId'])) {
            $dossierMedical = $em->getRepository(DossierMedical::class)->find($data['dossierMedicalId']);
            $prescription->setDossierMedical($dossierMedical);
        }
        
        if (isset($data['diagnostiqueId'])) {
            $diagnostique = $em->getRepository(Diagnostique::class)->find($data['diagnostiqueId']);
            $prescription->setDiagnostique($diagnostique);
        }
        
        $em->persist($prescription);
        $em->flush();

        return $this->json($prescription, Response::HTTP_CREATED);
    }

    #[Route('/{id}/update', name: 'prescription_update', methods: ['PUT'])]
    public function update(Request $request, Prescription $prescription, EntityManagerInterface $em): Response
    {
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['contenue'])) {
            $prescription->setContenue($data['contenue']);
        }
        
        if (isset($data['date_prscription'])) {
            $prescription->setDatePrscription(new \DateTime($data['date_prscription']));
        }
        
        $em->flush();

        return $this->json($prescription);
    }

    #[Route('/{id}', name: 'prescription_delete', methods: ['DELETE'])]
    public function delete(Prescription $prescription, EntityManagerInterface $em): Response
    {
        $em->remove($prescription);
        $em->flush();

        return $this->json(['message' => 'Prescription supprimée'], Response::HTTP_NO_CONTENT);
    }
}
