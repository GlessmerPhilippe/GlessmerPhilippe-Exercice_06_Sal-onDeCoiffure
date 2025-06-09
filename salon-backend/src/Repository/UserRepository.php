<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<User>
 */
class UserRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
    }

    /**
     * Retourne tous les utilisateurs qui possèdent un rôle donné (ex: ROLE_COIFFEUR)
     * Compatible PostgreSQL et MySQL
     *
     * @param string $role
     * @return User[]
     */
    public function findByRole(string $role): array
    {
        // Requête SQL native pour PostgreSQL (champ json)
        $conn = $this->getEntityManager()->getConnection();
        $sql = 'SELECT id FROM "user" WHERE roles::text LIKE :role';
        $stmt = $conn->prepare($sql);
        $resultSet = $stmt->executeQuery(['role' => '%"'.$role.'"%']);
        $ids = array_column($resultSet->fetchAllAssociative(), 'id');
        if (!$ids) return [];
        // On retourne les entités User Doctrine
        return $this->createQueryBuilder('u')
            ->andWhere('u.id IN (:ids)')
            ->setParameter('ids', $ids)
            ->getQuery()
            ->getResult();
    }
}
