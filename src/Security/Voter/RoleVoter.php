<?php

namespace App\Security\Voter;

use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authorization\Voter\Voter;
use Symfony\Component\Security\Core\User\UserInterface;

final class RoleVoter extends Voter{
    protected function supports(string $attribute, mixed $subject): bool
    {
        // Vérifie si l'attribut correspond à un rôle attendu
        return in_array($attribute, ['PATIENT', 'MEDECIN']);
    }

    protected function voteOnAttribute(string $attribute, mixed $subject, TokenInterface $token): bool
    {
        $user = $token->getUser();

        if (!$user instanceof UserInterface) {
            return false;
        }

        // Vérifie si le rôle exact est présent dans la liste des rôles de l'utilisateur
        return in_array($attribute, $user->getRoles());
    }
}
