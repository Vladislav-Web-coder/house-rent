# 🏡 Simbungalow — сервис бронирования загородных домов

Fullstack-приложение для аренды домиков и апартаментов: публичный сайт с каталогом, календарём доступности и расчётом стоимости + админ-панель на Filament.

## Стек

**Backend**
- PHP 8.2+ / Laravel 12
- PostgreSQL 18
- Redis (кэш, очереди, сессии)
- Filament 3 (админ-панель) + Spatie Media Library (медиа объектов)
- Sanctum (API-аутентификация)
- SabreVObject / ics-parser — двусторонняя синхронизация календарей (Google/Apple ICS)
- spatie/laravel-sitemap — генерация sitemap.xml
- Уведомления: e-mail (SMTP/Mailpit) + мессенджер MAX (бот)

**Frontend**
- Vue 3 (SPA) + Vite 7
- Tailwind CSS 4
- Pinia, vue-router
- vee-validate + zod (валидация форм бронирования)
- Swiper, VueDatepicker, libphonenumber-js

## Основные возможности

- Каталог объектов (`house` / `apartament`) с галереями фото/видео и lightbox
- Расчёт стоимости: базовая цена за ночь + сезонные периоды + цены по дням недели + ценовые исключения
- Календарь доступности с учётом заявок, подтверждённых броней, заблокированных дат и внешних ICS-календарей
- Создание заявок на бронирование с валидацией и уведомлениями (e-mail + MAX)
- Экспорт собственного календаря объекта в `.ics` по секретному токену
- Автоотмена неактивных заявок, синхронизация внешних календарей по расписанию
- Админ-панель (`/control-panel`): управление объектами, заявками, ссылками на календари, договорами (PDF)

## Структура проекта

```
app/
├── Filament/          # админ-панель (Resources, Pages, Widgets)
├── Http/Controllers/  # Api/* — публичные эндпоинты, Admin/* — ручка панели
├── Listeners/         # события (обработка загруженных видео и др.)
├── Models/            # Property, BookingRequest, Booking, PricePeriod, ...
├── Notifications/     # канал MAX-бота, mail-шаблоны
├── Services/          # PricingService, AvailabilityService, CacheService, ...
routes/
├── api.php            # REST API для фронтенда (с rate limiting)
├── web.php            # SPA-роуты, sitemap.xml, скачивание договора, fallback
├── console.php        # планировщик (sync каждые 5 мин, отмена заявок hourly)
resources/js/
├── api/               # слой HTTP-запросов (axios)
├── components/        # BookingForm, DateRangePicker, YandexMap, Lightbox, ...
├── stores/            # Pinia
├── utils/             # formatPrice и др.
└── views/             # Home, PropertyView, PrivacyPolicy, NotFound
```

## Быстрый старт (Docker)

Требуется Docker и docker compose. Сервисы из `compose.yaml`: приложение (nginx + php-fpm), PostgreSQL, Redis, Mailpit.

```bash
git clone https://github.com/Vladislav-Web-coder/house-rent.git
cd house-rent

cp .env.example .env
docker compose up -d pgsql redis mailpit
docker compose run --rm app sh -c "
    composer install &&
    php artisan key:generate &&
    php artisan migrate --seed &&
    npm install && npm run build"
docker compose up -d app
```

Создать администратора (в панели могут работать только пользователи с `role = 'admin'`):

```bash
docker compose exec app php artisan tinker
>>> \App\Models\User::create(['name' => 'Admin', 'email' => 'admin@example.com',
        'password' => bcrypt('secret'), 'role' => 'admin']);
```

⚠️ При обновлении со старой версии: колонка `role` в таблице `users` nullable — убедитесь, что у существующих админов выставлено `role='admin'`, иначе они получат 403.

Точки входа по умолчанию:

| URL | Что |
|---|---|
| http://localhost:8080 | сайт |
| http://localhost:8080/control-panel | панель Filament (только `role=admin`) |
| http://localhost:8025 | Mailpit (просмотр писем) |
| localhost:5433 | PostgreSQL снаружи контейнера |

Без Docker локально поднимают стандартно: `php artisan serve` + `npm run dev`, при этом `DB_*`/`REDIS_*` в `.env` должны указывать на ваши PostgreSQL и Redis.

## Переменные окружения

Ключевые (полный список — `.env.example`):

| Переменная | Назначение |
|---|---|
| `APP_URL` | домен сайта (используется в письмах и ссылках) |
| `DB_CONNECTION=pgsql` | СУБД; `CACHE_STORE=redis` и `QUEUE_CONNECTION=redis` обязательны (кэш работает на тегах) |
| `MAIL_*` | SMTP (Mailpit в dev) |
| `MAX_BOT_TOKEN` | токен MAX-бота для уведомлений (пусто — канал отключается мягко) |

## Команды и планировщик

```bash
php artisan calendar:sync-external [--property=ID]   # импорт внешних ICS (по расписанию — каждые 5 мин)
php artisan bookings:cancel-expired                  # отмена просроченных заявок (hourly)
php artisan video:optimize [--id=MEDIA_ID]           # конвертация видео (обычно запускается автоматически через очередь при загрузке)
```

Для продакшена нужен cron с `php artisan schedule:work` (или системный cron на `schedule:run`).

## Тесты

```bash
php artisan test
# или в контейнере
docker compose exec app php artisan test
```

## Требования к серверу (продакшен)

Проект рассчитан на малый трафик (до ~1000 посетителей/мес) — достаточно **одного VPS**:

| Ресурс | Минимум | Комфортно |
|---|---|---|
| vCPU | 2 | 4 (важно для ffmpeg при обработке видео) |
| RAM | 4 ГБ | 8 ГБ |
| Диск | 40–60 ГБ SSD/NVMe | зависит от объёма медиа + бэкапы БД |

Развертывание: Docker Compose (`laravel.test` + `nginx` + `pgsql` + `redis`) на Ubuntu 22.04/24.04, перед ним Caddy или nginx-proxy для HTTPS. Очередь (`QUEUE_CONNECTION=redis`) обрабатывается тем же контейнером приложения через supervisord — отдельные серверы не нужны. Подходящие тарифы: Hetzner CX22/CX32 или аналоги у российских провайдеров (~€10–20/мес).

Если видео в галереях станет много и конвертация будет нагружать сервер — перенесите медиа на S3-совместимое хранилище + CDN (MediaLibrary поддерживает удалённые диски почти без изменений в коде).

## Полезное

```bash
vendor/bin/pint                       # форматирование PHP (Laravel Pint)
php artisan route:list
php artisan pail                      # просмотр логов в реальном времени
```
