<?php

namespace App\Controller\Api;

use App\Entity\Ring;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/cart', name: 'api_cart_')]
class CartController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'get', methods: ['GET'])]
    public function getCart(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $cart = $user->getCart();
        $cartWithDetails = [];

        foreach ($cart as $item) {
            $ring = $this->entityManager->getRepository(Ring::class)->find($item['ringId']);
            
            if ($ring) {
                $group = $ring->getRingGroup();
                $photos = $ring->getPhotos();
                $cartWithDetails[] = [
                    'ringId' => $item['ringId'],
                    'quantity' => $item['quantity'],
                    'ring' => [
                        'id' => $ring->getId(),
                        'partNumber' => $ring->getPartNumber(),
                        'price' => $ring->getPrice(),
                        'inStock' => $ring->getInStock(),
                        'photoUrl' => !empty($photos) ? $photos[0] : null,
                        'dimensions' => $ring->getDimensions(),
                        'group' => $group ? [
                            'id' => $group->getId(),
                            'typeCode' => $group->getTypeCode(),
                            'nameRu' => $group->getNameRu(),
                        ] : null
                    ]
                ];
            }
        }

        return $this->json([
            'success' => true,
            'cart' => $cartWithDetails
        ]);
    }

    #[Route('/add', name: 'add', methods: ['POST'])]
    public function addToCart(Request $request): JsonResponse
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

        if (!isset($data['ringId']) || !isset($data['quantity'])) {
            return $this->json([
                'success' => false,
                'message' => 'Необходимо указать ringId и quantity'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ringId = (int) $data['ringId'];
        $quantity = (int) $data['quantity'];

        if ($quantity < 1) {
            return $this->json([
                'success' => false,
                'message' => 'Количество должно быть больше 0'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ring = $this->entityManager->getRepository(Ring::class)->find($ringId);

        if (!$ring) {
            return $this->json([
                'success' => false,
                'message' => 'Кольцо не найдено'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($ring->getInStock() < $quantity) {
            return $this->json([
                'success' => false,
                'message' => 'Недостаточно товара на складе'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->addToCart($ringId, $quantity);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Товар добавлен в корзину',
            'cart' => $user->getCart()
        ]);
    }

    #[Route('/update', name: 'update', methods: ['POST'])]
    public function updateCartItem(Request $request): JsonResponse
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

        if (!isset($data['ringId']) || !isset($data['quantity'])) {
            return $this->json([
                'success' => false,
                'message' => 'Необходимо указать ringId и quantity'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ringId = (int) $data['ringId'];
        $quantity = (int) $data['quantity'];

        if ($quantity < 1) {
            return $this->json([
                'success' => false,
                'message' => 'Количество должно быть больше 0'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ring = $this->entityManager->getRepository(Ring::class)->find($ringId);

        if (!$ring) {
            return $this->json([
                'success' => false,
                'message' => 'Кольцо не найдено'
            ], Response::HTTP_NOT_FOUND);
        }

        if ($ring->getInStock() < $quantity) {
            return $this->json([
                'success' => false,
                'message' => 'Недостаточно товара на складе'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->updateCartItemQuantity($ringId, $quantity);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Количество обновлено',
            'cart' => $user->getCart()
        ]);
    }

    #[Route('/remove', name: 'remove', methods: ['POST'])]
    public function removeFromCart(Request $request): JsonResponse
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

        if (!isset($data['ringId'])) {
            return $this->json([
                'success' => false,
                'message' => 'Необходимо указать ringId'
            ], Response::HTTP_BAD_REQUEST);
        }

        $ringId = (int) $data['ringId'];

        $user->removeFromCart($ringId);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Товар удален из корзины',
            'cart' => $user->getCart()
        ]);
    }

    #[Route('/clear', name: 'clear', methods: ['POST'])]
    public function clearCart(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Необходима авторизация'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $user->clearCart();
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Корзина очищена',
            'cart' => []
        ]);
    }
}
