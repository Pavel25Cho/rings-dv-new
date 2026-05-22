<?php

namespace App\Repository;

use App\Entity\RingGroup;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

class RingGroupRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, RingGroup::class);
    }

    public function findByTypeCode(string $typeCode): ?RingGroup
    {
        return $this->findOneBy(['typeCode' => $typeCode]);
    }

    public function findVisibleGroups(array $filters = []): array
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.isHidden = false');

        if (!empty($filters['typeCode'])) {
            $qb->andWhere('g.typeCode = :typeCode')
               ->setParameter('typeCode', $filters['typeCode']);
        }

        if (!empty($filters['brand'])) {
            $qb->andWhere('g.brand = :brand')
               ->setParameter('brand', $filters['brand']);
        }

        if (!empty($filters['material'])) {
            $qb->andWhere('g.materialEn LIKE :material OR g.materialRu LIKE :material')
               ->setParameter('material', '%' . $filters['material'] . '%');
        }

        return $qb->getQuery()->getResult();
    }
}
