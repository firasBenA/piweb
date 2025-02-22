<?php

namespace App\Controller;

use App\Entity\Prescription;
use App\Form\PrescriptionType;
use App\Repository\DiagnostiqueRepository;
use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use App\Repository\PrescriptionRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[Route('/prescription')]
class PrescriptionController extends AbstractController
{

    private EntityManagerInterface $entityManager;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/', name: 'app_prescription_index', methods: ['GET'])]
    public function index(PrescriptionRepository $prescriptionRepository): Response
    {
        return $this->render('prescription/index.html.twig', [
            'prescriptions' => $prescriptionRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_prescription_new')]
    public function new(
        Request $request,
        DiagnostiqueRepository $diagnostiqueRepository,
        MedecinRepository $medecinRepository,
        EntityManagerInterface $entityManager
    ) {
        $diagnostiqueId = $request->query->get('diagnostique_id');

        if (!$diagnostiqueId) {
            return $this->redirectToRoute('home');
        }

        $diagnostique = $diagnostiqueRepository->find($diagnostiqueId);

        if (!$diagnostique) {
            throw $this->createNotFoundException('Diagnostique not found!');
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getPatient();

        $medecin = $medecinRepository->findOneBy([]);
        if (!$medecin) {
            throw $this->createNotFoundException('Medecin not found!');
        }

        $prescription = new Prescription();
        $prescription->setDiagnostique($diagnostique);
        $prescription->setDossierMedical($dossierMedical);
        $prescription->setMedecin($medecin);
        $prescription->setDatePrescription(new \DateTime());

        $form = $this->createForm(PrescriptionType::class, $prescription);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $diagnostique->setStatus(1); 

            $entityManager->persist($prescription);
            $entityManager->flush();

            return $this->redirectToRoute('medecinDossierMedicalByPatient_page', [
                'id' => $patient->getId()
            ]);
        }

        return $this->render('prescription/new.html.twig', [
            'form' => $form->createView(),
            'diagnostique' => $diagnostique,
            'patient' => $patient,
        ]);
    }



    #[Route('/prescription/{id}/edit', name: 'app_prescription_edit')]
    public function editPrescription(
        Request $request,
        Prescription $prescription,
        EntityManagerInterface $entityManager,
        DiagnostiqueRepository $diagnostiqueRepository
    ) {
        $diagnostique = $prescription->getDiagnostique();

        if (!$diagnostique) {
            throw $this->createNotFoundException("Aucun diagnostique trouvé pour cette prescription.");
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getPatient();

        $form = $this->createForm(PrescriptionType::class, $prescription);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush(); 

            return $this->redirectToRoute('medecinDossierMedicalByPatient_page', [
                'id' => $patient->getId()
            ]);
        }

        return $this->render('prescription/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }


    #[Route('/delete/{id}', name: 'app_prescription_delete', methods: ['POST'])]
    public function delete(Prescription $prescription, EntityManagerInterface $entityManager): RedirectResponse
    {
        $diagnostique = $prescription->getDiagnostique();

        if (!$diagnostique) {
            throw $this->createNotFoundException("Aucun diagnostique trouvé pour cette prescription.");
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getPatient();

        $entityManager->remove($prescription);
        $entityManager->flush();

        $this->addFlash('success', 'Prescription deleted successfully.');

        return $this->redirectToRoute('medecinDossierMedicalByPatient_page', [
            'id' => $patient->getId()
        ]);
    }


    #[Route('/{id}', name: "prescription_details", methods: ['GET'])]
    public function showPrescriptionDetails(Prescription $prescription): Response
    {
        return $this->render('prescription/details.html.twig', [
            'prescription' => $prescription,
        ]);
    }
}
