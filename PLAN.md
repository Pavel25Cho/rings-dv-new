# План разработки каталога колец на Symfony + Vue 3

## Архитектура проекта

**Технологический стек:**
- **Backend**: Symfony 7.2 + Doctrine ORM
- **Frontend**: Vue 3 (Composition API) + Vite
- **БД**: MySQL 8.0
- **Аутентификация**: Symfony Security + JWT
- **Email**: Symfony Mailer + Messenger (очереди)
- **Импорт Excel**: PhpSpreadsheet
- **Контейнеризация**: Docker Compose

**Принципы:**
- Разделение Backend (API) и Frontend (SPA)
- Repository Pattern для работы с БД
- Service Layer для бизнес-логики
- DTO для передачи данных
- Event-driven архитектура для уведомлений

---

## 📁 Структура проекта

```
rings-catalog/
├── backend/                          # Symfony приложение
│   ├── src/
│   │   ├── Entity/                   # Сущности (User, RingGroup, Ring, Chat, etc)
│   │   ├── Repository/               # Репозитории Doctrine
│   │   ├── Controller/
│   │   │   ├── Api/                  # API контроллеры
│   │   │   │   ├── AuthController.php
│   │   │   │   ├── CatalogController.php
│   │   │   │   ├── ChatController.php
│   │   │   │   └── Admin/            # Админские контроллеры
│   │   │   └── WebController.php     # Главная страница (отдача Vue SPA)
│   │   ├── Service/                  # Бизнес-логика
│   │   │   ├── CatalogService.php
│   │   │   ├── ChatService.php
│   │   │   ├── OrderService.php
│   │   │   ├── ExcelImportService.php
│   │   │   └── EmailNotificationService.php
│   │   ├── DTO/                      # Data Transfer Objects
│   │   │   ├── CreateRingGroupDTO.php
│   │   │   ├── FilterDTO.php
│   │   │   └── ChatMessageDTO.php
│   │   ├── EventListener/            # Слушатели событий
│   │   │   └── ChatMessageListener.php (отправка email)
│   │   ├── Security/                 # Аутентификация
│   │   │   ├── Voter/                # Voters для авторизации
│   │   │   └── JWTAuthenticator.php
│   │   ├── MessageHandler/           # Обработчики очередей
│   │   │   ├── SendEmailHandler.php
│   │   │   └── ProcessExcelHandler.php
│   │   └── Helper/                   # Вспомогательные классы
│   │       ├── ExcelParser.php
│   │       └── FileUploadHelper.php
│   ├── config/
│   │   ├── packages/                 # Конфигурация пакетов
│   │   ├── routes/                   # Маршруты
│   │   └── services.yaml             # DI контейнер
│   ├── migrations/                   # Doctrine миграции
│   ├── public/
│   │   ├── index.php                 # Точка входа
│   │   └── uploads/                  # Загруженные файлы
│   ├── templates/
│   │   └── base.html.twig           # Базовый шаблон для SPA
│   ├── composer.json
│   └── .env
│
├── frontend/                         # Vue 3 приложение
│   ├── src/
│   │   ├── components/               # Общие компоненты
│   │   │   ├── RingGroupCard.vue
│   │   │   ├── RingTable.vue
│   │   │   ├── ChatComponent.vue
│   │   │   ├── FilterPanel.vue
│   │   │   ├── ImageUpload.vue
│   │   │   └── Navbar.vue
│   │   ├── views/                    # Страницы
│   │   │   ├── Home.vue
│   │   │   ├── Catalog.vue
│   │   │   ├── Login.vue
│   │   │   ├── Register.vue
│   │   │   ├── Profile.vue
│   │   │   ├── Chats.vue
│   │   │   └── admin/
│   │   │       ├── Dashboard.vue
│   │   │       ├── Groups.vue
│   │   │       ├── Rings.vue
│   │   │       ├── Import.vue
│   │   │       ├── Clients.vue
│   │   │       └── Settings.vue
│   │   ├── composables/              # Переиспользуемая логика
│   │   │   ├── useAuth.js
│   │   │   ├── useCart.js
│   │   │   ├── useChat.js
│   │   │   └── useApi.js
│   │   ├── mixins/                   # Миксины
│   │   │   ├── authMixin.js
│   │   │   └── formMixin.js
│   │   ├── stores/                   # Pinia stores
│   │   │   ├── auth.js
│   │   │   ├── catalog.js
│   │   │   └── chat.js
│   │   ├── router/
│   │   │   └── index.js
│   │   ├── assets/
│   │   │   └── styles/
│   │   │       └── main.css
│   │   ├── App.vue
│   │   └── main.js
│   ├── package.json
│   ├── vite.config.js
│   └── index.html
│
├── docker/
│   ├── nginx/
│   │   └── default.conf
│   ├── php/
│   │   └── Dockerfile
│   └── mysql/
│       └── init.sql
│
├── docker-compose.yml
└── README.md
```

