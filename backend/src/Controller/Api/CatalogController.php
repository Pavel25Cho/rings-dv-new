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

        // Фильтр по typeCode
        if (isset($queryParams['typeCode']) && !empty($queryParams['typeCode'])) {
            $qb->andWhere('rg.typeCode = :typeCode')
               ->setParameter('typeCode', $queryParams['typeCode']);
        }

        // Фильтр по материалу
        if (isset($queryParams['material']) && !empty($queryParams['material'])) {
            $qb->andWhere('(rg.materialEn LIKE :material OR rg.materialRu LIKE :material)')
               ->setParameter('material', '%' . $queryParams['material'] . '%');
        }

        // Фильтр по бренду
        if (isset($queryParams['brand']) && !empty($queryParams['brand'])) {
            $qb->andWhere('rg.brand LIKE :brand')
               ->setParameter('brand', '%' . $queryParams['brand'] . '%');
        }

        // Фильтр по артикулу (ищем через кольца)
        if (isset($queryParams['partNumber']) && !empty($queryParams['partNumber'])) {
            $rings = $this->entityManager->getRepository(Ring::class)->createQueryBuilder('r')
                ->select('DISTINCT IDENTITY(r.ringGroup)')
                ->where('r.partNumber LIKE :partNumber')
                ->andWhere('r.isHidden = false')
                ->setParameter('partNumber', '%' . $queryParams['partNumber'] . '%')
                ->getQuery()
                ->getResult();

            $groupIds = array_column($rings, 1);
            
            if (empty($groupIds)) {
                return $this->json([]);
            }

            $qb->andWhere('rg.id IN (:groupIds)')
               ->setParameter('groupIds', $groupIds);
        }

        // Фильтр по наличию
        if (isset($queryParams['inStockOnly']) && ($queryParams['inStockOnly'] === 'true' || $queryParams['inStockOnly'] === true)) {
            $ringsWithStock = $this->entityManager->getRepository(Ring::class)->createQueryBuilder('r')
                ->select('DISTINCT IDENTITY(r.ringGroup)')
                ->where('r.inStock = true')
                ->andWhere('r.isHidden = false')
                ->getQuery()
                ->getResult();

            $groupIds = array_column($ringsWithStock, 1);
            
            if (empty($groupIds)) {
                return $this->json([]);
            }

            $qb->andWhere('rg.id IN (:groupIds)')
               ->setParameter('groupIds', $groupIds);
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
            $qb->andWhere('r.inStock = true');
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
