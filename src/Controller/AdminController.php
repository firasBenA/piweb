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
    #[Route('/', name: 'admin_dashboard')]
    public function index(Security $security, UserRepository $userRepository): Response
    {
        // Get the logged-in user
        $user = $security->getUser();
    
        // Get the list of medecins and patients
        $medecins = $userRepository->findByRoles('MEDECIN');
        $patients = $userRepository->findByRoles('PATIENT');
    
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
        $medecins = $userRepository->findByRoles('MEDECIN');
        $patients = $userRepository->findByRoles('PATIENT');

        // Render the Twig template
        return $this->render('admin/index.html.twig', [
            'medecins' => $medecins,
            'patients' => $patients,
        ]);
    }

    #[Route('/admin/users/{id}/block', name: 'admin_block_user', methods: ['POST'])]
    public function blockUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer le rôle actuel de l'utilisateur
        $currentRoles = $user->getRoles();
    
        // Si l'utilisateur n'est pas déjà bloqué, stocker son rôle actuel dans un rôle personnalisé
        if (!in_array('BLOCKED', $currentRoles)) {
            // Ajouter un rôle personnalisé pour stocker le rôle d'origine
            $originalRole = $currentRoles[0]; // Rôle d'origine (MEDECIN ou PATIENT)
            $user->setRoles(['BLOCKED', 'ORIGINAL_ROLE_' . $originalRole]);
        }
    
        // Bloquer l'utilisateur
        $entityManager->flush();
    
        return $this->json([
            'status' => 'User blocked successfully',
            'newRole' => 'BLOCKED',
            'userId' => $user->getId(),
        ]);
    }
    
    #[Route('/admin/users/{id}/unblock', name: 'admin_unblock_user', methods: ['POST'])]
    public function unblockUser(User $user, EntityManagerInterface $entityManager): JsonResponse
    {
        // Récupérer tous les rôles de l'utilisateur
        $currentRoles = $user->getRoles();
    
        // Trouver le rôle d'origine (s'il existe)
        $originalRole = null;
        foreach ($currentRoles as $role) {
            if (strpos($role, 'ORIGINAL_ROLE_') === 0) {
                $originalRole = str_replace('ORIGINAL_ROLE_', '', $role);
                break;
            }
        }
    
        // Si aucun rôle d'origine n'est trouvé, attribuer un rôle par défaut (PATIENT ou MEDECIN)
        if (!$originalRole) {
            $originalRole = 'PATIENT'; // ou une autre logique pour déterminer le rôle par défaut
        }
    
        // Débloquer l'utilisateur et lui redonner son rôle d'origine
        $user->setRoles([$originalRole]);
        $entityManager->flush();
    
        return $this->json([
            'status' => 'User unblocked successfully',
            'newRole' => $originalRole,
            'userId' => $user->getId(),
        ]);
    }

}
