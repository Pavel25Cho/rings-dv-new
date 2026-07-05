#!/usr/bin/env php
<?php

/**
 * Скрипт для тестирования SMTP подключения
 * Использование: php test-smtp.php
 */

require __DIR__ . '/../vendor/autoload.php';

use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\Mailer\Transport;
use Symfony\Component\Mailer\Mailer;
use Symfony\Component\Mime\Email;

echo "=== Тест SMTP подключения ===\n\n";

// Загружаем .env
$dotenv = new Dotenv();
$dotenv->loadEnv(__DIR__ . '/../.env');

$smtpHost = $_ENV['SMTP_HOST'] ?? null;
$smtpPort = $_ENV['SMTP_PORT'] ?? null;
$smtpUser = $_ENV['SMTP_USER'] ?? null;
$smtpPassword = $_ENV['SMTP_PASSWORD'] ?? null;
$smtpFrom = $_ENV['SMTP_FROM'] ?? null;
$mailerDsn = $_ENV['MAILER_DSN'] ?? null;

echo "Проверка переменных окружения:\n";
echo "SMTP_HOST: " . ($smtpHost ?: 'НЕ ЗАДАН') . "\n";
echo "SMTP_PORT: " . ($smtpPort ?: 'НЕ ЗАДАН') . "\n";
echo "SMTP_USER: " . ($smtpUser ?: 'НЕ ЗАДАН') . "\n";
echo "SMTP_PASSWORD: " . ($smtpPassword ? '***' : 'НЕ ЗАДАН') . "\n";
echo "SMTP_FROM: " . ($smtpFrom ?: 'НЕ ЗАДАН') . "\n";
echo "MAILER_DSN: " . ($mailerDsn ?: 'НЕ ЗАДАН') . "\n\n";

if (!$mailerDsn || !$smtpFrom) {
    echo "❌ Ошибка: Не заданы необходимые переменные окружения\n";
    exit(1);
}

// Запрашиваем email для теста
echo "Введите email для отправки тестового письма: ";
$testEmail = trim(fgets(STDIN));

if (!filter_var($testEmail, FILTER_VALIDATE_EMAIL)) {
    echo "❌ Неверный формат email\n";
    exit(1);
}

echo "\nОтправка тестового письма на $testEmail...\n";

try {
    $transport = Transport::fromDsn($mailerDsn);
    $mailer = new Mailer($transport);
    
    $email = (new Email())
        ->from($smtpFrom)
        ->to($testEmail)
        ->subject('Тест SMTP подключения - Rings Catalog')
        ->text('Это тестовое письмо для проверки работы SMTP.')
        ->html('<p>Это <strong>тестовое письмо</strong> для проверки работы SMTP.</p><p>Если вы получили это письмо, значит настройка выполнена успешно! ✅</p>');
    
    $mailer->send($email);
    
    echo "\n✅ Письмо успешно отправлено на $testEmail\n";
    echo "Проверьте почтовый ящик (включая папку спам)\n";
    
} catch (\Exception $e) {
    echo "\n❌ Ошибка при отправке письма:\n";
    echo $e->getMessage() . "\n\n";
    
    echo "Возможные причины:\n";
    echo "1. Неверные SMTP параметры в .env\n";
    echo "2. SMTP сервер недоступен\n";
    echo "3. Неверный логин или пароль\n";
    echo "4. Неправильный порт (используйте 465 для SSL, 587 для TLS)\n";
    echo "5. Блокировка фаервола\n\n";
    
    exit(1);
}
