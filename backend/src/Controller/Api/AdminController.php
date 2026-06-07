<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\Ring;
use App\Entity\RingGroup;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
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

    #[Route('/stats', name: 'stats', methods: ['GET'])]
    public function getStats(): JsonResponse
    {
        $ringGroupRepo = $this->entityManager->getRepository(RingGroup::class);
        $ringRepo = $this->entityManager->getRepository(Ring::class);

        // Общее количество групп
        $totalGroups = $ringGroupRepo->count([]);

        // Количество групп с кольцами где inStock > 0
        $groupsWithStock = $this->entityManager->createQuery(
            'SELECT COUNT(DISTINCT rg.id) 
             FROM App\Entity\RingGroup rg 
             JOIN rg.rings r 
             WHERE r.inStock > 0'
        )->getSingleScalarResult();

        // Общее количество колец
        $totalRings = $ringRepo->count([]);

        // Количество колец где inStock > 0
        $ringsWithStock = $ringRepo->count(['inStock' => ['$gt' => 0]]);
        // Используем DQL для правильного подсчета
        $ringsWithStock = $this->entityManager->createQuery(
            'SELECT COUNT(r.id) 
             FROM App\Entity\Ring r 
             WHERE r.inStock > 0'
        )->getSingleScalarResult();

        return $this->json([
            'groupsTotal' => (int)$totalGroups,
            'groupsWithStock' => (int)$groupsWithStock,
            'ringsTotal' => (int)$totalRings,
            'ringsWithStock' => (int)$ringsWithStock,
        ]);
    }

    #[Route('/groups', name: 'groups', methods: ['GET'])]
    public function getGroups(): JsonResponse
    {
        $groups = $this->entityManager->getRepository(RingGroup::class)->findAll();
        return $this->json($groups);
    }

    #[Route('/groups/{id}', name: 'group_get', methods: ['GET'])]
    public function getGroup(int $id): JsonResponse
    {
        $group = $this->entityManager->getRepository(RingGroup::class)->find($id);
        
        if (!$group) {
            return $this->json(['error' => 'Группа не найдена'], 404);
        }
        
        return $this->json($group);
    }

    #[Route('/groups/{id}', name: 'group_update', methods: ['PUT', 'PATCH'])]
    public function updateGroup(int $id, Request $request): JsonResponse
    {
        $group = $this->entityManager->getRepository(RingGroup::class)->find($id);
        
        if (!$group) {
            return $this->json(['error' => 'Группа не найдена'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['nameRu'])) {
            $group->setNameRu($data['nameRu']);
        }
        
        if (isset($data['nameEn'])) {
            $group->setNameEn($data['nameEn']);
        }
        
        if (isset($data['typeCode'])) {
            $group->setTypeCode($data['typeCode']);
        }
        
        if (isset($data['brand'])) {
            $group->setBrand($data['brand']);
        }
        
        if (isset($data['materialEn'])) {
            $group->setMaterialEn($data['materialEn']);
        }
        
        if (isset($data['materialRu'])) {
            $group->setMaterialRu($data['materialRu']);
        }
        
        if (isset($data['photoUrl'])) {
            $group->setPhotoUrl($data['photoUrl']);
        }
        
        if (isset($data['dimensionsPhotoUrl'])) {
            $group->setDimensionsPhotoUrl($data['dimensionsPhotoUrl']);
        }
        
        if (isset($data['columnNames'])) {
            $group->setColumnNames($data['columnNames']);
        }
        
        if (isset($data['isHidden'])) {
            $group->setIsHidden($data['isHidden']);
        }
        
        $this->entityManager->flush();
        
        return $this->json($group);
    }

    #[Route('/groups/{id}', name: 'group_delete', methods: ['DELETE'])]
    public function deleteGroup(int $id): JsonResponse
    {
        $group = $this->entityManager->getRepository(RingGroup::class)->find($id);
        
        if (!$group) {
            return $this->json(['error' => 'Группа не найдена'], 404);
        }
        
        $this->entityManager->remove($group);
        $this->entityManager->flush();
        
        return $this->json(['success' => true]);
    }

    #[Route('/rings', name: 'rings', methods: ['GET'])]
    public function getRings(): JsonResponse
    {
        $rings = $this->entityManager->getRepository(Ring::class)->findAll();
        
        // Преобразуем в массив с ID группы
        $ringsData = array_map(function($ring) {
            $data = [
                'id' => $ring->getId(),
                'partNumber' => $ring->getPartNumber(),
                'dimensions' => $ring->getDimensions(),
                'inStock' => $ring->getInStock(),
                'price' => $ring->getPrice(),
                'photos' => $ring->getPhotos(),
                'isHidden' => $ring->isHidden(),
                'ringGroup' => $ring->getRingGroup()?->getId(),
            ];
            return $data;
        }, $rings);
        
        return $this->json($ringsData);
    }

    #[Route('/rings/{id}', name: 'ring_get', methods: ['GET'])]
    public function getRing(int $id): JsonResponse
    {
        $ring = $this->entityManager->getRepository(Ring::class)->find($id);
        
        if (!$ring) {
            return $this->json(['error' => 'Кольцо не найдено'], 404);
        }
        
        return $this->json($ring);
    }

    #[Route('/rings/{id}', name: 'ring_update', methods: ['PUT', 'PATCH'])]
    public function updateRing(int $id, Request $request): JsonResponse
    {
        $ring = $this->entityManager->getRepository(Ring::class)->find($id);
        
        if (!$ring) {
            return $this->json(['error' => 'Кольцо не найдено'], 404);
        }
        
        $data = json_decode($request->getContent(), true);
        
        if (isset($data['partNumber'])) {
            $ring->setPartNumber($data['partNumber']);
        }
        
        if (isset($data['dimensions'])) {
            $ring->setDimensions($data['dimensions']);
        }
        
        if (isset($data['inStock'])) {
            $ring->setInStock((int)$data['inStock']);
        }
        
        if (isset($data['price'])) {
            $ring->setPrice($data['price']);
        }
        
        if (isset($data['photos'])) {
            $ring->setPhotos($data['photos']);
        }
        
        if (isset($data['isHidden'])) {
            $ring->setIsHidden($data['isHidden']);
        }
        
        $this->entityManager->flush();
        
        return $this->json($ring);
    }

    #[Route('/rings/{id}', name: 'ring_delete', methods: ['DELETE'])]
    public function deleteRing(int $id): JsonResponse
    {
        $ring = $this->entityManager->getRepository(Ring::class)->find($id);
        
        if (!$ring) {
            return $this->json(['error' => 'Кольцо не найдено'], 404);
        }
        
        $this->entityManager->remove($ring);
        $this->entityManager->flush();
        
        return $this->json(['success' => true]);
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

    #[Route('/catalog/delete-all', name: 'catalog_delete_all', methods: ['DELETE'])]
    public function deleteAllCatalog(): JsonResponse
    {
        try {
            // Очищаем корзины всех пользователей
            $this->entityManager->createQuery('UPDATE App\Entity\User u SET u.cart = :emptyCart')
                ->setParameter('emptyCart', json_encode([]))
                ->execute();
            
            // Удаляем все кольца
            $this->entityManager->createQuery('DELETE FROM App\Entity\Ring r')->execute();
            
            // Удаляем все группы
            $this->entityManager->createQuery('DELETE FROM App\Entity\RingGroup rg')->execute();
            
            return $this->json([
                'success' => true,
                'message' => 'Весь каталог успешно удален, корзины пользователей очищены'
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка при удалении каталога: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/upload-image', name: 'upload_image', methods: ['POST'])]
    public function uploadImage(Request $request): JsonResponse
    {
        $file = $request->files->get('image');
        
        if (!$file) {
            return $this->json(['error' => 'Файл не найден'], 400);
        }

        // Проверяем тип файла
        $allowedMimeTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($file->getMimeType(), $allowedMimeTypes)) {
            return $this->json(['error' => 'Неверный формат файла. Разрешены: JPG, PNG, GIF, WEBP'], 400);
        }

        // Проверяем размер (макс 5MB)
        if ($file->getSize() > 5 * 1024 * 1024) {
            return $this->json(['error' => 'Файл слишком большой. Максимум 5MB'], 400);
        }

        try {
            // Генерируем уникальное имя файла
            $originalFilename = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = transliterator_transliterate(
                'Any-Latin; Latin-ASCII; [^A-Za-z0-9_] remove; Lower()',
                $originalFilename
            );
            $newFilename = $safeFilename . '-' . uniqid() . '.' . $file->guessExtension();

            // Определяем директорию для загрузки
            $uploadDir = $this->getParameter('kernel.project_dir') . '/public/uploads/images';
            
            // Создаем директорию если её нет
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            // Перемещаем файл
            $file->move($uploadDir, $newFilename);

            // Возвращаем URL файла
            $fileUrl = '/uploads/images/' . $newFilename;

            return $this->json([
                'success' => true,
                'url' => $fileUrl,
                'filename' => $newFilename
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при загрузке файла: ' . $e->getMessage()
            ], 500);
        }
    }
}
