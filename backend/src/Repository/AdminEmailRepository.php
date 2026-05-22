<?php

namespace App\Repository;

use App\Entity\AdminEmail;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class AdminEmailRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, AdminEmail::class);
    }

    public function findActiveEmails(): array
    {
        return $this->createQueryBuilder('a')
            ->where('a.isActive = true')
            ->getQuery()
            ->getResult();
    }
}
