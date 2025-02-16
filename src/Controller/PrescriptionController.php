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
        // Safely get the diagnostique_id from the query parameters
        $diagnostiqueId = $request->query->get('diagnostique_id');

        // Check if diagnostique_id is not set or is invalid
        if (!$diagnostiqueId) {
            // Redirect to an appropriate route or handle the error (e.g., home page or an error message)
            return $this->redirectToRoute('home');
        }

        // Find the Diagnostique by ID
        $diagnostique = $diagnostiqueRepository->find($diagnostiqueId);

        // If Diagnostique is not found, throw a 404 exception
        if (!$diagnostique) {
            throw $this->createNotFoundException('Diagnostique not found!');
        }

        // Get associated DossierMedical and Patient
        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getPatient();

        // Find a Medecin (Modify as per your business logic)
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

        // Create the form and pass the diagnostique_id as an option
        $form = $this->createForm(PrescriptionType::class, $prescription);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the Diagnostique status (if necessary)
            $diagnostique->setStatus(1); // Modify as needed

            // Persist both Prescription and updated Diagnostique
            $entityManager->persist($prescription);
            $entityManager->flush();

            // Redirect to patient's dossierMedical page (or another appropriate page)
            return $this->redirectToRoute('medecinDossierMedicalByPatient_page', [
                'id' => $patient->getId()
            ]);
        }

        // Return the view with form and relevant data
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
    // Ensure we have the Diagnostique associated with this Prescription
    $diagnostique = $prescription->getDiagnostique(); // Get existing diagnostique
    
    if (!$diagnostique) {
        throw $this->createNotFoundException("Aucun diagnostique trouvé pour cette prescription.");
    }

    $dossierMedical = $diagnostique->getDossierMedical();
    $patient = $dossierMedical->getPatient();

    // Create the form and disable diagnostique field
    $form = $this->createForm(PrescriptionType::class, $prescription);

    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush(); // No need to persist, it's already managed
        
        return $this->redirectToRoute('medecinDossierMedicalByPatient_page', [
            'id' => $patient->getId()
        ]);
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


    #[Route('/{id}', name: "prescription_details", methods: ['GET'])]
    public function showPrescriptionDetails(Prescription $prescription): Response
    {
        return $this->render('prescription/details.html.twig', [
            'prescription' => $prescription,
        ]);
    }
}
