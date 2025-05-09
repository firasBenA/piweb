<?php

namespace App\Security;

use Symfony\Component\PasswordHasher\PasswordHasherInterface;

class JBCryptPasswordHasher implements PasswordHasherInterface
{
    private int $cost;

    public function __construct(int $cost = 10) // Changed to 10 to match JavaFX
    {
        $this->cost = $cost;
    }

    public function hash(string $plainPassword, ?string $salt = null): string
{
    // Don't allow salt input (deprecated and insecure)
    if (null !== $salt) {
        throw new \InvalidArgumentException('Providing a salt manually is not supported.');
    }

    $hash = password_hash($plainPassword, PASSWORD_BCRYPT, ['cost' => $this->cost]);

    if (!$hash) {
        throw new \RuntimeException('Failed to hash password with BCrypt.');
    }

    // Replace $2y$ with $2a$ for compatibility with other systems
    $hash = str_replace('$2y$', '$2a$', $hash);

    // Validate the final hash structure
    if (!preg_match('/^\$2a\$\d{2}\$[.\/A-Za-z0-9]{53}$/', $hash)) {
        throw new \RuntimeException('Generated invalid BCrypt hash: ' . $hash);
    }

    return $hash;
}

public function verify(string $hashedPassword, string $plainPassword, ?string $salt = null): bool
{
    // Normalize $2a$ to $2y$ for PHP's password_verify
    $normalizedHash = str_replace('$2a$', '$2y$', $hashedPassword);
    return password_verify($plainPassword, $normalizedHash);
}

    public function needsRehash(string $hashedPassword): bool
    {
        // Rehash if the hash uses $2y$ or has a different cost
        return preg_match('/^\$2y\$/', $hashedPassword) || password_needs_rehash($hashedPassword, PASSWORD_BCRYPT, ['cost' => $this->cost]);
    }
}