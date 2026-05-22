# Rings Catalog - Каталог автомобильных колец

## Быстрый старт

### Запуск проекта

**1. Backend (Docker)**
```bash
cd /var/www/rings-dv-new/backend
docker-compose up -d
docker-compose exec php php bin/console cache:clear
```

**2. Frontend (production build уже готов)**
- Фронтенд собран и находится в `/frontend/dist`
- Nginx автоматически раздает статику

### Доступ к приложению

- **Сайт:** http://5.129.248.221
- **MailHog:** http://5.129.248.221:8025

## API Endpoints

### Авторизация
- `POST /api/auth/register` - регистрация
- `POST /api/auth/login` - вход (возвращает JWT токен)
- `GET /api/auth/me` - данные пользователя
- `POST /api/auth/logout` - выход

### Каталог
- `GET /api/catalog/groups` - группы колец
- `GET /api/catalog/rings` - кольца

## Разработка

### Backend

```bash
cd /var/www/rings-dv-new/backend

# Логи
docker-compose logs -f php

# Консольные команды
docker-compose exec php php bin/console <command>

# Миграции
docker-compose exec php php bin/console doctrine:migrations:migrate

# Создать админа
docker-compose exec php php bin/console app:create-admin admin@example.com password
```

### Frontend

```bash
cd /var/www/rings-dv-new/frontend

# Разработка
npm run dev

# Сборка
npm run build

# После сборки
chmod -R 755 dist/
chown -R www-data:www-data dist/
```

## Сервисы

### Nginx (системный)
```bash
systemctl restart nginx
systemctl reload nginx
tail -f /var/log/nginx/vlad-rings-error.log
```

### Docker
```bash
cd /var/www/rings-dv-new/backend
docker-compose ps
docker-compose restart php
docker-compose logs -f
```

## Конфигурация

- Nginx: `/etc/nginx/sites-available/vlad-rings`
- Backend ENV: `/var/www/rings-dv-new/backend/.env`
- Frontend ENV: `/var/www/rings-dv-new/frontend/.env`
- Docker Compose: `/var/www/rings-dv-new/backend/docker-compose.yml`

## Порты

| Сервис | Порт | Доступ |
|--------|------|--------|
| Nginx | 80 | Публичный |
| PHP-FPM | 9000 | Localhost |
| MySQL | 3307 | Внешний |
| Redis | 6380 | Внешний |
| MailHog Web | 8025 | Публичный |

## Тестовый пользователь

- Email: `test123@test.com`
- Пароль: `123456`
