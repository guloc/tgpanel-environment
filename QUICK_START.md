# Быстрая установка TG Panel на VDS

## Способ 1: Установка одной командой (рекомендуется)

Подключитесь к вашему VDS через SSH и выполните:

```bash
curl -sSL https://raw.githubusercontent.com/your-repo/tgpanel/main/install_tgpanel.sh | sudo bash
```

или через wget:

```bash
wget -O - https://raw.githubusercontent.com/your-repo/tgpanel/main/install_tgpanel.sh | sudo bash
```

Скрипт автоматически:
- Обновит систему
- Установит все необходимые пакеты
- Установит и настроит aaPanel
- Установит PHP 8.2 и расширения
- Настроит права доступа
- Настроит cron задания
- Выведет данные для входа

## Управление Cron заданиями

### Автоматическое управление (рекомендуется)

```bash
# Скачать скрипт управления
wget https://raw.githubusercontent.com/your-repo/tgpanel/main/manage_cron.sh

# Сделать исполняемым
chmod +x manage_cron.sh

# Запустить
sudo ./manage_cron.sh
```

Скрипт позволяет:
- Добавлять cron задания
- Удалять cron задания
- Проверять статус
- Тестировать выполнение

### Ручное управление

```bash
# Просмотр текущих заданий
crontab -l

# Добавление задания вручную
crontab -e
# Добавьте строку:
* * * * * php /путь/к/сайту/index.php cron_run initiate > /dev/null 2>&1

# Проверка логов cron
tail -f /var/log/syslog | grep CRON
```

## Способ 2: Установка через скрипт

```bash
# Скачиваем скрипт установки
wget https://raw.githubusercontent.com/your-repo/tgpanel/main/quick_install.sh

# Делаем скрипт исполняемым
chmod +x quick_install.sh

# Запускаем установку
sudo ./quick_install.sh
```

## Способ 3: Установка вручную

```bash
# Обновляем систему
sudo apt update
sudo apt upgrade -y

# Устанавливаем необходимые пакеты
sudo apt install wget unzip cron -y

# Создаём рабочую директорию
mkdir -p /home/tgpanel
cd /home/tgpanel

# Скачиваем установщик aaPanel
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh

# Запускаем установку aaPanel
sudo bash install.sh aapanel

# После установки aaPanel, устанавливаем FFmpeg
sudo apt install ffmpeg -y
```

## После установки

1. Откройте в браузере:
```
http://ваш-ip:7800
```

2. Войдите в панель управления:
- Данные для входа будут выведены в терминал
- Также они сохраняются в файл /root/tgpanel_info.txt

3. Проверьте работу панели:
```bash
# Проверка статуса
sudo bt default

# Проверка cron заданий
sudo ./manage_cron.sh
```

## Решение проблем

### Проблемы с Cron

```bash
# Проверка статуса службы
sudo systemctl status cron

# Перезапуск службы
sudo systemctl restart cron

# Проверка логов
tail -f /var/log/syslog | grep CRON

# Проверка прав доступа
ls -la /путь/к/сайту/index.php
```

### Если сайт не открывается:
1. Проверьте файрвол:
```bash
sudo ufw status
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 7800/tcp
```

2. Проверьте права доступа:
```bash
sudo chmod -R 755 /www/wwwroot/tgpanel.local/
sudo chmod -R 777 /www/wwwroot/tgpanel.local/app/cache
sudo chmod -R 777 /www/wwwroot/tgpanel.local/app/logs
sudo chmod -R 777 /www/wwwroot/tgpanel.local/session.madeline
sudo chmod -R 777 /www/wwwroot/tgpanel.local/assets/upload
```

## Безопасность

После установки обязательно:
1. Смените пароль в aaPanel
2. Настройте файрвол
3. Включите SSL
4. Проверьте работу cron заданий

## Мониторинг

```bash
# Мониторинг системы
htop

# Мониторинг cron
sudo ./manage_cron.sh

# Мониторинг логов
tail -f /www/server/panel/logs/*
```

## Обновление

```bash
# Обновление системы
sudo apt update && sudo apt upgrade -y

# Обновление aaPanel
bt update

# Проверка cron после обновления
sudo ./manage_cron.sh
