# Контакты

Symfony-приложение с SQLite. Одна таблица `contacts`: `id`, `name`, `phone`, `email`, `note`.

При первом запуске `bin/init-db.php` создаёт таблицу и пять начальных контактов из `database/init.sql`.
Если таблица уже существует, записи не изменяются и не добавляются повторно.
Файл базы по умолчанию: `var/contacts.db`. После потери файла база создаётся заново.

## Render

1. Разместить проект в GitHub/GitLab и создать на Render **New → Web Service** из репозитория.
2. Выбрать **Language: Docker** и **Instance Type: Free**.
3. Если проект вложен в репозиторий, указать папку проекта в **Root Directory**.
4. Путь Dockerfile: `./Dockerfile`.
5. **Docker Command** можно оставить пустым: в Dockerfile уже задан запуск.
   Если команда нужна явно, указать:

   ```sh
   sh ./start.sh
   ```

Отдельная база, диск и Build Command не требуются. `APP_ENV` и `APP_DEBUG`
заданы в образе, путь SQLite указан в `config/packages/doctrine.yaml`.
`DATABASE_URL` больше не используется. `APP_SECRET` генерируется при запуске,
если его не задали в Environment.
Apache слушает порт из `PORT` (по умолчанию `10000`).

На бесплатном Render изменения SQLite пропадают при деплое, перезапуске или засыпании.
При следующем запуске восстанавливаются пять начальных записей.

## Локальный запуск

Без Docker:

```sh
composer install
php bin/init-db.php
php -S 127.0.0.1:8000 -t public public/router.php
```

С Docker:

```sh
docker build -t contacts-app .
docker run --rm -p 8000:10000 contacts-app
```

## Страницы

- `/` — интерфейс контактов.
- `/api/contacts` — API.
- `/api/doc` — Swagger UI.
- `/api/redoc` — ReDoc.
- `/api/doc.json` — общая спецификация OpenAPI.

После изменения OpenAPI-атрибутов обновить спецификацию:

```sh
vendor/bin/openapi src/Controller -o public/api/doc.json -f json
```
