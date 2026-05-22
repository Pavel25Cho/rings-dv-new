#!/bin/bash

echo "🚀 Установка Rings Catalog - Symfony Backend"
echo "============================================"
echo ""

# Проверка что мы в контейнере
if [ ! -f "/var/www/backend/composer.json" ]; then
    echo "❌ Ошибка: запустите этот скрипт внутри PHP контейнера"
    echo "   Команда: docker-compose exec php bash"
    echo "   Затем: bash /var/www/INSTALL.sh"
    exit 1
fi

cd /var/www/backend

echo "📦 Шаг 1: Очистка старых зависимостей..."
rm -rf vendor composer.lock var/cache/* var/log/*

echo ""
echo "📥 Шаг 2: Установка Composer зависимостей..."
composer install --no-interaction --optimize-autoloader

if [ $? -ne 0 ]; then
    echo ""
    echo "❌ Ошибка при установке зависимостей"
    echo "Попробуйте вручную: composer install"
    exit 1
fi

echo ""
echo "🔑 Шаг 3: Генерация JWT ключей..."
mkdir -p config/jwt

# Проверяем существуют ли ключи
if [ -f "config/jwt/private.pem" ]; then
    echo "⚠️  JWT ключи уже существуют, пропускаем..."
else
    echo "Генерируем новые JWT ключи..."
    echo "ВАЖНО: Введите пароль из .env (JWT_PASSPHRASE)"
    echo "По умолчанию: your_jwt_passphrase_change_in_production"
    echo ""
    
    openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
    
    if [ $? -eq 0 ]; then
        openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout
        echo "✅ JWT ключи созданы"
    else
        echo "⚠️  JWT ключи не созданы (можно сделать позже)"
    fi
fi

echo ""
echo "🗄️  Шаг 4: Создание базы данных..."
php bin/console doctrine:database:create --if-not-exists

if [ $? -ne 0 ]; then
    echo "⚠️  База данных уже существует или ошибка подключения"
fi

echo ""
echo "📋 Шаг 5: Создание миграций..."
php bin/console doctrine:migrations:diff --no-interaction

echo ""
echo "🔄 Шаг 6: Применение миграций..."
php bin/console doctrine:migrations:migrate --no-interaction

echo ""
echo "🧹 Шаг 7: Очистка кэша..."
php bin/console cache:clear

echo ""
echo "✅ УСТАНОВКА ЗАВЕРШЕНА!"
echo "======================="
echo ""
echo "Проверьте работу:"
echo "  - API Healthcheck: curl http://localhost:8080/api/health"
echo "  - Или в браузере: http://localhost:8080/api/health"
echo ""
echo "Для создания админа:"
echo "  php bin/console app:create-admin admin@example.com password123"
echo ""
