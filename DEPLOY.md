# Production Deployment Guide

## ⚠️ Важно для Production

**Зависимости уже установлены в Docker образе!**  
**НЕ запускайте `composer install` на production сервере!**

## Быстрый старт на сервере

### 1. Клонирование проекта

```bash
git clone <repository-url> Symfony-test
cd Symfony-test
```

### 2. Настройка переменных окружения

Создайте `.env` файл для production:

```bash
cat > .env << 'EOF'
APP_ENV=prod
APP_SECRET=ИЗМЕНИТЕ_НА_СЛУЧАЙНУЮ_СТРОКУ
DATABASE_URL="mysql://symfony_user:symfony_pass@mysql:3306/symfony_db?serverVersion=8.0&charset=utf8mb4"
EOF
```

**Обязательно измените `APP_SECRET`** на случайную строку!

### 3. Сборка и запуск с production конфигурацией

```bash
# Собрать образы
docker compose -f docker-compose.prod.yml build

# Запустить контейнеры
docker compose -f docker-compose.prod.yml up -d
```

### 4. Запуск миграций базы данных

```bash
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 5. Загрузка тестовых данных (опционально)

```bash
docker compose -f docker-compose.prod.yml exec php php bin/console app:load-books-data
```

**Внимание**: Загрузка 300,000 книг может занять несколько минут.

### 6. Проверка работы

Откройте в браузере: `http://your-server:8080`

## Отличия Production от Development

### Development (`docker-compose.yml`)
- ✅ Монтирует локальную директорию как volume
- ✅ Изменения в коде сразу видны
- ✅ Удобно для разработки

### Production (`docker-compose.prod.yml`)
- ✅ Использует файлы из Docker образа
- ✅ Зависимости уже установлены
- ✅ Оптимизирован autoloader
- ✅ OPcache включен
- ❌ НЕ монтирует локальную директорию

## Частые проблемы

### ❌ Ошибка: "vendor does not exist and could not be created"

**Причина**: Попытка запустить `composer install` при использовании `docker-compose.yml` (development конфигурация).

**Решение**: 
1. Используйте `docker-compose.prod.yml` для production
2. НЕ запускайте `composer install` - зависимости уже в образе

```bash
# ❌ НЕПРАВИЛЬНО (для production)
docker compose exec php composer install

# ✅ ПРАВИЛЬНО (зависимости уже установлены)
docker compose -f docker-compose.prod.yml up -d
```

### ❌ Ошибка: "Could not open input file: composer"

**Причина**: Неправильная команда.

**Решение**: Используйте `composer`, а не `php composer`:

```bash
# ❌ НЕПРАВИЛЬНО
docker compose exec php php composer install

# ✅ ПРАВИЛЬНО
docker compose exec php composer install
```

## Команды для управления

### Просмотр логов

```bash
# Все логи
docker compose -f docker-compose.prod.yml logs -f

# Только PHP
docker compose -f docker-compose.prod.yml logs -f php

# Только Nginx
docker compose -f docker-compose.prod.yml logs -f nginx
```

### Выполнение команд Symfony

```bash
# Очистить кэш
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear

# Запустить консольную команду
docker compose -f docker-compose.prod.yml exec php php bin/console <command>
```

### Остановка и перезапуск

```bash
# Остановить
docker compose -f docker-compose.prod.yml stop

# Перезапустить
docker compose -f docker-compose.prod.yml restart

# Остановить и удалить контейнеры
docker compose -f docker-compose.prod.yml down
```

### Обновление приложения

```bash
# 1. Получить обновления
git pull origin main

# 2. Пересобрать образ
docker compose -f docker-compose.prod.yml build php

# 3. Перезапустить контейнеры
docker compose -f docker-compose.prod.yml up -d

# 4. Запустить миграции (если есть)
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

# 5. Очистить кэш
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear
```

## Безопасность для Production

### Обязательно измените:

1. **APP_SECRET** - генерируйте случайную строку
2. **MYSQL_ROOT_PASSWORD** - установите сильный пароль
3. **MYSQL_PASSWORD** - установите сильный пароль
4. **Порты** - измените порты если нужно

### Рекомендации:

- Используйте HTTPS (настройте reverse proxy с SSL)
- Настройте firewall
- Регулярно обновляйте зависимости
- Используйте Docker secrets для чувствительных данных
- Настройте резервное копирование БД

## API Endpoints

Все API запросы требуют заголовок: `X-API-User-Name: admin`

```bash
# Получить список книг
curl -H "X-API-User-Name: admin" "http://your-server:8080/api/v1/books/list?page=1&limit=50"

# Получить книгу по ID
curl -H "X-API-User-Name: admin" "http://your-server:8080/api/v1/books/by-id?id=1"

# Обновить книгу
curl -X POST -H "X-API-User-Name: admin" -H "Content-Type: application/json" \
  -d '{"id":1,"title":"New Title"}' \
  "http://your-server:8080/api/v1/books/update"

# Удалить книгу
curl -X DELETE -H "X-API-User-Name: admin" \
  "http://your-server:8080/api/v1/books/1"
```

## Мониторинг

### Проверка статуса контейнеров

```bash
docker compose -f docker-compose.prod.yml ps
```

### Проверка использования ресурсов

```bash
docker stats
```

### Проверка здоровья приложения

```bash
curl -I http://localhost:8080
```

## Поддержка

При возникновении проблем проверьте:
1. Логи контейнеров
2. Статус контейнеров
3. Доступность порта 8080
4. Подключение к базе данных

