<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\Ring;
use App\Entity\RingGroup;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/admin', name: 'api_admin_')]
#[IsGranted('ROLE_ADMIN')]
class AdminController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('/groups', name: 'groups', methods: ['GET'])]
    public function getGroups(): JsonResponse
    {
        $groups = $this->entityManager->getRepository(RingGroup::class)->findAll();
        return $this->json($groups);
    }

    #[Route('/rings', name: 'rings', methods: ['GET'])]
    public function getRings(): JsonResponse
    {
        $rings = $this->entityManager->getRepository(Ring::class)->findAll();
        return $this->json($rings);
    }

    #[Route('/clients', name: 'clients', methods: ['GET'])]
    public function getClients(): JsonResponse
    {
        $clients = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getQuery()
            ->getResult();

        return $this->json($clients);
    }
}