---

## 🗄️ Схема базы данных

### Таблица: `users`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
email           VARCHAR(180) UNIQUE NOT NULL
password        VARCHAR(255) NOT NULL
roles           JSON NOT NULL                    -- ['ROLE_USER'] или ['ROLE_ADMIN']
is_blocked      BOOLEAN DEFAULT FALSE
created_at      DATETIME NOT NULL
updated_at      DATETIME NOT NULL
```

### Таблица: `ring_groups`
```sql
id                  BIGINT PRIMARY KEY AUTO_INCREMENT
type_code           VARCHAR(50) NOT NULL            -- SPW, PGR, MHM
name_ru             VARCHAR(255)
name_en             VARCHAR(255)
brand               VARCHAR(100)
material_en         VARCHAR(100)
material_ru         VARCHAR(100)
photo_url           VARCHAR(255)
dimensions_photo_url VARCHAR(255)
column_names        JSON                            -- {"1": "OD", "2": "ID", ...}
is_hidden           BOOLEAN DEFAULT FALSE
created_at          DATETIME NOT NULL
updated_at          DATETIME NOT NULL

INDEX idx_type_code (type_code)
INDEX idx_brand (brand)
```

### Таблица: `rings`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
ring_group_id   BIGINT NOT NULL
part_number     VARCHAR(100) NOT NULL              -- SPW60, PGR-16-3.2
dimensions      JSON NOT NULL                      -- ["60", "46", "8.5", "9"]
in_stock        BOOLEAN DEFAULT TRUE
price           DECIMAL(10,2)
photo_url       VARCHAR(255)
is_hidden       BOOLEAN DEFAULT FALSE
created_at      DATETIME NOT NULL
updated_at      DATETIME NOT NULL

FOREIGN KEY (ring_group_id) REFERENCES ring_groups(id) ON DELETE CASCADE
INDEX idx_part_number (part_number)
INDEX idx_in_stock (in_stock)
```

### Таблица: `chats`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
user_id         BIGINT NOT NULL
admin_id        BIGINT
last_message_at DATETIME
created_at      DATETIME NOT NULL

FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
FOREIGN KEY (admin_id) REFERENCES users(id) ON DELETE SET NULL
INDEX idx_user_id (user_id)
```

### Таблица: `chat_messages`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
chat_id         BIGINT NOT NULL
sender_id       BIGINT NOT NULL
message_text    TEXT NOT NULL
is_read         BOOLEAN DEFAULT FALSE
created_at      DATETIME NOT NULL

FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
INDEX idx_chat_id (chat_id)
INDEX idx_is_read (is_read)
```

### Таблица: `order_items`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
chat_id         BIGINT NOT NULL
ring_id         BIGINT NOT NULL
quantity        INT NOT NULL DEFAULT 1
price           DECIMAL(10,2)
created_at      DATETIME NOT NULL

FOREIGN KEY (chat_id) REFERENCES chats(id) ON DELETE CASCADE
FOREIGN KEY (ring_id) REFERENCES rings(id) ON DELETE CASCADE
```

### Таблица: `import_logs`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
admin_id        BIGINT NOT NULL
filename        VARCHAR(255) NOT NULL
status          VARCHAR(50) NOT NULL               -- success, failed, processing
errors          JSON
created_at      DATETIME NOT NULL

FOREIGN KEY (admin_id) REFERENCES users(id)
INDEX idx_admin_id (admin_id)
INDEX idx_status (status)
```

