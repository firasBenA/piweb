<?php

namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\Reponse;
use App\Form\ReponseType;
use App\Repository\ReclamationRepository;
use App\Repository\UserRepository;
use App\Service\EmailServiceReclamtion;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Psr\Log\LoggerInterface;
use SendinBlue\Client\Configuration;
use SendinBlue\Client\Api\TransactionalEmailsApi;
use SendinBlue\Client\Model\SendSmtpEmail;

#[Route('/reponse')]
final class ResponseController extends AbstractController
{
    private $emailService;

    public function __construct(EmailServiceReclamtion $emailService)
    {
        $this->emailService = $emailService;
    }
    #[Route('/liste', name: 'reponse_reclamation_page')]
    public function index(EntityManagerInterface $entityManager, PaginatorInterface $paginator, Request $request): Response
    {
        $page = $request->query->getInt('page', 1); // Get the current page from the URL, default is 1
        $limit = 2; // Set the number of items per page to 2
    
        // Fetch the data
        $reclamations = $entityManager->getRepository(Reclamation::class)
            ->findBy([], ['date_debut' => 'DESC']);
    
        // Paginate the data
        $pagination = $paginator->paginate(
            $reclamations,
            $page,
            $limit // 2 items per page
        );
    
        // Pass pagination to the template
        return $this->render('reponse/liste.html.twig', [
            'pagination' => $pagination,
        ]);
    }
    
    

    #[Route('/ajouter/{id}', name: 'ajouter_reponse')]
    public function ajouter(Reclamation $reclamation, Request $request, EntityManagerInterface $entityManager): Response
    {
        $reponse = new Reponse();
        $form = $this->createForm(ReponseType::class, $reponse);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Set the response to the reclamation
            $reponse->setReclamation($reclamation);
            $reponse->setDateDeReponse(new \DateTime()); // Set the current date
    
            // Update the reclamation's status to 'traité'
            $reclamation->setEtat('traité');
            $entityManager->persist($reponse);
            $entityManager->persist($reclamation); // Update the reclamation
            $entityManager->flush();
    
            // Dynamically fetch the email address of the user (patient) linked to the reclamation
            $patient = $reclamation->getUser(); // Assuming you have a User associated with Reclamation
            $emailAddress = $patient->getEmail(); // Get the email address dynamically
            $subject = "Réclamation Traitée ";


            // HTML message with styling
            $message = "
               <body style=\"font-family: Arial, sans-serif; background-color: #f4f4f4; padding: 20px;\">
                    <div style=\"max-width: 600px; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0px 0px 10px rgba(0, 0, 0, 0.1); margin: auto;\">
                        <!-- Header avec une icône -->
                        <div style=\"text-align: center;\">
                            <h2 style=\"color: #333;\">Votre réclamation a été traitée ✅</h2>
                        </div>

                        <p style=\"font-size: 16px; color: #555;\">Bonjour,</p>
                        <p style=\"font-size: 16px; color: #555;\">Nous avons examiné votre réclamation et voici notre réponse :</p>

                        <!-- Contenu de la réponse avec une icône -->
                        <blockquote style=\"border-left: 5px solid #4CAF50; padding-left: 15px; font-style: italic; background: #f9f9f9; padding: 10px; border-radius: 5px;\">
                            <span style=\"font-size: 15px; color: #333;\">{$reponse->getContenu()}</span>
                        </blockquote>

                        <p style=\"font-size: 16px; color: #555;\">Si vous avez d'autres questions, n\'hésitez pas à nous contacter.</p>

                        <!-- Footer avec une icône -->
                        <div style=\"text-align: center; margin-top: 20px;\">
                            <p style=\"font-size: 14px; color: #777;\">
                                <a href=\"mailto:support@votreentreprise.com\" style=\"color: #4CAF50; text-decoration: none;\">support@votreentreprise.com</a>
                            </p>
                        </div>

                        <p style=\"font-size: 14px; color: #777; text-align: center;\">Merci de nous faire confiance !<br><strong>Service Support</strong></p>
                    </div>
                </body>;
            ";  
    
            // Try to send the email
            try {
                $this->emailService->sendEmail($emailAddress, $subject, $message);
                $this->addFlash('success', 'Email sent successfully!'); // Set success message
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error sending email: ' . $e->getMessage()); // Set error message
            }
            
            // Redirect to the response reclamation page
            
      
            // Flash message to notify success
            $this->addFlash('success', 'Réponse ajoutée avec succès et réclamation marquée comme traitée !');
            return $this->redirectToRoute('reponseRecAdmin');
        }
    
        return $this->render('reponse/ajouter.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}    