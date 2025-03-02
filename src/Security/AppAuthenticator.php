<?php

namespace App\Security;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Authenticator\AbstractLoginFormAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\CsrfTokenBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\RememberMeBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Http\SecurityRequestAttributes;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppAuthenticator extends AbstractLoginFormAuthenticator
{
    use TargetPathTrait;

    public const LOGIN_ROUTE = 'app_login2';

    

    public function __construct(private UrlGeneratorInterface $urlGenerator,
    private EntityManagerInterface $em,
    private UserPasswordHasherInterface $passwordHasher)
    {
    }

    public function authenticate(Request $request): Passport
    {
      /*  $email = $request->getPayload()->getString('email');

        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );*/

        $email = $request->getPayload()->getString('email');
        $password = $request->getPayload()->getString('password');

        if (!$email && !$password) {
            throw new CustomUserMessageAuthenticationException('Veuillez remplir les champs email et mot de passe.');
        }
        if (!$email) {
            throw new CustomUserMessageAuthenticationException('l\'email ne peut pas étre vide.');
        }

        if (!$password) {
            throw new CustomUserMessageAuthenticationException('le mot de passe ne peut pas étre vide.');
        }


        $request->getSession()->set(SecurityRequestAttributes::LAST_USERNAME, $email);

        $user = $this->em->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            throw new CustomUserMessageAuthenticationException('Aucun compte trouvé avec cet email.');
        }

        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            $user ->setFailedLoginAttempts($user ->getFailedLoginAttempts() + 1);
            if($user->getFailedLoginAttempts() >= 3) {
                $user ->setLockUntil(new \DateTime('+15 minutes'));
            }
            $this->em->flush();
            throw new CustomUserMessageAuthenticationException('Mot de passe incorrect.');
        }
        if ($user->getLockUntil() && $user->getLockUntil() > new \DateTime()) {
            $now = new \DateTime();
            $interval = $now->diff($user->getLockUntil());
            
            // Formatage du temps restant
            $remainingTime = $interval->format('%i minutes et %s secondes');
            
            throw new CustomUserMessageAuthenticationException('Votre compte est bloqué. Veuillez réessayer dans : ' . $remainingTime);
        }
       

        $user->setFailedLoginAttempts(0);
        $user->setLockUntil(null);
        $this->em->flush();





        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($request->getPayload()->getString('password')),
            [
                new CsrfTokenBadge('authenticate', $request->getPayload()->getString('_csrf_token')),
                new RememberMeBadge(),
            ]
        );

    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        $user = $token->getUser();

        if (in_array('MEDECIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('medecin_dashboard'));
        } elseif (in_array('PATIENT', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('patient_dashboard'));
        } elseif (in_array('ADMIN', $user->getRoles())) {
            return new RedirectResponse($this->urlGenerator->generate('admin_dashboard'));
        }

        return new RedirectResponse($this->urlGenerator->generate('home_page')); // Fallback
    }

    protected function getLoginUrl(Request $request): string
    {
        return $this->urlGenerator->generate(self::LOGIN_ROUTE);
    }
}
 