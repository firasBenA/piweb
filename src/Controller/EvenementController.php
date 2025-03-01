<?php

namespace App\Controller;

use App\Entity\Evenement;
use App\Form\EvenementType;
use App\Repository\EvenementRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Knp\Component\Pager\PaginatorInterface;


#[Route('/evenement')]
final class EvenementController extends AbstractController
{
    #[Route(name: 'app_evenement_index', methods: ['GET'])]
    public function index(
        Request $request,
        EvenementRepository $evenementRepository,
        PaginatorInterface $paginator
    ): Response {
        // Fetch query without executing it
        $query = $evenementRepository->createQueryBuilder('e')->getQuery();
    
        // Paginate the results, limiting to 3 events per page
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1), // Get page number from request, default to 1
            4 // Limit to 3 events per page
        );
    
        return $this->render('evenement/index.html.twig', [
            'evenements' => $pagination,
        ]);
    }

    #[Route('/ajouter', name: 'app_evenement_ajouter', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $evenement = new Evenement();
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($evenement);
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/new.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_afficher', methods: ['GET'])]
    public function show(Evenement $evenement): Response
    {
        return $this->render('evenement/show.html.twig', [
            'evenement' => $evenement,
        ]);
    }

    #[Route('/{id}/modifier', name: 'app_evenement_modifier', methods: ['GET', 'POST'])]
    public function edit(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(EvenementType::class, $evenement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('evenement/edit.html.twig', [
            'evenement' => $evenement,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_evenement_delete', methods: ['POST'])]
    public function delete(Request $request, Evenement $evenement, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $evenement->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($evenement);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_evenement_index', [], Response::HTTP_SEE_OTHER);
    }

    
    #[Route('/participate/{id}', name: 'app_evenement_participate', methods: ['POST'])]
    #[IsGranted('ROLE_PATIENT')]
    public function participate(Evenement $evenement, EntityManagerInterface $entityManager, Request $request): JsonResponse
    {
        $user = $this->getUser();
        

        if (!$user) {
            return new JsonResponse(['error' => 'Utilisateur non connecté'], 403);
        }

        if ($evenement->getUsers()->contains($user)) {
            $evenement->removeUser($user);
            $participating = false;
        } else {
            $evenement->addUser($user);
            $participating = true;
        }

        $entityManager->persist($evenement);
        $entityManager->flush();

        return new JsonResponse(['participating' => $participating]);
        
    }
    
    #[Route('participer/{id}', name:"app_evenement_participer")]

    public function participer(Evenement $evenement): Response
{
    return $this->render('evenement/participer.html.twig', [
        'evenement' => $evenement
    ]);
}


}
