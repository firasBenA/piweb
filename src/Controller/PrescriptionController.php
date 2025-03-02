<?php

namespace App\Controller;

use App\Entity\Prescription;
use App\Entity\User;
use App\Form\PrescriptionType;
use App\Repository\DiagnostiqueRepository;
use App\Repository\MedecinRepository;
use App\Repository\PatientRepository;
use App\Repository\PrescriptionRepository;
use App\Service\pdfService;
use App\Service\TwilioService;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
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
    private TwilioService $twilioService;



    public function __construct(EntityManagerInterface $entityManager, TwilioService $twilioService)
    {
        $this->entityManager = $entityManager;
        $this->twilioService = $twilioService;
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
        EntityManagerInterface $entityManager,
        Security $security,
        TwilioService $twilioService // Inject TwilioService
    ) {
        // Get the currently logged-in user (medecin)
        $user = $security->getUser();

        // Make sure the logged-in user is a medecin
        if (!$user || !in_array('ROLE_MEDECIN', $user->getRoles())) {
            throw $this->createAccessDeniedException('You are not authorized to create prescriptions.');
        }

        $diagnostiqueId = $request->query->get('diagnostique_id');

        if (!$diagnostiqueId) {
            return $this->redirectToRoute('home');
        }

        $diagnostique = $diagnostiqueRepository->find($diagnostiqueId);

        if (!$diagnostique) {
            throw $this->createNotFoundException('Diagnostique not found!');
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getUser(); // This is now the patient user

        $patientId = $dossierMedical->getUser()->getId();
        $patient = $entityManager->getRepository(User::class)->find($patientId);

        // Create a new Prescription instance
        $prescription = new Prescription();
        $prescription->setDiagnostique($diagnostique);
        $prescription->setDossierMedical($dossierMedical);
        $prescription->setMedecin($user); // Set the logged-in medecin as the prescriber
        $prescription->setPatient($patient); // Set the patient associated with the prescription
        $prescription->setDatePrescription(new \DateTime());

        // Create and handle the form
        $form = $this->createForm(PrescriptionType::class, $prescription);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Update the diagnostique status if needed
            $diagnostique->setStatus(1);

            // Persist the prescription
            $entityManager->persist($prescription);
            $entityManager->flush();

            // ✅ SEND SMS TO THE PATIENT
            $phoneNumber = '+216' . $patient->getTelephone();
            $message = 'Allez voir votre ordonnance!';
            if ($phoneNumber) {
                try {
                    $this->twilioService->sendSms($phoneNumber, $message);
                    return $this->redirectToRoute('PrescriptionMedecin_page', [
                        'id' => $patient->getId(),
                        'user' => $user
                    ]);
                } catch (\Exception $e) {
                    return new JsonResponse(['erroreeeee' => $e->getMessage()], 500);
                }
            }

            // Redirect to the patient's dossier
            return $this->redirectToRoute('PrescriptionMedecin_page', [
                'id' => $patient->getId(),
                'user' => $user
            ]);
        }

        // Render the form view
        return $this->render('prescription/new.html.twig', [
            'form' => $form->createView(),
            'diagnostique' => $diagnostique,
            'patient' => $patient,
            'user' => $user
        ]);
    }




    #[Route('/prescription/{id}/edit', name: 'app_prescription_edit')]
    public function editPrescription(
        Request $request,
        Prescription $prescription,
        EntityManagerInterface $entityManager,
        Security $security
    ) {

        $user = $security->getUser();

        $diagnostique = $prescription->getDiagnostique();

        if (!$diagnostique) {
            throw $this->createNotFoundException("Aucun diagnostique trouvé pour cette prescription.");
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getUser();

        $form = $this->createForm(PrescriptionType::class, $prescription);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('PrescriptionMedecin_page', [
                'id' => $patient->getId()
            ]);
        }

        return $this->render('prescription/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $user
        ]);
    }


    #[Route('/delete/{id}', name: 'app_prescription_delete', methods: ['POST'])]
    public function delete(Prescription $prescription, EntityManagerInterface $entityManager, Security $security): RedirectResponse
    {
        $user = $security->getUser();

        $diagnostique = $prescription->getDiagnostique();

        if (!$diagnostique) {
            throw $this->createNotFoundException("Aucun diagnostique trouvé pour cette prescription.");
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getUser();

        $entityManager->remove($prescription);
        $entityManager->flush();

        $this->addFlash('success', 'Prescription deleted successfully.');

        return $this->redirectToRoute('PrescriptionMedecin_page', [
            'id' => $patient->getId(),
            'user' => $user

        ]);
    }

    #[Route('/delete/{id}', name: 'admin_app_prescription_delete', methods: ['POST'])]
    public function deleteAdmin(Prescription $prescription, EntityManagerInterface $entityManager, Security $security): RedirectResponse
    {
        $user = $security->getUser();

        $diagnostique = $prescription->getDiagnostique();

        if (!$diagnostique) {
            throw $this->createNotFoundException("Aucun diagnostique trouvé pour cette prescription.");
        }

        $dossierMedical = $diagnostique->getDossierMedical();
        $patient = $dossierMedical->getUser();

        $entityManager->remove($prescription);
        $entityManager->flush();

        $this->addFlash('success', 'Prescription deleted successfully.');

        return $this->redirectToRoute('prescriptionAdmin', [
            'id' => $patient->getId(),
            'user' => $user

        ]);
    }


    #[Route('/{id}', name: "prescription_details", methods: ['GET'])]
    public function showPrescriptionDetails(
        Prescription $prescription,
        Security $security
    ): Response {
        $user = $security->getUser();


        return $this->render('prescription/details.html.twig', [
            'prescription' => $prescription,
            'user' => $user
        ]);
    }


    #[Route('/prescriptions/search', name: 'prescription_search', methods: ['GET'])]
    public function search(Request $request, PrescriptionRepository $prescriptionRepository, LoggerInterface $logger): JsonResponse
    {
        try {
            $searchTerm = $request->query->get('search', '');
            $logger->info('Search term: ' . $searchTerm); // Debugging

            // Search query adjusted to remove `user` filter
            if (!$searchTerm) {
                $prescriptions = $prescriptionRepository->findAll(); // Get all prescriptions
            } else {
                $prescriptions = $prescriptionRepository->createQueryBuilder('p')
                    ->where('p.titre LIKE :search')
                    ->setParameter('search', '%' . $searchTerm . '%')
                    ->getQuery()
                    ->getResult();
            }

            $data = [];
            foreach ($prescriptions as $prescription) {
                $data[] = [
                    'id' => $prescription->getId(),
                    'titre' => $prescription->getTitre(),
                    'contenue' => $prescription->getContenue(),
                    'date' => $prescription->getDatePrescription()->format('Y-m-d'), // Ensure correct method
                    'medecin' => ['nom' => $prescription->getMedecin()->getNom()],
                ];
            }

            return $this->json($data);
        } catch (\Exception $e) {
            $logger->error('Error in search: ' . $e->getMessage());
            return $this->json(['error' => 'An error occurred'], 500);
        }
    }


    #[Route('/download/{id}', name: 'prescription_download')]
    public function downloadPrescriptionPdf(int $id, pdfService $pdfService, PrescriptionRepository $prescriptionRepository): Response
    {
        $prescription = $prescriptionRepository->find($id);

        if (!$prescription) {
            throw $this->createNotFoundException('Prescription not found.');
        }

        $patient = $prescription->getDossierMedical()->getUser();

        $html = $this->renderView('pdf/prescription.html.twig', [
            'prescription' => $prescription,
            'patient' => $patient,
        ]);

        $pdfContent = $pdfService->generateBinaryPdf($html);

        return new Response(
            $pdfContent,
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="prescription_' . $prescription->getId() . '.pdf"',
            ]
        );
    }
}
