<?php
namespace App\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\User;

class NotificationController extends AbstractController
{
    #[Route('/notifications', name: 'notifications')]
    public function notifications(EntityManagerInterface $entityManager): StreamedResponse
    {
        $response = new StreamedResponse(function () use ($entityManager) {
            $lastUserId = 0; // Initialise à 0 au lieu de null

            while (true) {
                // Récupère les nouveaux utilisateurs
                $newUsers = $entityManager->getRepository(User::class)
                   ->createQueryBuilder('u')
                   ->select('u.id', 'u.email', 'u.createdAt')
                   ->where('u.id > :lastUserId')
                   ->setParameter('lastUserId', $lastUserId)
                   ->orderBy('u.createdAt', 'DESC')
                   ->setMaxResults(10) // Limite à 10 résultats
                   ->getQuery()
                   ->useQueryCache(true)
                   ->getResult();

                // Envoie une notification pour chaque nouvel utilisateur
                foreach ($newUsers as $user) {
                    $lastUserId = $user->getId();

                    $newUser = [
                        'message' => 'Nouvel utilisateur : ' . $user->getEmail(),
                        'timestamp' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
                    ];

                    echo "data: " . json_encode($newUser) . "\n\n";
                    ob_flush();
                    flush();

                    // Vérifie si la connexion est toujours active
                    if (connection_aborted()) {
                        break 2;
                    }
                }

                sleep(5); 
            }
        });

        $response->headers->set('Content-Type', 'text/event-stream');
        $response->headers->set('Cache-Control', 'no-cache');
        $response->headers->set('Connection', 'keep-alive');

        return $response;
    }
}