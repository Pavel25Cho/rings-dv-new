<?php

namespace App\Service;

use App\Entity\User;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Psr\Log\LoggerInterface;

class EmailService
{
    public function __construct(
        private MailerInterface $mailer,
        private ParameterBagInterface $params,
        private LoggerInterface $logger
    ) {
    }

    public function sendVerificationEmail(User $user): void
    {
        try {
            $smtpFrom = $this->params->get('app.smtp_from');
            $frontendUrl = $this->params->get('app.frontend_url');
            
            $verificationUrl = $frontendUrl . '/verify-email?token=' . $user->getVerificationToken();

            $email = (new TemplatedEmail())
                ->from(new Address($smtpFrom, 'Rings Catalog'))
                ->to($user->getEmail())
                ->subject('Подтверждение регистрации')
                ->htmlTemplate('emails/verification.html.twig')
                ->context([
                    'user' => $user,
                    'verificationUrl' => $verificationUrl,
                ]);

            $this->mailer->send($email);
            
            $this->logger->info('Verification email sent', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail()
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send verification email', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function sendUnreadMessagesNotificationToUser(User $user, int $unreadCount): void
    {
        try {
            $smtpFrom = $this->params->get('app.smtp_from');
            $frontendUrl = $this->params->get('app.frontend_url');
            
            $chatUrl = $frontendUrl . '/chat';

            $email = (new TemplatedEmail())
                ->from(new Address($smtpFrom, 'Rings Catalog'))
                ->to($user->getEmail())
                ->subject('У вас есть непрочитанные сообщения')
                ->htmlTemplate('emails/unread_messages_user.html.twig')
                ->context([
                    'user' => $user,
                    'unreadCount' => $unreadCount,
                    'chatUrl' => $chatUrl,
                ]);

            $this->mailer->send($email);
            
            $this->logger->info('Unread messages notification sent to user', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'unread_count' => $unreadCount
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send unread messages notification to user', [
                'user_id' => $user->getId(),
                'email' => $user->getEmail(),
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    public function sendUnreadMessagesNotificationToAdmin(string $adminEmail, int $totalUnreadCount, array $chatsInfo): void
    {
        try {
            $smtpFrom = $this->params->get('app.smtp_from');
            $frontendUrl = $this->params->get('app.frontend_url');
            
            $adminChatUrl = $frontendUrl . '/admin/chats';

            $email = (new TemplatedEmail())
                ->from(new Address($smtpFrom, 'Rings Catalog'))
                ->to($adminEmail)
                ->subject('Новые сообщения в чатах')
                ->htmlTemplate('emails/unread_messages_admin.html.twig')
                ->context([
                    'totalUnreadCount' => $totalUnreadCount,
                    'chatsInfo' => $chatsInfo,
                    'adminChatUrl' => $adminChatUrl,
                ]);

            $this->mailer->send($email);
            
            $this->logger->info('Unread messages notification sent to admin', [
                'admin_email' => $adminEmail,
                'total_unread' => $totalUnreadCount,
                'chats_count' => count($chatsInfo)
            ]);
        } catch (\Exception $e) {
            $this->logger->error('Failed to send unread messages notification to admin', [
                'admin_email' => $adminEmail,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
