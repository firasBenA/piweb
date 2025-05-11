<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    #[Route('/admin', name: 'admin_dashboard')]
    public function index(Security $security, UserRepository $userRepository): Response
    {
        // Get the logged-in user
        $user = $security->getUser();
    
        // Get the list of medecins and patients
        $medecins = $userRepository->findByRoles('ROLE_MEDECIN');
        $patients = $userRepository->findByRoles('ROLE_PATIENT');
    
        // Debug: Check the data fetched
        dump($medecins, $patients);
    
        return $this->render('admin/index.html.twig', [
            'user' => $user,
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/users', name: 'admin_users', methods: ['GET'])]
    public function listUsers(UserRepository $userRepository): Response
    {
        // Get the list of medecins and patients
        $medecins = $userRepository->findByRoles('ROLE_MEDECIN');
        $patients = $userRepository->findByRoles('ROLE_PATIENT');

        // Render the Twig template
        return $this->render('admin/index.html.twig', [
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/admin/users/{id}/block', name: 'admin_block_user', methods: ['POST'])]
    public function blockUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Get the current roles of the user
        $currentRoles = $user->getRoles();
    
        // If the user is not already blocked, store their original role
        if (!in_array('ROLE_BLOCKED', $currentRoles)) {
            // Assume the first role is the primary role (ROLE_MEDECIN or ROLE_PATIENT)
            $originalRole = $currentRoles[0] ?? 'ROLE_PATIENT'; // Default to ROLE_PATIENT if no role
            // Ensure the original role is valid
            if (!in_array($originalRole, ['ROLE_MEDECIN', 'ROLE_PATIENT'])) {
                return $this->json([
                    'status' => 'error',
                    'message' => 'Rôle d\'origine non valide.',
                ], 400);
            }
            // Set roles to ["ROLE_BLOCKED", "ORIGINAL_ROLE_MEDECIN"] or ["ROLE_BLOCKED", "ORIGINAL_ROLE_PATIENT"]
            $user->setRoles(['ROLE_BLOCKED', 'ORIGINAL_ROLE_' . substr($originalRole, 5)]); // Remove "ROLE_" prefix
        }
    
        // Persist changes
        $entityManager->flush();
    
        return $this->json([
            'status' => 'success',
            'message' => 'Utilisateur bloqué avec succès.',
            'newRoles' => $user->getRoles(),
            'userId' => $user->getId(),
        ]);
    }
    
    #[Route('/admin/users/{id}/unblock', name: 'admin_unblock_user', methods: ['POST'])]
    public function unblockUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Get all roles of the user
        $currentRoles = $user->getRoles();
    
        // Find the original role (if it exists)
        $originalRole = null;
        foreach ($currentRoles as $role) {
            if (strpos($role, 'ORIGINAL_ROLE_') === 0) {
                $originalRole = 'ROLE_' . substr($role, 14); // Reconstruct with ROLE_ prefix
                break;
            }
        }
    
        // If no original role is found, assign a default role
        if (!$originalRole) {
            $originalRole = 'ROLE_PATIENT'; // Default role
        }
    
        // Ensure the restored role is valid
        if (!in_array($originalRole, ['ROLE_MEDECIN', 'ROLE_PATIENT'])) {
            return $this->json([
                'status' => 'error',
                'message' => 'Rôle d\'origine non valide pour le déblocage.',
            ], 400);
        }
    
        // Unblock the user and restore the original role
        $user->setRoles([$originalRole]);
        $entityManager->flush();
    
        return $this->json([
            'status' => 'success',
            'message' => 'Utilisateur débloqué avec succès.',
            'newRoles' => $user->getRoles(),
            'userId' => $user->getId(),
        ]);
    }
}