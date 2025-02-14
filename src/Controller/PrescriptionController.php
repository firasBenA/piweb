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

        // Get the associated DossierMedical and Patient
        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getPatient(); // Get the patient from the dossierMedical

        // Find a Medecin (modify the logic based on your requirements)
        $medecin = $medecinRepository->findOneBy([]);
        if (!$medecin) {
            throw $this->createNotFoundException('Medecin not found!');
        }

        // Create a new Prescription
        $prescription = new Prescription();
        $prescription->setDiagnostique($diagnostique);
        $prescription->setDossierMedical($dossierMedical);
        $prescription->setMedecin($medecin);
        $prescription->setDatePrescription(new \DateTime());

        // Create the form
        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // **Update the status of the Diagnostique to 1**
            $diagnostique->setStatus(1); // Assuming you have a `setStatus()` method in Diagnostique

            // Persist both the prescription and the updated Diagnostique
            $entityManager->persist($prescription);
            $entityManager->flush();

            // Redirect to the dossierMedicalByPatient_page with the patient's ID
            return $this->redirectToRoute('dossierMedicalByPatient_page', [
                'id' => $patient->getId()  // Use the patient ID from the DossierMedical
            ]);
        }

        return $this->render('prescription/new.html.twig', [
            'form' => $form->createView(),
            'diagnostique' => $diagnostique,
            'patient' => $patient, // Pass the patient to the template
        ]);
    }


    #[Route('/prescription/{id}/edit', name: 'app_prescription_edit')]
    public function editPrescription(Request $request, Prescription $prescription, EntityManagerInterface $entityManager)
    {
        // Process the form submission
        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Save the prescription (or any other processing you need)
            $entityManager->flush();

            // Redirect to the previous page
            $referer = $request->headers->get('referer');
            return new RedirectResponse($referer);
        }

        return $this->render('prescription/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/delete/{id}', name: 'app_prescription_delete', methods: ['POST'])]
    public function delete(PatientRepository $patientRepository, Prescription $prescription, EntityManagerInterface $entityManager): RedirectResponse
    {
        $entityManager->remove($prescription);
        $entityManager->flush();

        $this->addFlash('success', 'Prescription deleted successfully.');

        $patient = $patientRepository->findOneBy([]);

        return $this->redirectToRoute('dossierMedicalByPatient_page', [
            'id' => $patient->getId()
        ]);
    }
}
