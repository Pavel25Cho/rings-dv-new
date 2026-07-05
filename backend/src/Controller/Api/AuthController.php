<?php

namespace App\Controller\Api;

use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

#[Route('/api/auth', name: 'api_auth_')]
class AuthController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher,
        private ValidatorInterface $validator,
        private JWTTokenManagerInterface $jwtManager,
        private EmailService $emailService
    ) {
    }

    #[Route('/register', name: 'register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email и пароль обязательны'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        // Проверка формата email
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return $this->json([
                'success' => false,
                'message' => 'Неверный формат email'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Проверка длины пароля
        if (strlen($password) < 6) {
            return $this->json([
                'success' => false,
                'message' => 'Пароль должен быть не менее 6 символов'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Проверка существования пользователя
        $existingUser = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);
        
        if ($existingUser) {
            return $this->json([
                'success' => false,
                'message' => 'Пользователь с таким email уже существует'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Создание пользователя
        $user = new User();
        $user->setEmail($email);
        $user->setRoles(['ROLE_USER']);
        $user->setIsBlocked(false);
        
        $hashedPassword = $this->passwordHasher->hashPassword($user, $password);
        $user->setPassword($hashedPassword);
        
        // Генерируем токен для подтверждения email
        $user->generateVerificationToken();

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        // Отправляем письмо с подтверждением
        try {
            $this->emailService->sendVerificationEmail($user);
        } catch (\Exception $e) {
            // Логируем ошибку, но не прерываем регистрацию
            // Пользователь сможет переотправить письмо позже
        }

        return $this->json([
            'success' => true,
            'message' => 'Регистрация успешна. Проверьте почту для подтверждения email.',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'roles' => $user->getRoles(),
                'emailVerified' => $user->isEmailVerified(),
            ]
        ], Response::HTTP_CREATED);
    }

    #[Route('/login', name: 'login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['email']) || !isset($data['password'])) {
            return $this->json([
                'success' => false,
                'message' => 'Email и пароль обязательны'
            ], Response::HTTP_BAD_REQUEST);
        }

        $email = $data['email'];
        $password = $data['password'];

        // Поиск пользователя
        $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $email]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Неверный email или пароль'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Проверка блокировки
        if ($user->isBlocked()) {
            return $this->json([
                'success' => false,
                'message' => 'Ваш аккаунт заблокирован'
            ], Response::HTTP_FORBIDDEN);
        }

        // Проверка пароля
        if (!$this->passwordHasher->isPasswordValid($user, $password)) {
            return $this->json([
                'success' => false,
                'message' => 'Неверный email или пароль'
            ], Response::HTTP_UNAUTHORIZED);
        }

        // Генерация JWT токена
        $token = $this->jwtManager->create($user);

        return $this->json([
            'success' => true,
            'token' => $token,
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'role' => in_array('ROLE_ADMIN', $user->getRoles()) ? 'ADMIN' : 'USER',
                'roles' => $user->getRoles(),
                'emailVerified' => $user->isEmailVerified(),
                'isBlocked' => $user->isBlocked(),
            ]
        ]);
    }

    #[Route('/me', name: 'me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json(['user' => null]);
        }

        return $this->json([
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getUserIdentifier(),
                'role' => in_array('ROLE_ADMIN', $user->getRoles()) ? 'ADMIN' : 'USER',
                'roles' => $user->getRoles(),
                'name' => $user->getName(),
                'phone' => $user->getPhone(),
                'emailVerified' => $user->isEmailVerified(),
                'isBlocked' => $user->isBlocked(),
            ]
        ]);
    }

    #[Route('/profile', name: 'profile', methods: ['PUT'])]
    public function updateProfile(Request $request): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Пользователь не авторизован'
            ], Response::HTTP_UNAUTHORIZED);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $user->setName($data['name']);
        }

        if (isset($data['phone'])) {
            $user->setPhone($data['phone']);
        }

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Профиль успешно обновлен',
            'user' => [
                'id' => $user->getId(),
                'email' => $user->getEmail(),
                'name' => $user->getName(),
                'phone' => $user->getPhone(),
                'role' => in_array('ROLE_ADMIN', $user->getRoles()) ? 'ADMIN' : 'USER',
                'roles' => $user->getRoles(),
                'emailVerified' => $user->isEmailVerified(),
                'isBlocked' => $user->isBlocked(),
            ]
        ]);
    }

    #[Route('/logout', name: 'logout', methods: ['POST'])]
    public function logout(): JsonResponse
    {
        // JWT is stateless, client should just remove the token
        return $this->json(['success' => true, 'message' => 'Logged out successfully']);
    }

    #[Route('/send-verification-email', name: 'send_verification_email', methods: ['POST'])]
    public function sendVerificationEmail(): JsonResponse
    {
        $user = $this->getUser();

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Пользователь не авторизован'
            ], Response::HTTP_UNAUTHORIZED);
        }

        if ($user->isEmailVerified()) {
            return $this->json([
                'success' => false,
                'message' => 'Email уже подтвержден'
            ], Response::HTTP_BAD_REQUEST);
        }

        // Генерируем новый токен
        $user->generateVerificationToken();
        $this->entityManager->flush();

        try {
            $this->emailService->sendVerificationEmail($user);
            
            return $this->json([
                'success' => true,
                'message' => 'Письмо с подтверждением отправлено на ' . $user->getEmail()
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Ошибка при отправке письма. Попробуйте позже.'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    #[Route('/verify-email', name: 'verify_email', methods: ['POST'])]
    public function verifyEmail(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        if (!isset($data['token']) || empty($data['token'])) {
            return $this->json([
                'success' => false,
                'message' => 'Токен не указан'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user = $this->entityManager->getRepository(User::class)
            ->findOneBy(['verificationToken' => $data['token']]);

        if (!$user) {
            return $this->json([
                'success' => false,
                'message' => 'Неверный или устаревший токен'
            ], Response::HTTP_BAD_REQUEST);
        }

        if ($user->isEmailVerified()) {
            return $this->json([
                'success' => false,
                'message' => 'Email уже был подтвержден ранее'
            ], Response::HTTP_BAD_REQUEST);
        }

        $user->setEmailVerified(true);
        $user->setVerificationToken(null);
        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'message' => 'Email успешно подтвержден'
        ]);
    }
}
