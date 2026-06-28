<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\Order;
use App\Entity\OrderItem;
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
        
        if (isset($data['descriptionRu'])) {
            $group->setDescriptionRu($data['descriptionRu']);
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

    #[Route('/rings', name: 'ring_create', methods: ['POST'])]
    public function createRing(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['partNumber']) || !isset($data['ringGroup'])) {
            return $this->json(['error' => 'Обязательные поля: partNumber, ringGroup'], 400);
        }

        $ringGroup = $this->entityManager->getRepository(RingGroup::class)->find($data['ringGroup']);
        if (!$ringGroup) {
            return $this->json(['error' => 'Группа не найдена'], 404);
        }

        $ring = new Ring();
        $ring->setPartNumber($data['partNumber']);
        $ring->setRingGroup($ringGroup);
        
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
        
        $this->entityManager->persist($ring);
        $this->entityManager->flush();
        
        return $this->json([
            'id' => $ring->getId(),
            'partNumber' => $ring->getPartNumber(),
            'dimensions' => $ring->getDimensions(),
            'inStock' => $ring->getInStock(),
            'price' => $ring->getPrice(),
            'photos' => $ring->getPhotos(),
            'isHidden' => $ring->isHidden(),
            'ringGroup' => $ring->getRingGroup()?->getId(),
        ], 201);
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
    public function getClients(Request $request): JsonResponse
    {
        $search = $request->query->get('search', '');
        $emailVerified = $request->query->get('emailVerified');
        $isBlocked = $request->query->get('isBlocked');
        $hasChat = $request->query->get('hasChat');
        
        $qb = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->leftJoin('u.chats', 'c')
            ->where('u.roles NOT LIKE :role')
            ->setParameter('role', '%ROLE_ADMIN%');
        
        if ($search) {
            $qb->andWhere('u.email LIKE :search OR u.name LIKE :search OR u.phone LIKE :search')
               ->setParameter('search', '%' . $search . '%');
        }
        
        if ($emailVerified !== null && $emailVerified !== '') {
            $qb->andWhere('u.emailVerified = :emailVerified')
               ->setParameter('emailVerified', filter_var($emailVerified, FILTER_VALIDATE_BOOLEAN));
        }
        
        if ($isBlocked !== null && $isBlocked !== '') {
            $qb->andWhere('u.isBlocked = :isBlocked')
               ->setParameter('isBlocked', filter_var($isBlocked, FILTER_VALIDATE_BOOLEAN));
        }
        
        if ($hasChat !== null && $hasChat !== '') {
            if (filter_var($hasChat, FILTER_VALIDATE_BOOLEAN)) {
                $qb->andWhere('c.id IS NOT NULL');
            } else {
                $qb->andWhere('c.id IS NULL');
            }
        }
        
        $clients = $qb->orderBy('u.createdAt', 'DESC')
                     ->getQuery()
                     ->getResult();
        
        // Получаем количество сообщений для каждого пользователя
        $chatRepo = $this->entityManager->getRepository(Chat::class);
        
        $result = array_map(function($user) use ($chatRepo) {
            $chat = $chatRepo->findOneBy(['user' => $user]);
            $hasMessages = false;
            
            if ($chat) {
                $hasMessages = $chat->getMessages()->count() > 0;
            }
            
            return [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'phone' => $user->getPhone(),
                'isBlocked' => $user->isBlocked(),
                'emailVerified' => $user->isEmailVerified(),
                'hasChat' => $chat !== null,
                'hasMessages' => $hasMessages,
                'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ];
        }, $clients);

        return $this->json($result);
    }
    
    #[Route('/clients/stats', name: 'clients_stats', methods: ['GET'])]
    public function getClientsStats(): JsonResponse
    {
        $count = $this->entityManager->getRepository(User::class)
            ->createQueryBuilder('u')
            ->select('COUNT(u.id)')
            ->where('u.roles NOT LIKE :role')
            ->andWhere('u.emailVerified = :verified')
            ->andWhere('u.isBlocked = :blocked')
            ->setParameter('role', '%ROLE_ADMIN%')
            ->setParameter('verified', true)
            ->setParameter('blocked', false)
            ->getQuery()
            ->getSingleScalarResult();

        return $this->json(['count' => (int)$count]);
    }

    #[Route('/clients/{id}/block', name: 'client_block', methods: ['POST'])]
    public function blockClient(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['error' => 'Пользователь не найден'], 404);
        }

        if ($user->isAdmin()) {
            return $this->json(['error' => 'Нельзя заблокировать администратора'], 400);
        }

        $user->setIsBlocked(true);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Пользователь заблокирован'
        ]);
    }

    #[Route('/clients/{id}/unblock', name: 'client_unblock', methods: ['POST'])]
    public function unblockClient(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['error' => 'Пользователь не найден'], 404);
        }

        $user->setIsBlocked(false);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Пользователь разблокирован'
        ]);
    }

    #[Route('/clients/{id}/verify-email', name: 'client_verify_email', methods: ['POST'])]
    public function verifyClientEmail(int $id): JsonResponse
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        
        if (!$user) {
            return $this->json(['error' => 'Пользователь не найден'], 404);
        }

        $user->setEmailVerified(true);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Email подтвержден'
        ]);
    }

    // #[Route('/catalog/delete-all', name: 'catalog_delete_all', methods: ['DELETE'])]
    // public function deleteAllCatalog(): JsonResponse
    // {
        
    //     try {
    //         // Очищаем корзины всех пользователей
    //         $this->entityManager->createQuery('UPDATE App\Entity\User u SET u.cart = :emptyCart')
    //             ->setParameter('emptyCart', json_encode([]))
    //             ->execute();
            
    //         // Удаляем все кольца
    //         $this->entityManager->createQuery('DELETE FROM App\Entity\Ring r')->execute();
            
    //         // Удаляем все группы
    //         $this->entityManager->createQuery('DELETE FROM App\Entity\RingGroup rg')->execute();
            
    //         return $this->json([
    //             'success' => true,
    //             'message' => 'Весь каталог успешно удален, корзины пользователей очищены'
    //         ]);
    //     } catch (\Exception $e) {
    //         return $this->json([
    //             'success' => false,
    //             'message' => 'Ошибка при удалении каталога: ' . $e->getMessage()
    //         ], 500);
    //     }
    // }

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

    #[Route('/orders/confirm/{id}', name: 'order_confirm', methods: ['POST'])]
    public function confirmOrder(int $id): JsonResponse
    {
        $order = $this->entityManager->getRepository(Order::class)->find($id);
        
        if (!$order) {
            return $this->json(['error' => 'Заказ не найден'], 404);
        }

        if (!$order->isPending()) {
            return $this->json(['error' => 'Заказ уже обработан'], 400);
        }

        try {
            // Вычитаем количество из каталога
            foreach ($order->getItems() as $item) {
                $ring = $item->getRing();
                if ($ring) {
                    $newStock = $ring->getInStock() - $item->getQuantity();
                    if ($newStock < 0) {
                        return $this->json([
                            'error' => "Недостаточно товара '{$ring->getPartNumber}' на складе"
                        ], 400);
                    }
                    $ring->setInStock($newStock);
                }
            }

            $order->confirm();
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Заказ подтвержден',
                'order' => $this->serializeOrderForAdmin($order)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при подтверждении заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/orders/cancel/{id}', name: 'order_cancel', methods: ['POST'])]
    public function cancelOrder(int $id): JsonResponse
    {
        $order = $this->entityManager->getRepository(Order::class)->find($id);
        
        if (!$order) {
            return $this->json(['error' => 'Заказ не найден'], 404);
        }

        if ($order->isCancelled()) {
            return $this->json(['error' => 'Заказ уже отменен'], 400);
        }

        try {
            // Если заказ был подтвержден, возвращаем количество в каталог
            if ($order->isConfirmed()) {
                foreach ($order->getItems() as $item) {
                    $ring = $item->getRing();
                    if ($ring) {
                        $ring->setInStock($ring->getInStock() + $item->getQuantity());
                    }
                }
            }

            $order->cancel();
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Заказ отменен',
                'order' => $this->serializeOrderForAdmin($order)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при отмене заказа: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/orders/update-item/{itemId}', name: 'order_update_item', methods: ['POST'])]
    public function updateOrderItem(int $itemId, Request $request): JsonResponse
    {
        $orderItem = $this->entityManager->getRepository(OrderItem::class)->find($itemId);
        
        if (!$orderItem) {
            return $this->json(['error' => 'Товар не найден'], 404);
        }

        $order = $orderItem->getOrder();
        if (!$order->isPending()) {
            return $this->json(['error' => 'Можно изменять только неподтвержденные заказы'], 400);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['quantity']) || $data['quantity'] < 1) {
            return $this->json(['error' => 'Неверное количество'], 400);
        }

        $ring = $orderItem->getRing();
        if ($ring && $ring->getInStock() < $data['quantity']) {
            return $this->json([
                'error' => "Недостаточно товара на складе. Доступно: {$ring->getInStock()}"
            ], 400);
        }

        try {
            $orderItem->setQuantity((int)$data['quantity']);
            $order->calculateTotalPrice();
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Количество обновлено',
                'order' => $this->serializeOrderForAdmin($order)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'error' => 'Ошибка при обновлении: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/orders/check-stock/{id}', name: 'order_check_stock', methods: ['GET'])]
    public function checkOrderStock(int $id): JsonResponse
    {
        $order = $this->entityManager->getRepository(Order::class)->find($id);
        
        if (!$order) {
            return $this->json(['error' => 'Заказ не найден'], 404);
        }

        $stockInfo = [];
        $hasIssues = false;

        foreach ($order->getItems() as $item) {
            $ring = $item->getRing();
            $available = $ring ? $ring->getInStock() : 0;
            $needed = $item->getQuantity();
            $isAvailable = $available >= $needed;

            if (!$isAvailable) {
                $hasIssues = true;
            }

            $stockInfo[] = [
                'partNumber' => $item->getPartNumber(),
                'needed' => $needed,
                'available' => $available,
                'isAvailable' => $isAvailable
            ];
        }

        return $this->json([
            'success' => true,
            'hasIssues' => $hasIssues,
            'items' => $stockInfo
        ]);
    }

    private function serializeOrderForAdmin(Order $order): array
    {
        $items = [];
        foreach ($order->getItems() as $item) {
            $ring = $item->getRing();
            $photos = $ring ? $ring->getPhotos() : [];
            
            $items[] = [
                'id' => $item->getId(),
                'ringId' => $ring?->getId(),
                'partNumber' => $item->getPartNumber(),
                'brand' => $item->getBrand(),
                'quantity' => $item->getQuantity(),
                'price' => $item->getPrice(),
                'totalPrice' => $item->getTotalPrice(),
                'inStock' => $ring?->getInStock(),
                'photoUrl' => !empty($photos) ? $photos[0] : null
            ];
        }

        return [
            'id' => $order->getId(),
            'status' => $order->getStatus(),
            'totalPrice' => $order->getTotalPrice(),
            'items' => $items,
            'createdAt' => $order->getCreatedAt()->format('Y-m-d H:i:s'),
            'confirmedAt' => $order->getConfirmedAt()?->format('Y-m-d H:i:s'),
            'cancelledAt' => $order->getCancelledAt()?->format('Y-m-d H:i:s')
        ];
    }
}
