# JDOC API

**JDOC API** — это RESTful API-сервис, предназначенный для управления и публикации JSON-документов с разграничением прав доступа пользователей. Проект построен на современном и производительном стеке технологий с использованием контейнеризации Docker.

---

## 🛠 Технологический стек

* **Язык программирования:** PHP 8.4 (FPM) + встроенный Xdebug (режим Coverage)
* **Фреймворк:** Symfony 8.1.* + Symfony Messenger + Symfony Mailer
* **База данных:** PostgreSQL 16 (Alpine)
* **Брокер сообщений (Очереди):** RabbitMQ 4 (Management Alpine)
* **Кэширование и сессии:** Redis 7 (Alpine)
* **Почтовый сервер (SMTP заглушка):** Mailpit (доступен локально для просмотра писем)
* **Веб-сервер:** Nginx (Alpine)
* **Аутентификация:** JWT (JSON Web Token) посредством установки авторизационной HTTP-куки
* **Качество кода:** PHPStan (уровень 6)

---

## 🐳 Быстрый старт в Docker

Проект полностью готов к разворачиванию в Docker. По умолчанию приложение доступно на локальном хосте на порту `8080`.

### 1. Сборка и запуск контейнеров
Соберите Docker-образы (с предустановленным расширением Xdebug) и запустите сервисы в фоновом режиме:
```bash
docker compose up -d --build
```

После запуска локально разворачиваются следующие инструменты и панели управления:
* **Основное API:** `http://localhost:82`
* **Mailpit (Панель просмотра писем):** `http://localhost:8025` (SMTP-приемник доступен на внутреннем порту `1025`)
* **RabbitMQ Management (Панель очередей):** `http://localhost:15672` (логин/пароль по умолчанию: `guest`/`guest`)
* **Redis Server:** Доступен внутри сети Docker на порту `6379`
* **PostgreSQL:** Доступен на хост-машине на порту `5432`

### 2. Инициализация базы данных и миграции
Создайте схему базы данных и примените все существующие миграции Doctrine:
```bash
docker compose exec php bin/console doctrine:database:create
docker compose exec php bin/console doctrine:migrations:migrate -n
```

### 3. Подготовка тестового окружения
Создайте базу данных для запуска автотестов:
```bash
docker compose exec php bin/console doctrine:database:create --env=test
docker compose exec php bin/console doctrine:migrations:migrate -n --env=test
```


---

## 🧑‍💻 Инструменты разработки и тестирования

Все команды выполняются внутри изолированного контейнера `php` с использованием Composer-скриптов. Благодаря пакету `dama/doctrine-test-bundle`, все тесты базы данных изолированы и автоматически откатываются через транзакции.

* **Запуск автотестов (PHPUnit):**
  ```bash
  docker compose exec php composer test
  ```
* **Генерация отчета о покрытии кода (Code Coverage HTML):**
  Запуск генератора покрытия кода с использованием встроенного драйвера Xdebug:
  ```bash
  docker compose exec --env XDEBUG_MODE=coverage php vendor/bin/phpunit --coverage-html var/coverage
  ```
  После генерации откройте файл `var/coverage/index.html` в браузере.

* **Запуск статического анализа кода (PHPStan):**
  ```bash
  docker compose exec php composer phpstan
  ```
* **Очистка кэша Symfony (для dev и test окружений):**
  ```bash
  docker compose exec php bin/console cache:clear
  ```

---

## 📑 Спецификация API (Endpoints)

Все запросы к закрытым эндпоинтам требуют наличия Bearer JWT-токена в заголовках или авторизационной куки (схема `Bearer` в OpenAPI).

### 🔐 Аутентификация и Регистрация (`Auth` & `Registr`)

| Метод | Путь | Описание | Доступ | Возвращаемый тип |
| :--- | :--- | :--- | :--- | :--- |
| `POST` | `/api/v1/auth` | Аутентификация пользователя (выпуск JWT-куки) | Публичный | `ResultSucess` (200) / `ErrorResult` (401) |
| `POST` | `/api/v1/registr` | Регистрация нового аккаунта (отправка email) | Публичный | `ResultSucess` (200) / `ErrorValidationResult` (422) |
| `GET` | `/api/v1/confirm/{key}` | Активация аккаунта по кэшированному токену | Публичный | `ResultSucess` (200) / `ErrorResult` (404) |
| `GET` | `/api/v1/profile` | Получение данных текущего пользователя | Авторизованный | `User` (200) |

### 📄 Управление документами (`Document`)

| Метод | Путь | Описание | Права (Voter) | Возвращаемый тип |
| :--- | :--- | :--- | :--- | :--- |
| `GET` | `/api/v1/document` | Получение списка документов с пагинацией | Авторизованный | `DocumentListResponse` (200) |
| `POST` | `/api/v1/document` | Создание нового JSON-документа (черновика) | Авторизованный | `Document` (200) / `ErrorResponse` (401) |
| `GET` | `/api/v1/document/{id}` | Получение полной информации о документе по ID | Публичный / Владелец | `Document` (200) / `ErrorResponse` (404) |
| `PUT` | `/api/v1/document/{id}` | Редактирование существующего черновика по ID | Только Владелец | `Document` (200) / `ErrorResponse` (401, 404) |
| `DELETE`| `/api/v1/document/{id}` | Удаление документа по его ID | Только Владелец | `{ "succes": true }` (200) / `ErrorResponse` (401, 404) |
| `POST` | `/api/v1/document/{id}/publish` | Публикация документа (перевод из черновика) | Только Владелец | `Document` (200) / `ErrorResponse` (401, 404) |

---

## 📝 Структура основных JSON-сущностей (Schemas)

### Документ (`Document`)
```json
{
  "idocid": 1,
  "state": "draft",
  "payload": {},
  "createAt": "2026-09-17T11:04:38Z",
  "modifyAt": "2026-09-17T11:07:00Z"
}
```

### Список документов с пагинацией (`DocumentListResponse`)
```json
{
  "pagination": {
    "page": 1,
    "perPage": 10,
    "total": 142
  },
  "document": [
    {
      "idocid": 1,
      "state": "published",
      "payload": {},
      "createAt": "2026-09-17T11:04:38Z",
      "modifyAt": "2026-09-17T11:07:00Z"
    }
  ]
}
```

### Профиль пользователя (`User`)
```json
{
  "iuserid": 12,
  "vclogin": "user_login",
  "vcemail": "user@example.com",
  "istateid": 2,
  "vcstate": "approved",
  "roles": ["ROLE_USER"]
}
```

### Схема ошибки (`ErrorResponse`)
```json
{
  "code": 404,
  "message": "Документ не найден"
}
```
