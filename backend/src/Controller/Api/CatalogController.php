<?php

namespace App\Controller\Api;

use App\Entity\Ring;
use App\Entity\RingGroup;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/catalog', name: 'api_catalog_')]
class CatalogController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/groups', name: 'groups', methods: ['GET'])]
    public function getGroups(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();
        
        $qb = $this->entityManager->getRepository(RingGroup::class)->createQueryBuilder('rg');
        $qb->where('rg.isHidden = false');

        // Универсальный поиск по названию, типу и номеру колец
        if (isset($queryParams['search']) && !empty($queryParams['search'])) {
            $searchTerm = $queryParams['search'];
            
            // Ищем группы, у которых есть кольца с таким номером
            $ringsWithNumber = $this->entityManager->getRepository(Ring::class)->createQueryBuilder('r')
                ->select('DISTINCT IDENTITY(r.ringGroup)')
                ->where('r.partNumber LIKE :partNumber')
                ->andWhere('r.isHidden = false')
                ->setParameter('partNumber', '%' . $searchTerm . '%')
                ->getQuery()
                ->getResult();

            $groupIdsFromRings = !empty($ringsWithNumber) ? array_column($ringsWithNumber, 1) : [];
            
            // Комбинируем поиск: по названию/типу группы ИЛИ по ID групп с подходящими кольцами
            if (!empty($groupIdsFromRings)) {
                $qb->andWhere(
                    $qb->expr()->orX(
                        $qb->expr()->like('rg.nameRu', ':search'),
                        $qb->expr()->like('rg.nameEn', ':search'),
                        $qb->expr()->like('rg.typeCode', ':search'),
                        $qb->expr()->in('rg.id', ':groupIds')
                    )
                )
                ->setParameter('search', '%' . $searchTerm . '%')
                ->setParameter('groupIds', $groupIdsFromRings);
            } else {
                // Если нет колец с таким номером, ищем только по названию/типу группы
                $qb->andWhere('(rg.nameRu LIKE :search OR rg.nameEn LIKE :search OR rg.typeCode LIKE :search)')
                   ->setParameter('search', '%' . $searchTerm . '%');
            }
        }

        // Фильтр по наличию (всегда активен для обычного каталога)
        if (isset($queryParams['inStockOnly']) && ($queryParams['inStockOnly'] === 'true' || $queryParams['inStockOnly'] === true)) {
            $ringsWithStock = $this->entityManager->getRepository(Ring::class)->createQueryBuilder('r')
                ->select('DISTINCT IDENTITY(r.ringGroup)')
                ->where('r.inStock > 0')
                ->andWhere('r.isHidden = false')
                ->getQuery()
                ->getResult();

            $groupIds = array_column($ringsWithStock, 1);
            
            if (empty($groupIds)) {
                return $this->json([]);
            }

            $qb->andWhere('rg.id IN (:stockGroupIds)')
               ->setParameter('stockGroupIds', $groupIds);
        }

        $qb->orderBy('rg.typeCode', 'ASC');

        $groups = $qb->getQuery()->getResult();

        return $this->json($groups);
    }

    #[Route('/rings', name: 'rings', methods: ['GET'])]
    public function getRings(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();
        
        $qb = $this->entityManager->getRepository(Ring::class)->createQueryBuilder('r');
        $qb->where('r.isHidden = false');

        // Фильтр по группе (обязательно)
        if (isset($queryParams['groupId']) && !empty($queryParams['groupId'])) {
            $qb->andWhere('r.ringGroup = :groupId')
               ->setParameter('groupId', $queryParams['groupId']);
        }

        // Фильтр по наличию
        if (isset($queryParams['inStockOnly']) && ($queryParams['inStockOnly'] === 'true' || $queryParams['inStockOnly'] === true)) {
            $qb->andWhere('r.inStock > 0');
        }

        // Фильтр по артикулу
        if (isset($queryParams['partNumber']) && !empty($queryParams['partNumber'])) {
            $qb->andWhere('r.partNumber LIKE :partNumber')
               ->setParameter('partNumber', '%' . $queryParams['partNumber'] . '%');
        }

        $qb->orderBy('r.partNumber', 'ASC');

        $rings = $qb->getQuery()->getResult();

        return $this->json($rings);
    }
}