### Таблица: `site_settings`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
setting_key     VARCHAR(100) UNIQUE NOT NULL
setting_value   TEXT
updated_at      DATETIME NOT NULL
```

### Таблица: `admin_emails`
```sql
id              BIGINT PRIMARY KEY AUTO_INCREMENT
email           VARCHAR(180) NOT NULL
is_active       BOOLEAN DEFAULT TRUE
created_at      DATETIME NOT NULL
```

---

## 🔄 Разделение на этапы

### **ЭТАП 1: Фундамент (текущая сессия)**

**Цель:** Создать рабочую инфраструктуру, на которой можно строить функциональность

✅ **1.1. Инициализация проекта**
- Создание структуры папок
- Установка Symfony 7.2
- Настройка Composer зависимостей
- Конфигурация .env

✅ **1.2. Docker окружение**
- docker-compose.yml (PHP 8.3, MySQL 8.0, Nginx, Redis, MailHog)
- Dockerfile для PHP с нужными расширениями
- Nginx конфигурация
- Настройка volumes

✅ **1.3. База данных**
- Создание всех Entity классов
- Doctrine миграции
- Fixtures для тестовых данных (админ, группы колец)

✅ **1.4. Frontend структура**
- Инициализация Vue 3 + Vite
- Установка зависимостей (Vue Router, Pinia, Axios)
- Базовая структура папок
- Настройка Tailwind CSS
- App.vue и router

✅ **1.5. Базовая интеграция**
- Symfony отдаёт index.html для Vue SPA
- CORS настройка
- Healthcheck endpoint (GET /api/health)

**Результат этапа 1:**
- Проект запускается через Docker
- БД создана с полной схемой
- Vue приложение открывается в браузере
- API healthcheck работает

---

### **ЭТАП 2: Функциональность (следующая сессия)**

**Цель:** Реализовать всю бизнес-логику проекта

🔲 **2.1. Аутентификация**
- AuthController (register, login, logout, refresh)
- JWT токены (LexikJWTAuthenticationBundle)
- Security voters
- Middleware для проверки блокировки
- Frontend: страницы логина/регистрации

🔲 **2.2. Каталог (публичная часть)**
- CatalogService + Repository
- API endpoints:
  - GET /api/catalog/groups (с фильтрами)
  - GET /api/catalog/groups/{id}
  - GET /api/catalog/rings?groupId=X
- Frontend:
  - Страница каталога
  - Компоненты фильтров
  - Карточки групп
  - Раскрывающиеся таблицы размеров

🔲 **2.3. Чаты и заказы**
- ChatService + Repository
- API endpoints:
  - POST /api/chats (создание чата)
  - GET /api/chats (список чатов)
  - GET /api/chats/{id}/messages
  - POST /api/chats/{id}/messages
- Event listener для email уведомлений
- Frontend: компонент чата (как Farpost)

🔲 **2.4. Личный кабинет**
- ProfileController
- Редактирование профиля
- Список чатов/заказов
- Frontend: страницы профиля

🔲 **2.5. Админ-панель - CRUD**
- Admin\GroupController (CRUD групп)
- Admin\RingController (CRUD колец)
- Admin\ClientController (блокировка)
- FileUploadHelper для загрузки фото
- Frontend: админские страницы

🔲 **2.6. Импорт Excel**
- ExcelImportService
- ExcelParser helper
- Парсинг листа "Главная"
- Парсинг листов групп (SPW, PGR, etc)
- Очистка иероглифов
- Merge логика
- Messenger для фоновой обработки
- Frontend: страница импорта с прогрессом

🔲 **2.7. Настройки сайта**
- SettingsService
- API для редактирования настроек
- Управление списком admin emails
- Frontend: страница настроек

🔲 **2.8. Email уведомления**
- Symfony Mailer + Messenger
- Шаблоны писем (Twig)
- Очереди для асинхронной отправки
- Уведомления о новых сообщениях

🔲 **2.9. Полировка**
- Мобильная адаптация
- Оптимизация запросов (eager loading)
- Валидация всех форм
- Обработка ошибок
- Логирование

🔲 **2.10. Деплой**
- Production конфигурация
- HTTPS настройка
- Backup стратегия
- Документация по развёртыванию

---

## 📦 Основные Symfony пакеты

```json
{
  "symfony/framework-bundle": "^7.2",
  "symfony/orm-pack": "^2.4",
  "symfony/maker-bundle": "^1.60",
  "symfony/security-bundle": "^7.2",
  "symfony/mailer": "^7.2",
  "symfony/messenger": "^7.2",
  "symfony/validator": "^7.2",
  "doctrine/doctrine-bundle": "^2.13",
  "doctrine/orm": "^3.2",
  "lexik/jwt-authentication-bundle": "^3.1",
  "nelmio/cors-bundle": "^2.5",
  "phpoffice/phpspreadsheet": "^2.3",
  "symfony/uid": "^7.2"
}
```

## 📦 Frontend зависимости

```json
{
  "vue": "^3.5.0",
  "vue-router": "^4.4.0",
  "pinia": "^2.2.0",
  "axios": "^1.7.0",
  "tailwindcss": "^3.4.0",
  "vite": "^5.4.0"
}
```

---

## 🎯 API Endpoints (полный список)

### Публичные
- POST /api/auth/register
- POST /api/auth/login
- POST /api/auth/logout
- POST /api/auth/refresh
- GET /api/catalog/groups
- GET /api/catalog/groups/{id}
- GET /api/catalog/rings

### Авторизованные (клиент)
- GET /api/profile
- PATCH /api/profile
- GET /api/chats
- POST /api/chats
- GET /api/chats/{id}/messages
- POST /api/chats/{id}/messages

### Админские
- GET /api/admin/groups
- POST /api/admin/groups
- PATCH /api/admin/groups/{id}
- DELETE /api/admin/groups/{id}
- GET /api/admin/rings
- POST /api/admin/rings
- PATCH /api/admin/rings/{id}
- DELETE /api/admin/rings/{id}
- POST /api/admin/import
- GET /api/admin/import/history
- GET /api/admin/clients
- POST /api/admin/clients/{id}/block
- POST /api/admin/clients/{id}/unblock
- GET /api/admin/chats
- GET /api/admin/settings
- PATCH /api/admin/settings

---

## 🚀 Команды для запуска

```bash
# Первый запуск
docker-compose up -d
docker-compose exec php composer install
docker-compose exec php bin/console doctrine:migrations:migrate
docker-compose exec php bin/console doctrine:fixtures:load

