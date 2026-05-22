<?php

namespace App\Repository;

use App\Entity\Ring;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RingRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Ring::class);
    }

    public function findByPartNumber(string $partNumber): ?Ring
    {
        return $this->findOneBy(['partNumber' => $partNumber]);
    }

    public function findByGroupId(int $groupId, bool $onlyInStock = false): array
    {
        $qb = $this->createQueryBuilder('r')
            ->where('r.ringGroup = :groupId')
            ->andWhere('r.isHidden = false')
            ->setParameter('groupId', $groupId);

        if ($onlyInStock) {
            $qb->andWhere('r.inStock = true');
        }

        return $qb->getQuery()->getResult();
    }
}
