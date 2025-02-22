<?php

namespace App\Controller;

use App\Entity\DossierMedical;
use App\Entity\Medecin;
use App\Entity\Patient;
use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Security\AppAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, Security $security, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role = $form->get('role')->getData();
            $user->setRoles([$role]);

            // Hash the password
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($userPasswordHasher->hashPassword($user, $plainPassword));
            $user->setIsVerified(false);

            // Persist the user
            $entityManager->persist($user);

            // If the user is a patient, create and persist the associated entities
            if ($role === 'ROLE_PATIENT') {
                $patient = new Patient();
                $patient->setNom($form->get('nom')->getData());
                $patient->setPrenom($form->get('prenom')->getData());
                $patient->setEmail($form->get('email')->getData());
                $patient->setMotDePasse($plainPassword);
                $patient->setAge($form->get('age')->getData());
                $patient->setUser($user);

                // Create and associate DossierMedical
                $dossierMedical = new DossierMedical();
                $dossierMedical->setPatient($patient);
                $dossierMedical->setDatePrescription(new \DateTime());
                $patient->setDossierMedical($dossierMedical);

                $entityManager->persist($patient);
            }

            // If the user is a doctor, create and persist the Medecin entity
            if ($role === 'ROLE_MEDECIN') {
                $medecin = new Medecin();
                $medecin->setNom($form->get('nom')->getData());
                $medecin->setPrenom($form->get('prenom')->getData());
                $medecin->setEmail($form->get('email')->getData());
                $medecin->setUser($user);
                $medecin->setMotDePasse($plainPassword);
                $medecin->setAge($form->get('age')->getData());

                $entityManager->persist($medecin);
            }

            $entityManager->flush();

            // Log the user in
            return $security->login($user, AppAuthenticator::class, 'main');
        }

        return $this->render('registration/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
}
