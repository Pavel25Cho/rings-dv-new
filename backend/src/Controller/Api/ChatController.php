<?php

namespace App\Controller\Api;

use App\Entity\Chat;
use App\Entity\ChatAttachment;
use App\Entity\ChatMessage;
use App\Entity\Order;
use App\Entity\OrderItem;
use App\Entity\Ring;
use App\Entity\User;
use App\Service\FileUploadService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/chat', name: 'api_chat_')]
#[IsGranted('ROLE_USER')]
class ChatController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private FileUploadService $fileUploadService
    ) {
    }

    #[Route('/my', name: 'my_chat', methods: ['GET'])]
    public function getMyChat(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Если пользователь админ, не даем доступ к "моему" чату
        if ($user->isAdmin()) {
            return $this->json([
                'success' => false,
                'message' => 'Админы не могут иметь личный чат'
            ], Response::HTTP_FORBIDDEN);
        }

        // Ищем существующий чат
        $chat = $this->entityManager->getRepository(Chat::class)
            ->findOneBy(['user' => $user]);

        if (!$chat) {
            return $this->json([
                'success' => true,
                'chat' => null
            ]);
        }

        return $this->json([
            'success' => true,
            'chat' => $this->serializeChat($chat, $user)
        ]);
    }

    #[Route('/create-order', name: 'create_order', methods: ['POST'])]
    public function createOrder(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isAdmin()) {
            return $this->json([
                'success' => false,
                'message' => 'Админы не могут создавать заказы'
            ], Response::HTTP_FORBIDDEN);
        }

        if (!$user->isEmailVerified()) {
            return $this->json([
                'success' => false,
                'message' => 'Для оформления заказа необходимо подтвердить email'
            ], Response::HTTP_FORBIDDEN);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['items']) || !is_array($data['items']) || empty($data['items'])) {
            return $this->json([
                'success' => false,
                'message' => 'Необходимо указать товары для заказа'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            // Находим или создаем чат
            $chat = $this->entityManager->getRepository(Chat::class)
                ->findOneBy(['user' => $user]);

            if (!$chat) {
                $chat = new Chat();
                $chat->setUser($user);
                $this->entityManager->persist($chat);
            }

            // Создаем сообщение
            $message = new ChatMessage();
            $message->setChat($chat);
            $message->setSender($user);
            $message->setMessageText('Здравствуйте, хочу приобрести следующие кольца:');
            $this->entityManager->persist($message);

            // Создаем заказ
            $order = new Order();
            $order->setMessage($message);

            // Добавляем товары в заказ
            foreach ($data['items'] as $itemData) {
                if (!isset($itemData['ringId']) || !isset($itemData['quantity'])) {
                    continue;
                }

                $ring = $this->entityManager->getRepository(Ring::class)
                    ->find($itemData['ringId']);

                if (!$ring) {
                    continue;
                }

                // Проверяем наличие
                if ($ring->getInStock() < $itemData['quantity']) {
                    return $this->json([
                        'success' => false,
                        'message' => "Недостаточно товара '{$ring->getPartNumber}' на складе"
                    ], Response::HTTP_BAD_REQUEST);
                }

                $orderItem = new OrderItem();
                $orderItem->setRing($ring);
                $orderItem->setQuantity($itemData['quantity']);
                $order->addItem($orderItem);

                $this->entityManager->persist($orderItem);
            }

            // Рассчитываем общую стоимость
            $order->calculateTotalPrice();

            $this->entityManager->persist($order);

            // Обновляем время последнего сообщения в чате
            $chat->setLastMessageAt(new \DateTime());

            // Очищаем корзину пользователя
            $user->clearCart();

            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Заказ создан',
                'chat' => $this->serializeChat($chat, $user),
                'order' => $this->serializeOrder($order, $user)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка при создании заказа: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/send-message', name: 'send_message', methods: ['POST'])]
    public function sendMessage(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (!isset($data['chatId'])) {
            return $this->json([
                'success' => false,
                'message' => 'Необходимо указать chatId'
            ], Response::HTTP_BAD_REQUEST);
        }

        if (empty($data['text']) && empty($data['attachments'])) {
            return $this->json([
                'success' => false,
                'message' => 'Сообщение должно содержать текст или файлы'
            ], Response::HTTP_BAD_REQUEST);
        }

        $chat = $this->entityManager->getRepository(Chat::class)
            ->find($data['chatId']);

        if (!$chat) {
            return $this->json([
                'success' => false,
                'message' => 'Чат не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        // Проверяем права доступа
        if (!$user->isAdmin() && $chat->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату'
            ], Response::HTTP_FORBIDDEN);
        }

        try {
            $message = new ChatMessage();
            $message->setChat($chat);
            $message->setSender($user);
            
            if (!empty($data['text'])) {
                $message->setMessageText($this->sanitizeText(trim($data['text'])));
            }

            $chat->setLastMessageAt(new \DateTime());

            $this->entityManager->persist($message);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $this->serializeMessage($message, $user)
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка при отправке сообщения: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/messages/{chatId}', name: 'get_messages', methods: ['GET'])]
    public function getMessages(int $chatId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $chat = $this->entityManager->getRepository(Chat::class)->find($chatId);

        if (!$chat) {
            return $this->json([
                'success' => false,
                'message' => 'Чат не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        // Проверяем права доступа
        if (!$user->isAdmin() && $chat->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату'
            ], Response::HTTP_FORBIDDEN);
        }

        // Параметры пагинации
        $limit = $request->query->getInt('limit', 10);
        $offset = $request->query->getInt('offset', 0);
        
        // Ограничиваем максимальное значение limit
        $limit = min($limit, 100);

        // Получаем общее количество сообщений
        $totalMessages = $this->entityManager->createQuery(
            'SELECT COUNT(m.id) FROM App\Entity\ChatMessage m WHERE m.chat = :chat'
        )
        ->setParameter('chat', $chat)
        ->getSingleScalarResult();

        // Получаем сообщения с пагинацией (сортируем по ID в обратном порядке)
        $messages = $this->entityManager->createQuery(
            'SELECT m FROM App\Entity\ChatMessage m WHERE m.chat = :chat ORDER BY m.id DESC'
        )
        ->setParameter('chat', $chat)
        ->setMaxResults($limit)
        ->setFirstResult($offset)
        ->getResult();

        // Переворачиваем массив, чтобы старые сообщения были сначала
        $messages = array_reverse($messages);

        $serializedMessages = array_map(
            fn($msg) => $this->serializeMessage($msg, $user),
            $messages
        );

        return $this->json([
            'success' => true,
            'messages' => $serializedMessages,
            'pagination' => [
                'total' => (int)$totalMessages,
                'limit' => $limit,
                'offset' => $offset,
                'hasMore' => ($offset + $limit) < $totalMessages
            ]
        ]);
    }

    #[Route('/mark-read/{chatId}', name: 'mark_read', methods: ['POST'])]
    public function markMessagesAsRead(int $chatId): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $chat = $this->entityManager->getRepository(Chat::class)->find($chatId);

        if (!$chat) {
            return $this->json([
                'success' => false,
                'message' => 'Чат не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        // Проверяем права доступа
        if (!$user->isAdmin() && $chat->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату'
            ], Response::HTTP_FORBIDDEN);
        }

        // Помечаем непрочитанные сообщения как прочитанные
        foreach ($chat->getMessages() as $message) {
            if (!$message->isRead() && $message->getSender()->getId() !== $user->getId()) {
                $message->setIsRead(true);
            }
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Сообщения помечены как прочитанные'
        ]);
    }

    #[Route('/unread-count', name: 'unread_count', methods: ['GET'])]
    public function getUnreadCount(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isAdmin()) {
            // Для админа считаем все непрочитанные сообщения от пользователей
            $count = $this->entityManager->createQuery(
                'SELECT COUNT(m.id) 
                 FROM App\Entity\ChatMessage m
                 JOIN m.sender s
                 WHERE m.isRead = false 
                 AND s.roles NOT LIKE :role'
            )
            ->setParameter('role', '%ROLE_ADMIN%')
            ->getSingleScalarResult();
        } else {
            // Для пользователя считаем непрочитанные сообщения от админов в его чате
            $chat = $this->entityManager->getRepository(Chat::class)
                ->findOneBy(['user' => $user]);

            if (!$chat) {
                $count = 0;
            } else {
                $count = $this->entityManager->createQuery(
                    'SELECT COUNT(m.id) 
                     FROM App\Entity\ChatMessage m
                     JOIN m.sender s
                     WHERE m.chat = :chat 
                     AND m.isRead = false 
                     AND s.roles LIKE :role'
                )
                ->setParameter('chat', $chat)
                ->setParameter('role', '%ROLE_ADMIN%')
                ->getSingleScalarResult();
            }
        }

        return $this->json([
            'success' => true,
            'count' => (int)$count
        ]);
    }

    #[Route('/update-order-item/{itemId}', name: 'update_order_item', methods: ['POST'])]
    public function updateOrderItem(int $itemId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $orderItem = $this->entityManager->getRepository(OrderItem::class)->find($itemId);
        
        if (!$orderItem) {
            return $this->json([
                'success' => false,
                'message' => 'Товар не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        $order = $orderItem->getOrder();
        
        // Проверяем права доступа
        if (!$user->isAdmin()) {
            // Для обычного пользователя проверяем, что это его заказ
            $chat = $order->getMessage()->getChat();
            if ($chat->getUser()->getId() !== $user->getId()) {
                return $this->json([
                    'success' => false,
                    'message' => 'Нет доступа к этому заказу'
                ], Response::HTTP_FORBIDDEN);
            }
        }

        if (!$order->isPending()) {
            return $this->json([
                'success' => false,
                'message' => 'Можно изменять только неподтвержденные заказы'
            ], Response::HTTP_BAD_REQUEST);
        }

        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['quantity']) || $data['quantity'] < 1) {
            return $this->json([
                'success' => false,
                'message' => 'Неверное количество'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ring = $orderItem->getRing();
        if ($ring && $ring->getInStock() < $data['quantity']) {
            return $this->json([
                'success' => false,
                'message' => "Недостаточно товара на складе. Доступно: {$ring->getInStock()}"
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $orderItem->setQuantity((int)$data['quantity']);
            $order->calculateTotalPrice();
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => 'Количество обновлено',
                'order' => $this->serializeOrder($order, $user)
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка при обновлении: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/list', name: 'list', methods: ['GET'])]
    #[IsGranted('ROLE_ADMIN')]
    public function getChatList(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        $chats = $this->entityManager->getRepository(Chat::class)
            ->createQueryBuilder('c')
            ->orderBy('c.lastMessageAt', 'DESC')
            ->getQuery()
            ->getResult();

        $serializedChats = array_map(
            fn($chat) => $this->serializeChat($chat, $user),
            $chats
        );

        return $this->json([
            'success' => true,
            'chats' => $serializedChats
        ]);
    }

    #[Route('/upload-file/{chatId}', name: 'upload_file', methods: ['POST'])]
    public function uploadFile(int $chatId, Request $request): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $chat = $this->entityManager->getRepository(Chat::class)->find($chatId);

        if (!$chat) {
            return $this->json([
                'success' => false,
                'message' => 'Чат не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        if (!$user->isAdmin() && $chat->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Нет доступа к этому чату'
            ], Response::HTTP_FORBIDDEN);
        }

        $file = $request->files->get('file');
        
        if (!$file) {
            return $this->json([
                'success' => false,
                'message' => 'Файл не загружен'
            ], Response::HTTP_BAD_REQUEST);
        }

        try {
            $uploadedFileData = $this->fileUploadService->uploadChatFile($file);

            $message = new ChatMessage();
            $message->setChat($chat);
            $message->setSender($user);
            
            $text = $request->request->get('text', '');
            if (!empty($text)) {
                $message->setMessageText($this->sanitizeText(trim($text)));
            }

            $attachment = new ChatAttachment();
            $attachment->setOriginalFilename($uploadedFileData['originalFilename']);
            $attachment->setStoredFilename($uploadedFileData['storedFilename']);
            $attachment->setMimeType($uploadedFileData['mimeType']);
            $attachment->setFileSize($uploadedFileData['fileSize']);
            
            $message->addAttachment($attachment);

            $chat->setLastMessageAt(new \DateTime());

            $this->entityManager->persist($message);
            $this->entityManager->persist($attachment);
            $this->entityManager->flush();

            return $this->json([
                'success' => true,
                'message' => $this->serializeMessage($message, $user)
            ]);

        } catch (\RuntimeException $e) {
            return $this->json([
                'success' => false,
                'message' => $e->getMessage()
            ], Response::HTTP_BAD_REQUEST);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка при загрузке файла: ' . $e->getMessage()
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/download-file/{attachmentId}', name: 'download_file', methods: ['GET'])]
    public function downloadFile(int $attachmentId): Response
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $attachment = $this->entityManager->getRepository(ChatAttachment::class)->find($attachmentId);

        if (!$attachment) {
            return $this->json([
                'success' => false,
                'message' => 'Файл не найден'
            ], Response::HTTP_NOT_FOUND);
        }

        $chat = $attachment->getMessage()->getChat();

        if (!$user->isAdmin() && $chat->getUser()->getId() !== $user->getId()) {
            return $this->json([
                'success' => false,
                'message' => 'Нет доступа к этому файлу'
            ], Response::HTTP_FORBIDDEN);
        }

        $filePath = $this->fileUploadService->getFilePath($attachment->getStoredFilename());

        if (!$this->fileUploadService->fileExists($attachment->getStoredFilename())) {
            return $this->json([
                'success' => false,
                'message' => 'Файл не найден на сервере'
            ], Response::HTTP_NOT_FOUND);
        }

        $response = new BinaryFileResponse($filePath);
        
        // Правильное кодирование имени файла для поддержки кириллицы
        // Используем явное указание fallback ASCII имени для совместимости
        $originalFilename = $attachment->getOriginalFilename();
        $fallbackFilename = transliterator_transliterate(
            'Any-Latin; Latin-ASCII',
            $originalFilename
        ) ?: 'file.' . pathinfo($originalFilename, PATHINFO_EXTENSION);
        
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $originalFilename,
            $fallbackFilename
        );
        
        $response->headers->set('Content-Type', $attachment->getMimeType());
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Content-Security-Policy', "default-src 'none'");

        return $response;
    }

    private function serializeChat(Chat $chat, User $currentUser): array
    {
        $messages = $chat->getMessages()->toArray();
        $lastMessage = !empty($messages) ? end($messages) : null;

        $unreadCount = 0;
        foreach ($messages as $message) {
            if (!$message->isRead() && $message->getSender()->getId() !== $currentUser->getId()) {
                $unreadCount++;
            }
        }

        return [
            'id' => $chat->getId(),
            'user' => [
                'id' => $chat->getUser()->getId(),
                'email' => $chat->getUser()->getEmail(),
                'name' => $chat->getUser()->getName(),
                'phone' => $chat->getUser()->getPhone(),
                'isBlocked' => $chat->getUser()->isBlocked(),
                'emailVerified' => $chat->getUser()->isEmailVerified(),
            ],
            'lastMessageAt' => $chat->getLastMessageAt()?->format('Y-m-d H:i:s'),
            'createdAt' => $chat->getCreatedAt()->format('Y-m-d H:i:s'),
            'unreadCount' => $unreadCount,
            'lastMessage' => $lastMessage ? [
                'text' => mb_substr($lastMessage->getMessageText(), 0, 100),
                'hasOrder' => $lastMessage->getOrder() !== null
            ] : null
        ];
    }

    private function serializeMessage(ChatMessage $message, User $currentUser): array
    {
        $data = [
            'id' => $message->getId(),
            'text' => $message->getMessageText(),
            'isRead' => $message->isRead(),
            'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            'sender' => [
                'id' => $message->getSender()->getId(),
                'email' => $message->getSender()->getEmail(),
                'isAdmin' => $message->getSender()->isAdmin()
            ],
            'isMine' => $message->getSender()->getId() === $currentUser->getId()
        ];

        $order = $message->getOrder();
        if ($order) {
            $data['order'] = $this->serializeOrder($order, $currentUser);
        }

        if ($message->hasAttachments()) {
            $data['attachments'] = array_map(
                fn($attachment) => $this->serializeAttachment($attachment),
                $message->getAttachments()->toArray()
            );
        }

        return $data;
    }

    private function serializeAttachment(ChatAttachment $attachment): array
    {
        return [
            'id' => $attachment->getId(),
            'originalFilename' => $attachment->getOriginalFilename(),
            'mimeType' => $attachment->getMimeType(),
            'fileSize' => $attachment->getFileSize(),
            'fileSizeFormatted' => FileUploadService::formatFileSize($attachment->getFileSize()),
            'isImage' => $attachment->isImage(),
            'createdAt' => $attachment->getCreatedAt()->format('Y-m-d H:i:s'),
            'downloadUrl' => '/api/chat/download-file/' . $attachment->getId()
        ];
    }

    private function sanitizeText(string $text): string
    {
        $text = strip_tags($text);
        $text = htmlspecialchars($text, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        
        return $text;
    }

    private function serializeOrder(Order $order, User $currentUser): array
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
            'cancelledAt' => $order->getCancelledAt()?->format('Y-m-d H:i:s'),
            'canEdit' => $order->isPending() && ($currentUser->isAdmin() || !$order->isConfirmed())
        ];
    }
}
