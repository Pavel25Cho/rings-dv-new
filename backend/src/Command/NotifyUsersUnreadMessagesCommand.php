<?php

namespace App\Command;

use App\Entity\ChatMessage;
use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:notify-users-unread-messages',
    description: 'Отправляет уведомления пользователям о непрочитанных сообщениях',
)]
class NotifyUsersUnreadMessagesCommand extends Command
{
    // Время ожидания перед отправкой уведомления (в минутах)
    private const EMAIL_NOTIFICATION_DELAY = 5;

    public function __construct(
        private EntityManagerInterface $entityManager,
        private EmailService $emailService
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        
        $notificationDelay = self::EMAIL_NOTIFICATION_DELAY;
        $delayDateTime = new \DateTime("-{$notificationDelay} minutes");

        $io->info("Проверяем непрочитанные сообщения старше {$notificationDelay} минут...");

        // Находим всех обычных пользователей (не админов) с непрочитанными сообщениями
        $usersWithUnread = $this->entityManager->createQuery(
            'SELECT DISTINCT u
             FROM App\Entity\User u
             JOIN u.chats c
             JOIN c.messages m
             WHERE m.isRead = false
             AND m.createdAt <= :delayDateTime
             AND m.sender != u
             AND u.roles NOT LIKE :adminRole
             AND u.emailVerified = true
             AND (u.emailNotificationSentAt IS NULL OR u.emailNotificationSentAt <= :delayDateTime)'
        )
        ->setParameter('delayDateTime', $delayDateTime)
        ->setParameter('adminRole', '%ROLE_ADMIN%')
        ->getResult();

        $sentCount = 0;
        $errorCount = 0;

        foreach ($usersWithUnread as $user) {
            try {
                // Подсчитываем количество непрочитанных сообщений для этого пользователя
                $unreadCount = $this->entityManager->createQuery(
                    'SELECT COUNT(m.id)
                     FROM App\Entity\ChatMessage m
                     JOIN m.chat c
                     WHERE c.user = :user
                     AND m.isRead = false
                     AND m.sender != :user
                     AND m.createdAt <= :delayDateTime'
                )
                ->setParameter('user', $user)
                ->setParameter('delayDateTime', $delayDateTime)
                ->getSingleScalarResult();

                if ($unreadCount > 0) {
                    $this->emailService->sendUnreadMessagesNotificationToUser($user, (int)$unreadCount);
                    
                    // Обновляем время отправки уведомления
                    $user->setEmailNotificationSentAt(new \DateTime());
                    $this->entityManager->flush();
                    
                    $sentCount++;
                    $io->success("Отправлено уведомление пользователю {$user->getEmail()} ({$unreadCount} сообщений)");
                }
            } catch (\Exception $e) {
                $errorCount++;
                $io->error("Ошибка при отправке уведомления пользователю {$user->getEmail()}: {$e->getMessage()}");
            }
        }

        $io->success("Обработка завершена. Отправлено: {$sentCount}, Ошибок: {$errorCount}");

        return Command::SUCCESS;
    }
}
