<?php

namespace App\Command;

use App\Entity\Chat;
use App\Entity\User;
use App\Service\EmailService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:notify-admin-unread-messages',
    description: 'Отправляет уведомления админам о непрочитанных сообщениях от клиентов',
)]
class NotifyAdminUnreadMessagesCommand extends Command
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

        $io->info("Проверяем непрочитанные сообщения от клиентов старше {$notificationDelay} минут...");

        // Находим всех админов с receiver = true
        $adminReceivers = $this->entityManager->createQuery(
            'SELECT u
             FROM App\Entity\User u
             WHERE u.roles LIKE :adminRole
             AND u.receiver = true
             AND u.emailVerified = true'
        )
        ->setParameter('adminRole', '%ROLE_ADMIN%')
        ->getResult();

        if (empty($adminReceivers)) {
            $io->warning('Нет админов с включенным получением уведомлений (receiver = true)');
            return Command::SUCCESS;
        }

        // Находим все чаты с непрочитанными сообщениями от пользователей (не от админов)
        $chatsWithUnread = $this->entityManager->createQuery(
            'SELECT DISTINCT c
             FROM App\Entity\Chat c
             JOIN c.messages m
             JOIN m.sender s
             WHERE m.isRead = false
             AND m.emailNotificationSent = false
             AND m.createdAt <= :delayDateTime
             AND s.roles NOT LIKE :adminRole'
        )
        ->setParameter('delayDateTime', $delayDateTime)
        ->setParameter('adminRole', '%ROLE_ADMIN%')
        ->getResult();

        if (empty($chatsWithUnread)) {
            $io->info('Нет непрочитанных сообщений от клиентов');
            return Command::SUCCESS;
        }

        $chatsInfo = [];
        $totalUnreadCount = 0;

        foreach ($chatsWithUnread as $chat) {
            // Подсчитываем непрочитанные сообщения в этом чате
            $unreadCount = $this->entityManager->createQuery(
                'SELECT COUNT(m.id)
                 FROM App\Entity\ChatMessage m
                 JOIN m.sender s
                 WHERE m.chat = :chat
                 AND m.isRead = false
                 AND m.emailNotificationSent = false
                 AND m.createdAt <= :delayDateTime
                 AND s.roles NOT LIKE :adminRole'
            )
            ->setParameter('chat', $chat)
            ->setParameter('delayDateTime', $delayDateTime)
            ->setParameter('adminRole', '%ROLE_ADMIN%')
            ->getSingleScalarResult();

            if ($unreadCount > 0) {
                $user = $chat->getUser();
                $chatsInfo[] = [
                    'userName' => $user->getName(),
                    'userEmail' => $user->getEmail(),
                    'unreadCount' => (int)$unreadCount,
                ];
                
                $totalUnreadCount += (int)$unreadCount;
            }
        }

        if ($totalUnreadCount > 0) {
            $sentCount = 0;
            $errorCount = 0;

            // Отправляем уведомления всем админам с receiver = true
            foreach ($adminReceivers as $admin) {
                try {
                    $this->emailService->sendUnreadMessagesNotificationToAdmin(
                        $admin->getEmail(),
                        $totalUnreadCount,
                        $chatsInfo
                    );
                    
                    $sentCount++;
                    $io->success("Отправлено уведомление админу {$admin->getEmail()} ({$totalUnreadCount} сообщений в " . count($chatsInfo) . " чатах)");
                } catch (\Exception $e) {
                    $errorCount++;
                    $io->error("Ошибка при отправке уведомления админу {$admin->getEmail()}: {$e->getMessage()}");
                }
            }

            // Помечаем отправленные сообщения от пользователей
            if ($sentCount > 0) {
                // Сначала получаем ID всех сообщений, которые нужно пометить
                $messageIdsToUpdate = $this->entityManager->createQuery(
                    'SELECT m.id
                     FROM App\Entity\ChatMessage m
                     JOIN m.sender s
                     WHERE m.isRead = false
                     AND m.emailNotificationSent = false
                     AND m.createdAt <= :delayDateTime
                     AND s.roles NOT LIKE :adminRole'
                )
                ->setParameter('delayDateTime', $delayDateTime)
                ->setParameter('adminRole', '%ROLE_ADMIN%')
                ->getResult();
                
                if (!empty($messageIdsToUpdate)) {
                    $ids = array_column($messageIdsToUpdate, 'id');
                    
                    // Теперь обновляем по списку ID
                    $this->entityManager->createQuery(
                        'UPDATE App\Entity\ChatMessage m
                         SET m.emailNotificationSent = true
                         WHERE m.id IN (:ids)'
                    )
                    ->setParameter('ids', $ids)
                    ->execute();
                    
                    $this->entityManager->flush();
                }
            }

            $io->success("Обработка завершена. Отправлено: {$sentCount} админам, Ошибок: {$errorCount}");
        } else {
            $io->info('Нет новых непрочитанных сообщений для уведомления');
        }

        return Command::SUCCESS;
    }
}
