# Установка TG Panel на VDS через SSH

## Предварительная проверка VDS (рекомендуется)

1. Скачайте скрипт проверки:
```bash
wget https://raw.githubusercontent.com/your-repo/tgpanel/main/check_vds.sh
chmod +x check_vds.sh
```

2. Запустите проверку:
```bash
sudo ./check_vds.sh
```

Скрипт проверит:
- CPU, RAM и дисковое пространство
- Доступность портов
- Конфликтующие программы
- Сетевое подключение
- Системные ограничения

Результаты проверки сохраняются в файл vds_check_report.txt

## Быстрая установка

1. Подключитесь к вашему VDS через SSH:
```bash
ssh username@your_server_ip
```

2. Скачайте скрипт быстрой установки:
```bash
wget https://raw.githubusercontent.com/your-repo/tgpanel/main/quick_install.sh
```

3. Сделайте скрипт исполняемым и запустите:
```bash
chmod +x quick_install.sh
sudo ./quick_install.sh
```

Скрипт автоматически:
- Обновит систему
- Установит все необходимые пакеты
- Настроит файрвол
- Установит и настроит TG Panel
- Создаст задание для автоматического бэкапа

## Ручная установка (для опытных пользователей)

### Подготовка к установке

1. Подключитесь к вашему VDS через SSH:
```bash
ssh username@your_server_ip
```

2. Обновите систему:
```bash
sudo apt update
sudo apt upgrade -y
```

3. Установите Git и wget:
```bash
sudo apt install git wget unzip -y
```

### Установка TG Panel

1. Создайте рабочую директорию:
```bash
mkdir -p /home/tgpanel
cd /home/tgpanel
```

2. Скачайте установочные файлы:
```bash
wget https://github.com/your-repo/tgpanel/archive/main.zip
unzip main.zip
cd tgpanel-main
```

3. Сделайте скрипты исполняемыми:
```bash
chmod +x *.sh
```

4. Запустите установку:
```bash
sudo ./install.sh
```

## Проверка установки

После завершения установки:

1. Проверьте установку:
```bash
sudo ./check_installation.sh
```

2. Оптимизируйте настройки:
```bash
sudo ./optimize.sh
```

3. Создайте первую резервную копию:
```bash
sudo ./backup.sh
```

## Настройка домена

1. Добавьте A-запись в DNS вашего домена:
   - Тип: A
   - Имя: panel (или другое)
   - Значение: IP-адрес вашего VDS

2. Настройте домен в aaPanel:
   - Откройте http://ваш-ip:7800
   - Войдите с данными из credentials.txt
   - Перейдите в "Website"
   - Добавьте ваш домен

3. Настройте SSL (по желанию):
   - В aaPanel перейдите в раздел SSL
   - Выберите Let's Encrypt
   - Следуйте инструкциям

## Проверка работоспособности

1. Откройте в браузере:
```
http://ваш-домен
```

2. Проверьте логи:
```bash
sudo tail -f /www/wwwroot/tgpanel.local/app/logs/$(date +"%Y-%m-%d").log
```

## Полезные команды

### Управление службами
```bash
# Перезапуск PHP-FPM
sudo systemctl restart php-fpm-82

# Перезапуск MySQL
sudo systemctl restart mysql

# Перезапуск Nginx
sudo systemctl restart nginx
```

### Просмотр логов
```bash
# Логи PHP
sudo tail -f /www/server/php/82/var/log/php-fpm.log

# Логи MySQL
sudo tail -f /www/server/data/mysql.log

# Логи Nginx
sudo tail -f /www/server/nginx/logs/error.log
```

### Проверка статуса
```bash
# Статус PHP-FPM
sudo systemctl status php-fpm-82

# Статус MySQL
sudo systemctl status mysql

# Статус Nginx
sudo systemctl status nginx
```

## Решение проблем

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

### Если не работает FFmpeg:
```bash
# Проверка установки
ffmpeg -version

# Переустановка FFmpeg
sudo apt remove ffmpeg
sudo apt install ffmpeg -y
```

### Если проблемы с базой данных:
```bash
# Проверка подключения
mysql -u root -p

# Проверка базы
mysql -u root -p -e "show databases;"
```

## Безопасность

1. Измените пароли:
   - В aaPanel
   - В MySQL
   - В TG Panel

2. Настройте файрвол:
```bash
sudo ufw enable
sudo ufw default deny incoming
sudo ufw default allow outgoing
sudo ufw allow ssh
sudo ufw allow http
sudo ufw allow https
sudo ufw allow 7800/tcp
```

3. Настройте регулярные бэкапы:
```bash
# Создайте скрипт для автоматического бэкапа
sudo crontab -e

# Добавьте строку для ежедневного бэкапа в 3 часа ночи
0 3 * * * /home/tgpanel/tgpanel-main/backup.sh
```

## Обновление

1. Обновление системы:
```bash
sudo apt update
sudo apt upgrade -y
```

2. Обновление aaPanel:
   - Через веб-интерфейс
   - Или через CLI: `bt update`

3. Обновление TG Panel:
   - Создайте бэкап
   - Следуйте инструкциям обновления

## Мониторинг

1. Мониторинг ресурсов:
```bash
# Использование CPU и RAM
htop

# Использование диска
df -h

# Мониторинг в реальном времени
sudo iotop
```

2. Мониторинг логов:
```bash
# Все логи в реальном времени
sudo tail -f /www/wwwroot/tgpanel.local/app/logs/*
```

3. Мониторинг через aaPanel:
   - Откройте раздел "Monitoring"
   - Следите за графиками использования ресурсов
   - Настройте уведомления

## Дополнительная информация

- Все данные для доступа сохраняются в файле credentials.txt
- Информация о системе сохраняется в system_info.txt
- Результаты проверки VDS в vds_check_report.txt
- Логи установки находятся в /www/server/panel/logs/
- Бэкапы сохраняются в /www/backup/

## Поддержка

При возникновении проблем:
1. Проверьте логи установки
2. Запустите check_installation.sh
3. Проверьте system_info.txt и vds_check_report.txt
4. Следуйте инструкциям в разделе "Решение проблем"