# Frontend
cd frontend
npm install
npm run dev

# Доступ
Frontend: http://localhost:5173
Backend API: http://localhost:8080/api
MailHog (тестовая почта): http://localhost:8025
```

---

## 📝 Примеры кода

### Entity пример
```php
#[ORM\Entity(repositoryClass: RingGroupRepository::class)]
class RingGroup
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    private string $typeCode;

    #[ORM\Column(type: 'json', nullable: true)]
    private ?array $columnNames = null;

    #[ORM\OneToMany(mappedBy: 'ringGroup', targetEntity: Ring::class)]
    private Collection $rings;
    
    // getters/setters...
}
```

### Service пример
```php
class CatalogService
{
    public function __construct(
        private RingGroupRepository $groupRepository,
        private RingRepository $ringRepository
    ) {}

    public function getFilteredGroups(FilterDTO $filters): array
    {
        return $this->groupRepository->findByFilters($filters);
    }
}
```

### Repository пример
```php
class RingGroupRepository extends ServiceEntityRepository
{
    public function findByFilters(FilterDTO $filters): array
    {
        $qb = $this->createQueryBuilder('g')
            ->where('g.isHidden = false');

        if ($filters->typeCode) {
            $qb->andWhere('g.typeCode = :type')
               ->setParameter('type', $filters->typeCode);
        }

        return $qb->getQuery()->getResult();
    }
}
```

### Vue Composable пример
```javascript
// composables/useAuth.js
export function useAuth() {
  const authStore = useAuthStore()
  
  const login = async (email, password) => {
    const response = await axios.post('/api/auth/login', { email, password })
    authStore.setToken(response.data.token)
    authStore.setUser(response.data.user)
  }
  
  return { login, logout, user: computed(() => authStore.user) }
}
```

---

## ✅ Критерии готовности MVP

- [ ] Каталог с фильтрацией работает
- [ ] Регистрация и вход выполняются
- [ ] Чаты работают с уведомлениями на email
- [ ] Админ может управлять группами и кольцами
- [ ] Импорт Excel обрабатывает файлы корректно
- [ ] Блокировка клиентов работает
- [ ] Мобильная версия корректно отображается
- [ ] Проект запускается на чистом сервере через Docker

---

## 🔒 Безопасность

- Все пароли хешируются (Symfony PasswordHasher)
- JWT токены с коротким временем жизни + refresh token
- CORS правильно настроен
- SQL injection защита (Doctrine ORM)
- XSS защита (Vue экранирует по умолчанию)
- CSRF токены для критичных операций
- Rate limiting на API endpoints
- Валидация всех входных данных
- Загрузка файлов только разрешённых типов

---

## 📊 Производительность

**Оптимизации:**
- Doctrine lazy loading для связей
- Eager loading где нужно (JOIN FETCH)
- Redis для кеширования списка групп
- MySQL индексы на часто используемые поля
- Vite code splitting для фронтенда
- Lazy loading роутов в Vue
- Оптимизация изображений при загрузке
- Messenger для тяжёлых операций (импорт, email)

**Ожидаемая производительность:**
- Загрузка каталога: < 300ms
- API response time: < 100ms
- Импорт 1000 колец: < 30 сек (в фоне)
- Потребление памяти PHP: ~128MB
- Фронтенд bundle: < 500KB gzipped

---

## 🎨 UI/UX принципы

- Минималистичный дизайн
- Tailwind CSS для быстрой вёрстки
- Mobile-first подход
- Skeleton loaders при загрузке
- Оптимистичные UI обновления
- Понятные сообщения об ошибках
- Подтверждения критичных действий
- Хлебные крошки для навигации
- Иконки для лучшей визуализации

---

**Дата создания плана:** 16 мая 2026  
**Версия:** 1.0  
**Статус:** Готов к реализации Этапа 1
