Стек:
- **PHP**: 8.2
- **Symfony**: 7.1
- **MySQL**: 8.0
- **Nginx**: Alpine
- **Docker & Docker Compose**

Требования

- Docker 20.10+
- Docker Compose 2.0+

Установка и запуск

1. Клонирование репозитория

```bash
git clone <repository-url>
cd SymfonyTest
```

2. Настройка переменных окружения

Скопируйте файл `.env.example` в `.env` и настройте переменные:

```bash
cp .env.example .env
```

Для production обязательно измените:
- `APP_ENV=prod`
- `APP_SECRET` на уникальное значение
- `DATABASE_URL` на продакшн БД

3. Запуск Docker контейнеров

```bash
docker-compose up -d
```

Это запустит три контейнера:
- `symfony_php` - PHP-FPM 8.2
- `symfony_nginx` - Nginx веб-сервер
- `symfony_mysql` - MySQL 8.0 база данных

4. Установка зависимостей

```bash
docker-compose exec php composer install
```

5. Запуск миграций

```bash
docker-compose exec php php bin/console doctrine:migrations:migrate --no-interaction
```

6. Загрузка тестовых данных

Для создания 3 авторов и ~300,000 книг (по 100,000 на автора):

```bash
docker-compose exec php php bin/console app:load-books-data
```

**Внимание:** Загрузка может занять несколько минут.

7. Проверка работы

Откройте в браузере: http://localhost:8080

Использование

Административная панель

Доступна по адресу: `http://localhost:8080/`

- **Главная страница**: `/`
- **Список авторов**: `/admin/authors`
- **Создание автора**: `/admin/authors/new`
- **Список книг**: `/admin/books`
- **Создание книги**: `/admin/books/new`

REST API

Все API эндпоинты требуют заголовок:
```
X-API-User-Name: admin
```

## Команды для разработки

### Просмотр логов

```bash
# Все логи
docker-compose logs -f

# Логи PHP
docker-compose logs -f php

# Логи Nginx
docker-compose logs -f nginx
```

Выполнение команд в контейнере

```bash
# Войти в PHP контейнер
docker-compose exec php bash

# Очистить кэш
docker-compose exec php php bin/console cache:clear

# Создать новую миграцию
docker-compose exec php php bin/console make:migration

# Выполнить миграции
docker-compose exec php php bin/console doctrine:migrations:migrate
```

Остановка контейнеров

```bash
# Остановить
docker-compose stop

# Остановить и удалить контейнеры
docker-compose down

# Удалить контейнеры с volumes (удалит БД!)
docker-compose down -v
```
База данных

Схема базы данных доступна в файле `database_schema.sql`.

Таблицы

- **author**: Информация об авторах
  - id, name, created_at, updated_at

- **book**: Информация о книгах
  - id, author_id, title, isbn, published_year, created_at, updated_at

## Production деплой

### Настройка для production

1. Измените `APP_ENV=prod` в `.env`
2. Сгенерируйте новый `APP_SECRET`
3. Настройте production DATABASE_URL
4. Пересоберите Docker образ:

```bash
docker-compose build --no-cache
docker-compose up -d
```

