<?php

namespace App\Controller;

use App\Entity\Diagnostique;
use App\Entity\DossierMedical;
use App\Entity\Patient;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class MedecinController extends AbstractController
{


    #[Route('/medash', name: 'medecin_dashboard')]
    public function dashboard(Security $security): Response
    {
        // Fetch the currently authenticated user (Medecin)
        $user = $security->getUser();

        // Return the dashboard view for the medecin
        return $this->render('consultation/meddash.html.twig', [
            'medecin' => $user,
        ]);
    }
    
    #[Route('/infomed/{id}', name: 'infomed')]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $medecin = $entityManager->getRepository(User::class)->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        return $this->render('consultation/infomed.html.twig', [
            'medecin' => $medecin,
        ]);
    }


    /*#[Route('/medecin/dashboard/{id}', name: 'medecinDashboard_page')]
    public function dashboard(int $id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the doctor (medecin) by its ID
        $medecin = $entityManager->getRepository(Medecin::class)->find($id);

        if (!$medecin) {
            throw $this->createNotFoundException('Médecin non trouvé.');
        }

        // Fetch patients related to the doctor (medecin)
        $patients = $medecin->getPatients(); // Directly using the relation

        return $this->render('medecin/index.html.twig', [
            'medecin' => $medecin,
            'patients' => $patients,
        ]);
    }*/



    #[Route('/medecin/dossierMedicalByPatient/{id}', name: 'medecinDossierMedicalByPatient_page')]
    public function MedecinDossierMedical(int $id, EntityManagerInterface $entityManager): Response
    {
        // Fetch the patient by ID
        $patient = $entityManager->getRepository(Patient::class)->find($id);

        // Debugging - Log the patient object
        dump($patient);

        if (!$patient) {
            throw $this->createNotFoundException('No patient found with this ID.');
        }

        // Fetch the dossier medical linked to the patient
        $dossierMedical = $entityManager->getRepository(DossierMedical::class)->findOneBy(['patient' => $patient]);

        // Debugging - Log the dossierMedical object
        dump($dossierMedical);

        if (!$dossierMedical) {
            throw $this->createNotFoundException('No dossier medical found for this patient.');
        }

        // Fetch the diagnostiques related to the dossier medical
        $diagnostiques = $entityManager->getRepository(Diagnostique::class)->findBy(['dossierMedical' => $dossierMedical]);

        $prescriptions = $dossierMedical->getPrescriptions();
        $medecins = [];
        foreach ($prescriptions as $prescription) {
            if ($prescription->getMedecin() && !in_array($prescription->getMedecin(), $medecins, true)) {
                $medecins[] = $prescription->getMedecin();
            }
        }
        // Debugging - Log the diagnostiques
        dump($diagnostiques);

        return $this->render('medecin/dossierMedicalPatient.html.twig', [
            'dossierMedical' => $dossierMedical,
            'patient' => $patient, // Pass the patient to Twig
            'diagnostiques' => $diagnostiques, // Pass the diagnostiques to Twig
            'medecins' => $medecins,
        ]);
    }
    #[Route('/formMed', name: 'formMed_page')]
    public function formMed(): Response
    {
        return $this->render('medecin/form.html.twig', [
            'controller_name' => 'MedecinController',
        ]);

        /*
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class MedecinController extends AbstractController
{
    #[Route('/medecin', name: 'medecin_dashboard')]
    public function index(): Response
    {
        return $this->render('medecin_dashboard.html.twig');
    }

    #[Route('/medecin/update-profile', name: 'medecin_update_profile', methods: ['POST'])]
    public function updateProfile(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setEmail($request->request->get('email'));
        $user->setTelephone($request->request->get('telephone'));
        $user->setAdresse($request->request->get('adresse'));
        $user->setAge($request->request->get('age'));
        $user->setSexe($request->request->get('sexe'));
        $user->setSpecialite($request->request->get('specialite'));

        // Handle file uploads
        $certificatFile = $request->files->get('certificat');
        $imageProfilFile = $request->files->get('imageProfil');

        if ($certificatFile) {
            $certificatFileName = $this->uploadFile($certificatFile, $slugger, 'certificats_directory');
            $user->setCertificat($certificatFileName);
        }

        if ($imageProfilFile) {
            $imageProfilFileName = $this->uploadFile($imageProfilFile, $slugger, 'images_directory');
            $user->setImageProfil($imageProfilFileName);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        return $this->redirectToRoute('medecin_dashboard');
    }

    #[Route('/medecin/delete-profile', name: 'medecin_delete_profile')]
    public function deleteProfile(EntityManagerInterface $entityManager): Response
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        $entityManager->remove($user);
        $entityManager->flush();

        return $this->redirectToRoute('app_logout');
    }

    private function uploadFile($file, SluggerInterface $slugger, $directoryParameter): string
    {
        $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
        $safeFilename = $slugger->slug($originalFilename);
        $newFilename = $safeFilename.'-'.uniqid().'.'.$file->guessExtension();

        try {
            $file->move(
                $this->getParameter($directoryParameter),
                $newFilename
            );
        } catch (FileException $e) {
            // handle exception if something happens during file upload
            throw new \Exception('File upload error: ' . $e->getMessage());
        }

        return $newFilename;
    }*/
}}

