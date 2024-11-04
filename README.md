# TG Panel Environment Installer

Скрипт для установки окружения TG Panel, включая HestiaCP, PHP 8.2, Ioncube и другие необходимые компоненты.

## Системные требования

- Ubuntu 18.04+ / Debian 9+
- Минимум 1 ГБ RAM
- Минимум 10 ГБ свободного места
- Чистый сервер (без установленных веб-серверов)
- Root доступ

## Установка

### Способ 1 (если curl не установлен):
```bash
# Установка curl
apt update
apt install -y curl

# Установка окружения
curl -sSL https://raw.githubusercontent.com/guloc/tgpanel-environment/master/install.sh | sudo bash
```

### Способ 2 (через wget):
```bash
# Скачивание скрипта
wget https://raw.githubusercontent.com/guloc/tgpanel-environment/master/install.sh

# Установка прав и запуск
chmod +x install.sh
sudo ./install.sh
```

### Способ 3 (ручная загрузка):
1. Скачайте файл install.sh
2. Загрузите его на сервер (например, через SFTP)
3. Выполните:
```bash
chmod +x install.sh
sudo ./install.sh
```

## Компоненты установки

### Веб-сервер и PHP
- HestiaCP (панель управления)
- Nginx
- PHP 8.2
- MySQL

### PHP расширения
- curl
- gd
- mbstring
- mysql
- xml
- zip
- ioncube

### Дополнительные компоненты
- FFmpeg для обработки медиафайлов
- Git
- Wget
- Curl

## Процесс установки

1. Очистка системы от конфликтующих пакетов
2. Обновление системы
3. Установка базовых зависимостей
4. Установка HestiaCP
5. Настройка PHP и расширений
6. Установка Ioncube
7. Создание базовой структуры директорий

## После установки

После успешной установки вы получите:
- URL для доступа к панели управления
- Логин и пароль администратора
- Настроенное окружение для установки TG Panel

Все данные для доступа сохраняются в файл `/root/hestia_info.txt`

## Безопасность

После установки рекомендуется:
1. Сменить пароль администратора
2. Настроить SSL сертификат
3. Настроить регулярные бэкапы
4. Обновить правила файрвола

## Поддержка

При возникновении проблем:
1. Проверьте логи установки
2. Убедитесь, что сервер соответствует системным требованиям
3. Проверьте, что все порты открыты и доступны

## Устранение неполадок

### Если curl не установлен:
```bash
apt update
apt install -y curl
```

### Если wget не установлен:
```bash
apt update
apt install -y wget
```

### Если возникают проблемы с правами:
```bash
chmod +x install.sh
chown root:root install.sh
