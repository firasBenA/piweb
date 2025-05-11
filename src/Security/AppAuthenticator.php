<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\HttpFoundation\Session\RequestSession;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    public const LOGIN_ROUTE = 'app_login2';

    public function __construct(
        private UrlGeneratorInterface $urlGenerator,
        private EntityManagerInterface $em,
        private UserPasswordHasherInterface $passwordHasher,
        private RequestStack $requestStack
    ) {
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->getPayload()->getString('email');
        $password = $request->getPayload()->getString('password');

        if (!$email || !$password) {
            throw new CustomUserMessageAuthenticationException('Veuillez remplir tous les champs.');
        }

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Aucun compte trouvé avec cet email.');
        }

        // Account lock check
        if ($user->getLockUntil() && $user->getLockUntil() > new \DateTime()) {
            $now = new \DateTime();
            $interval = $now->diff($user->getLockUntil());
            $remainingTime = $interval->format('%i minutes et %s secondes');
            throw new CustomUserMessageAuthenticationException('Votre compte est bloqué. Veuillez réessayer dans : ' . $remainingTime);
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $user->setFailedLoginAttempts($user->getFailedLoginAttempts() + 1);
            if ($user->getFailedLoginAttempts() >= 3) {
                $user->setLockUntil(new \DateTime('+15 minutes'));
            }
            $this->em->flush();
            throw new CustomUserMessageAuthenticationException('Mot de passe incorrect.');
        }

        // Reset failed attempts
        $user->setFailedLoginAttempts(0);
        $user->setLockUntil(null);
        $this->em->flush();

        // Generate 2FA code and store in session
        $code = rand(100000, 999999);

        $session = $this->requestStack->getSession();
        $session->set('2fa_code', $code);
        $session->set('2fa_expiry', (new \DateTime('+5 minutes'))->getTimestamp());
        $session->set('2fa_user_email', $user->getEmail());
        $session->save();

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
            ]
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
{
    $session = $this->requestStack->getSession();

    // If already completed 2FA, go to dashboard
    if ($session->get('custom_2fa_authenticated', false)) {
        $session->remove('custom_2fa_authenticated');
        $session->save();

        $user = $token->getUser();

        if (in_array('ROLE_MEDECIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('medecin_dashboard'));
        } elseif (in_array('ROLE_PATIENT', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('patient_dashboard'));
        } elseif (in_array('ROLE_ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        return new RedirectResponse($this->urlGenerator->generate('home_page'));
    }

    // Only redirect to 2FA if 2FA hasn't been completed yet
    if (!$session->has('2fa_code')) {
        // Optional: Handle edge case where session expired
        $session->getFlashBag()->add('error', 'Session expirée. Veuillez vous reconnecter.');
        return new RedirectResponse($this->urlGenerator->generate('app_login2'));
    }

    return new RedirectResponse($this->urlGenerator->generate('2fa_login'));
}

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}