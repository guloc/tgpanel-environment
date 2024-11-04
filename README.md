# TG Panel Environment Installer

Скрипт для установки окружения TG Panel, включая HestiaCP, PHP 8.2, Ioncube и другие необходимые компоненты.

## Быстрая установка

### На чистом сервере (без curl):
```bash
# 1. Обновляем систему и устанавливаем wget
apt update
apt install -y wget

# 2. Скачиваем установщик
wget https://raw.githubusercontent.com/guloc/tgpanel-environment/master/install.sh

# 3. Делаем скрипт исполняемым и запускаем
chmod +x install.sh
./install.sh
```

### Если curl уже установлен:
```bash
curl -sSL https://raw.githubusercontent.com/guloc/tgpanel-environment/master/install.sh | bash
```

## Системные требования

- Ubuntu 18.04+ / Debian 9+
- Минимум 1 ГБ RAM
- Минимум 10 ГБ свободного места
- Чистый сервер (без установленных веб-серверов)
- Root доступ

## Что устанавливается

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

## Устранение неполадок

### Если возникают проблемы с правами:
```bash
chmod +x install.sh
chown root:root install.sh
```

### Если скрипт не запускается:
```bash
# Проверяем наличие ошибок в скрипте
bash -n install.sh

# Запускаем с выводом отладочной информации
bash -x install.sh
```

### Проверка логов:
```bash
# Логи HestiaCP
cat /var/log/hestia/nginx-error.log

# Логи установки
cat /root/hestia_info.txt
