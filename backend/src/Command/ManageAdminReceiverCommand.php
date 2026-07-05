<?php

namespace App\Command;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:admin:manage-receiver',
    description: 'Управление получением email уведомлений для админов',
)]
class ManageAdminReceiverCommand extends Command
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument('email', InputArgument::OPTIONAL, 'Email админа (если не указан, показывает список всех)')
            ->addOption('enable', null, InputOption::VALUE_NONE, 'Включить получение уведомлений')
            ->addOption('disable', null, InputOption::VALUE_NONE, 'Отключить получение уведомлений')
            ->addOption('all', null, InputOption::VALUE_NONE, 'Применить ко всем админам')
            ->setHelp(<<<'HELP'
Примеры использования:

  # Просмотр всех админов и их статуса получения уведомлений
  php bin/console app:admin:manage-receiver

  # Включить получение уведомлений для конкретного админа
  php bin/console app:admin:manage-receiver admin@example.ru --enable

  # Отключить получение уведомлений для конкретного админа
  php bin/console app:admin:manage-receiver admin@example.ru --disable

  # Включить получение уведомлений для всех админов
  php bin/console app:admin:manage-receiver --all --enable

  # Отключить получение уведомлений для всех админов
  php bin/console app:admin:manage-receiver --all --disable
HELP
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $email = $input->getArgument('email');
        $enable = $input->getOption('enable');
        $disable = $input->getOption('disable');
        $all = $input->getOption('all');

        // Проверка на конфликт опций
        if ($enable && $disable) {
            $io->error('Нельзя одновременно использовать --enable и --disable');
            return Command::FAILURE;
        }

        // Получаем всех админов
        $admins = $this->entityManager->createQuery(
            'SELECT u FROM App\Entity\User u WHERE u.roles LIKE :adminRole'
        )
        ->setParameter('adminRole', '%ROLE_ADMIN%')
        ->getResult();

        if (empty($admins)) {
            $io->warning('В системе нет админов');
            return Command::SUCCESS;
        }

        // Если не указаны опции, показываем список
        if (!$enable && !$disable) {
            return $this->showAdminsList($io, $admins);
        }

        // Если указан --all, применяем ко всем админам
        if ($all) {
            return $this->updateAllAdmins($io, $admins, $enable);
        }

        // Если указан email, применяем к конкретному админу
        if ($email) {
            return $this->updateSpecificAdmin($io, $email, $enable);
        }

        $io->error('Укажите email админа или используйте --all');
        return Command::FAILURE;
    }

    private function showAdminsList(SymfonyStyle $io, array $admins): int
    {
        $io->title('Список админов и их статус получения уведомлений');

        $rows = [];
        foreach ($admins as $admin) {
            $rows[] = [
                $admin->getId(),
                $admin->getEmail(),
                $admin->getName() ?: '-',
                $admin->isReceiver() ? '✅ Да' : '❌ Нет',
                $admin->isEmailVerified() ? '✅ Да' : '❌ Нет',
            ];
        }

        $io->table(
            ['ID', 'Email', 'Имя', 'Получает уведомления', 'Email подтвержден'],
            $rows
        );

        $enabledCount = count(array_filter($admins, fn($a) => $a->isReceiver()));
        $io->info("Всего админов: " . count($admins));
        $io->info("Получают уведомления: {$enabledCount}");

        return Command::SUCCESS;
    }

    private function updateAllAdmins(SymfonyStyle $io, array $admins, bool $enable): int
    {
        $io->title($enable ? 'Включение уведомлений для всех админов' : 'Отключение уведомлений для всех админов');

        $count = 0;
        foreach ($admins as $admin) {
            $admin->setReceiver($enable);
            $count++;
        }

        $this->entityManager->flush();

        $status = $enable ? 'включены' : 'отключены';
        $io->success("Уведомления {$status} для {$count} админов");

        return Command::SUCCESS;
    }

    private function updateSpecificAdmin(SymfonyStyle $io, string $email, bool $enable): int
    {
        $admin = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $email]);

        if (!$admin) {
            $io->error("Пользователь с email {$email} не найден");
            return Command::FAILURE;
        }

        if (!$admin->isAdmin()) {
            $io->error("Пользователь {$email} не является админом");
            return Command::FAILURE;
        }

        $admin->setReceiver($enable);
        $this->entityManager->flush();

        $status = $enable ? 'включены' : 'отключены';
        $io->success("Уведомления {$status} для админа {$email}");

        if ($enable && !$admin->isEmailVerified()) {
            $io->warning("Внимание: у админа {$email} не подтвержден email. Уведомления не будут отправляться.");
        }

        return Command::SUCCESS;
    }
}
