<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository implements PasswordUpgraderInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', $user::class));
        }

        $user->setPassword($newHashedPassword);
        $this->getEntityManager()->persist($user);
        $this->getEntityManager()->flush();
    }

    //    /**
    //     * @return User[] Returns an array of User objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('u.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?User
    //    {
    //        return $this->createQueryBuilder('u')
    //            ->andWhere('u.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }

   // src/Repository/UserRepository.php
public function findByRoles(string $role): array
{
    return $this->createQueryBuilder('u')
        ->andWhere('u.roles LIKE :role OR u.roles LIKE :blocked')
        ->setParameter('role', '%"' . $role . '"%') // Recherche PATIENT ou MEDECIN
        ->setParameter('blocked', '%"BLOCKED"%')   // Recherche BLOCKED
        ->getQuery()
        ->getResult();
}


 public function getDistinctSpecialites()
    {
        return $this->createQueryBuilder('u')
            ->select('DISTINCT u.specialite')
            ->where('u.specialite IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver des médecins par spécialité
     */
    public function findMedecinsBySpecialite(string $specialite)
    {
        return $this->createQueryBuilder('u')
            ->where('u.specialite LIKE :specialite')
            ->setParameter('specialite', '%' . $specialite . '%')
            ->getQuery()
            ->getResult();
    }

    /**
     * Trouver un médecin par son id
     */
    public function findMedecinById(int $id)
    {
        return $this->find($id);
    }

}
