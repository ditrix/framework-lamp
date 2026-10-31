# Framework LAMP (Docker Compose)

Локальное development-окружение на базе Docker Compose:

- Apache + PHP (по умолчанию 8.3)
- MySQL / MariaDB
- phpMyAdmin
- Redis
- HTTPS на домене `https://framework.dev`

Стек предназначен **только для локальной разработки**, не для production.

---

## Требования

- Docker Engine
- Docker Compose v2 (`docker compose`)
- [mkcert](https://github.com/FiloSottile/mkcert) — локальные SSL-сертификаты
- Свободные порты на хосте: `80`, `443`, `3306`, `8080`, `6379`

---

## 1. Подготовка локального компьютера

### 1.1. Установка Docker

#### Ubuntu

```bash
sudo apt update
sudo apt install -y ca-certificates curl
sudo install -m 0755 -d /etc/apt/keyrings
sudo curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
sudo chmod a+r /etc/apt/keyrings/docker.asc

echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
  $(. /etc/os-release && echo \"$VERSION_CODENAME\") stable" | \
  sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

sudo usermod -aG docker "$USER"
```

После добавления в группу `docker` выйдите из сессии и зайдите снова (или перезагрузите ПК).

Проверка:

```bash
docker --version
docker compose version
```

#### MacBook (macOS)

1. Установите [Docker Desktop for Mac](https://docs.docker.com/desktop/setup/install/mac-install/).
2. Для Apple Silicon (M1/M2/M3) скачайте версию **Apple Silicon**.
3. Запустите Docker Desktop и дождитесь статуса **Running**.

Проверка:

```bash
docker --version
docker compose version
```

> **Apple Silicon:** в `.env` предпочтительно использовать MariaDB (`DATABASE=mariadb106` или другую доступную MariaDB), т.к. официальные MySQL-образы Oracle могут быть недоступны для ARM.

---

### 1.2. Установка mkcert

#### Ubuntu

```bash
sudo apt update
sudo apt install -y libnss3-tools
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/amd64"
chmod +x mkcert-v*-linux-amd64
sudo mv mkcert-v*-linux-amd64 /usr/local/bin/mkcert
```

Для ARM (например, Raspberry / ARM-ноутбук):

```bash
curl -JLO "https://dl.filippo.io/mkcert/latest?for=linux/arm64"
chmod +x mkcert-v*-linux-arm64
sudo mv mkcert-v*-linux-arm64 /usr/local/bin/mkcert
```

Установите локальный CA в доверенные:

```bash
mkcert -install
```

#### MacBook (macOS)

Через Homebrew:

```bash
brew install mkcert nss
mkcert -install
```

---

### 1.3. Локальный домен в `/etc/hosts`

Добавьте запись, чтобы `framework.dev` указывал на ваш компьютер, а не в интернет.

#### Ubuntu

```bash
echo "127.0.0.1 framework.dev" | sudo tee -a /etc/hosts
```

Проверка:

```bash
grep framework.dev /etc/hosts
```

#### MacBook (macOS)

```bash
sudo sh -c 'echo "127.0.0.1 framework.dev" >> /etc/hosts'
```

Или откройте файл вручную:

```bash
sudo nano /etc/hosts
```

и добавьте строку:

```text
127.0.0.1 framework.dev
```

Проверка:

```bash
grep framework.dev /etc/hosts
```

---

### 1.4. Освободите порты 80 и 443 (если заняты)

#### Ubuntu

Если локальный Apache/nginx уже слушает 80/443:

```bash
sudo systemctl stop apache2
sudo systemctl disable apache2
# или
sudo systemctl stop nginx
sudo systemctl disable nginx
```

Проверка занятых портов:

```bash
sudo ss -tlnp | grep -E ':80|:443'
```

#### MacBook (macOS)

```bash
sudo lsof -nP -iTCP:80 -sTCP:LISTEN
sudo lsof -nP -iTCP:443 -sTCP:LISTEN
```

Остановите процесс, который занимает порт, либо измените порты в `.env` (`HOST_MACHINE_UNSECURE_HOST_PORT`, `HOST_MACHINE_SECURE_HOST_PORT`).

---

## 2. Развёртывание проекта

### 2.1. Клонирование и `.env`

```bash
git clone <URL-репозитория> framework-lamp
cd framework-lamp
cp sample.env .env
```

При необходимости отредактируйте `.env`. Ключевые значения по умолчанию:

| Переменная | Значение | Назначение |
|---|---|---|
| `PHPVERSION` | `php83` | Версия PHP |
| `DATABASE` | `mysql8` | БД (`mariadb106` для Apple Silicon) |
| `DOCUMENT_ROOT` | `./www` | Корень сайта |
| `HOST_MACHINE_UNSECURE_HOST_PORT` | `80` | HTTP |
| `HOST_MACHINE_SECURE_HOST_PORT` | `443` | HTTPS |
| `HOST_MACHINE_PMA_PORT` | `8080` | phpMyAdmin |
| `MYSQL_ROOT_PASSWORD` | `tiger` | root MySQL |
| `MYSQL_USER` / `MYSQL_PASSWORD` / `MYSQL_DATABASE` | `docker` | Пользователь и БД приложения |

---

### 2.2. SSL-сертификат для `framework.dev`

Сертификат нужно выпустить **на своей машине** через mkcert (чужой сертификат из репозитория браузер не доверит).

```bash
cd config/ssl
mkcert framework.dev localhost 127.0.0.1 ::1
```

Будут созданы файлы вида:

- `framework.dev+3.pem`
- `framework.dev+3-key.pem`

Имена файлов должны совпадать с путями в `config/vhosts/framework.dev.conf`:

```apache
SSLCertificateFile /etc/apache2/ssl/framework.dev+3.pem
SSLCertificateKeyFile /etc/apache2/ssl/framework.dev+3-key.pem
```

Если mkcert создал другие имена (например `framework.dev+4.pem`), либо переименуйте файлы, либо обновите vhost.

Вернитесь в корень проекта:

```bash
cd ../..
```

---

### 2.3. Запуск контейнеров

Первый запуск (со сборкой образов):

```bash
docker compose up -d --build
```

Повторный запуск:

```bash
docker compose up -d
```

Проверка статуса:

```bash
docker compose ps
```

Откройте в браузере:

- сайт: [https://framework.dev](https://framework.dev)
- phpMyAdmin: [http://localhost:8080](http://localhost:8080)

HTTP (`http://framework.dev`) автоматически редиректит на HTTPS.

---

## 3. Доступы

### Сайт

- Document root: `./www`
- Vhost: `config/vhosts/framework.dev.conf`
- SSL: `config/ssl/`

### phpMyAdmin

- URL: `http://localhost:8080`
- Пользователь: `root`
- Пароль: значение `MYSQL_ROOT_PASSWORD` из `.env` (по умолчанию `tiger`)

### MySQL (с хоста)

- Host: `127.0.0.1`
- Port: `3306`
- User / Password / Database: из `.env`

Из PHP внутри контейнера хост БД: `database`.

### Redis

- Host: `127.0.0.1`
- Port: `6379`

---

## 4. Полезные команды

```bash
# Логи web-сервера
docker compose logs -f webserver

# Shell в PHP/Apache контейнере
docker compose exec webserver bash

# Перезапуск
docker compose restart

# Остановка
docker compose down

# Пересборка после изменения Dockerfile
docker compose up -d --build
```

---

## 5. Структура проекта

```text
.
├── bin/                  # Dockerfile'ы PHP / MySQL / MariaDB
├── config/
│   ├── php/php.ini       # Настройки PHP / Xdebug
│   ├── ssl/              # Локальные SSL-сертификаты
│   ├── vhosts/           # VirtualHost Apache
│   └── initdb/           # SQL/скрипты при первом старте MySQL
├── data/mysql/           # Данные БД (локально)
├── logs/                 # Логи Apache / MySQL / Xdebug
├── www/                  # Код приложения (document root)
├── docker-compose.yml
├── sample.env
└── .env                  # Локальные настройки (не коммитить секреты)
```

---

## 6. Краткий чеклист развёртывания

1. Установить Docker (+ Docker Desktop на Mac).
2. Установить mkcert и выполнить `mkcert -install`.
3. Добавить `127.0.0.1 framework.dev` в `/etc/hosts`.
4. Освободить порты `80`/`443` (или сменить их в `.env`).
5. Скопировать `sample.env` → `.env` (на Apple Silicon при необходимости сменить `DATABASE` на MariaDB).
6. Сгенерировать сертификат в `config/ssl` и сверить имена с vhost.
7. Запустить `docker compose up -d --build`.
8. Открыть `https://framework.dev`.

---

## 7. Возможные проблемы

| Симптом | Что проверить |
|---|---|
| Браузер открывает реальный сайт framework.dev | Запись в `/etc/hosts` |
| Предупреждение о сертификате | `mkcert -install` и перевыпуск сертификата на этой машине |
| Порт занят / контейнер не стартует | Кто слушает 80/443; значения портов в `.env` |
| На Mac M1/M2/M3 проблемы с MySQL | Сменить `DATABASE` на MariaDB и пересобрать |
| Изменения в `www/` не видны | Контейнер запущен, volume `DOCUMENT_ROOT` смонтирован |

---

## 8. Xdebug (опционально)

Настройки находятся в `config/php/php.ini`. Для PHP 8.x раскомментируйте блок Xdebug 3, затем:

```bash
docker compose restart webserver
```

В VS Code / Cursor используйте расширение PHP Debug и `pathMappings`:

```json
{
  "version": "0.2.0",
  "configurations": [
    {
      "name": "Listen for Xdebug",
      "type": "php",
      "request": "launch",
      "port": 9003,
      "pathMappings": {
        "/var/www/html": "${workspaceFolder}/www"
      }
    }
  ]
}
```
