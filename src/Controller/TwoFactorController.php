<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;
use Symfony\Component\Mailer\MailerInterface;

class TwoFactorController extends AbstractController
{
    private RequestStack $requestStack;

    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
    }

    #[Route('/2fa', name: '2fa_login')]
    public function login(AuthenticationUtils $authenticationUtils): Response
    {
        $session = $this->requestStack->getSession();

        $error = $authenticationUtils->getLastAuthenticationError();

        return $this->render('security/2fa_login.html.twig', [
            'error' => $error,
            'userEmail' => $session->get('2fa_user_email'),
        ]);
    }

    #[Route("/send-2fa-code", name: "send_2fa_code")]
    public function send2FACode(MailerInterface $mailer): Response
    {
        $session = $this->requestStack->getSession();

        $email = $session->get('2fa_user_email');
        if (!$email) {
            $this->addFlash('error', 'Aucun utilisateur en cours.');
            return $this->redirectToRoute('app_login2');
        }

        $code = rand(100000, 999999);
        $session->set('2fa_code', $code);
        $session->set('2fa_expiry', (new \DateTime('+5 minutes'))->getTimestamp());

        try {
            $emailMessage = (new \Symfony\Component\Mime\Email())
                ->from('no-reply@example.com')
                ->to($email)
                ->subject('Votre Code de Vérification 2FA')
                ->html($this->renderView('security/2fa_email.html.twig', ['code' => $code]));

            $mailer->send($emailMessage);

            $this->addFlash('success', 'Un nouveau code a été envoyé à votre adresse e-mail.');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Impossible d\'envoyer le code. Veuillez réessayer.');
        }

        return $this->redirectToRoute('2fa_login');
    }

    #[Route("/verify-2fa-code", name: "verify_2fa_code", methods: ["POST"])]
    public function verify2FACode(Request $request): Response
    {
        $session = $this->requestStack->getSession();
    
        // Validate CSRF token
        $csrfToken = $request->request->get('_csrf_token');
        if (!$this->isCsrfTokenValid('verify_2fa', $csrfToken)) {
            $this->addFlash('error', 'Jeton CSRF invalide.');
            return $this->redirectToRoute('2fa_login');
        }
    
        $submittedCode = trim($request->request->get('auth_code'));
    
        if (!$submittedCode) {
            $this->addFlash('error', 'Veuillez entrer le code reçu par e-mail.');
            return $this->redirectToRoute('2fa_login');
        }
    
        $storedCode = $session->get('2fa_code');
        $expiry = $session->get('2fa_expiry');
    
        // Debug logging
        error_log("Submitted Code: $submittedCode, Stored Code: $storedCode, Expiry: $expiry, Current Time: " . time());
    
        if (!$storedCode || !$expiry) {
            $this->addFlash('error', 'Aucun code valide trouvé. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login2');
        }
    
        if ((int)$submittedCode !== (int)$storedCode) {
            $this->addFlash('error', 'Code incorrect.');
            return $this->redirectToRoute('2fa_login');
        }
    
        if (time() > $expiry) {
            $this->addFlash('error', 'Le code a expiré.');
            return $this->redirectToRoute('2fa_login');
        }
    
        $user = $this->getUser();
        if (!$user) {
            $this->addFlash('error', 'Session expirée. Veuillez vous reconnecter.');
            return $this->redirectToRoute('app_login2');
        }
    
        // Set 2FA flag and ensure session is saved
        $session->set('custom_2fa_authenticated', true);
        $session->remove('2fa_code');
        $session->remove('2fa_expiry');
        $session->remove('2fa_user_email');
        $session->save();
    
        // Redirect based on role
        if (in_array('ROLE_MEDECIN', $user->getRoles())) {
            $this->addFlash('success', 'Connexion réussie en tant que médecin.');
            return $this->redirectToRoute('medecin_dashboard');
        } elseif (in_array('ROLE_PATIENT', $user->getRoles())) {
            $this->addFlash('success', 'Connexion réussie en tant que patient.');
            return $this->redirectToRoute('patient_dashboard');
        } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
            $this->addFlash('success', 'Connexion réussie en tant qu\'administrateur.');
            return $this->redirectToRoute('admin_dashboard');
        }
    
        $this->addFlash('success', 'Connexion réussie.');
        return $this->redirectToRoute('home_page');
    }


}