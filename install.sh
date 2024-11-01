#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка TG Panel и aaPanel${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./install.sh)${NC}"
    exit 1
fi

echo -e "${YELLOW}[1/7] Установка aaPanel...${NC}"
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh
bash install.sh aapanel

# Ждем завершения установки aaPanel
sleep 10

echo -e "${YELLOW}[2/7] Установка FFmpeg и зависимостей...${NC}"
# Установка FFmpeg и зависимостей
if [ -f /etc/debian_version ]; then
    # Debian/Ubuntu
    apt-get update
    apt-get install -y ffmpeg
elif [ -f /etc/redhat-release ]; then
    # CentOS/RHEL
    yum install -y epel-release
    yum install -y ffmpeg ffmpeg-devel
fi

# Проверка установки FFmpeg
if command -v ffmpeg >/dev/null 2>&1; then
    echo -e "${GREEN}FFmpeg успешно установлен${NC}"
    ffmpeg -version | head -n 1
else
    echo -e "${RED}Ошибка установки FFmpeg${NC}"
    exit 1
fi

echo -e "${YELLOW}[3/7] Настройка PHP 8.2...${NC}"
# Установка PHP 8.2 через aaPanel CLI
bt 12
# Выбор PHP 8.2
printf "11\n" | bt

# Установка расширений PHP
bt 13
# Установка необходимых расширений
printf "1\ncurl,gd,mbstring,mysql,xml,zip,json\n" | bt

echo -e "${YELLOW}[4/7] Создание сайта для TG Panel...${NC}"
# Получаем IP сервера
SERVER_IP=$(hostname -I | awk '{print $1}')

# Создание сайта через aaPanel CLI
bt 23
# Создание нового сайта
printf "1\ntgpanel.local\n" | bt

echo -e "${YELLOW}[5/7] Создание базы данных...${NC}"
# Генерация случайного пароля
DB_PASSWORD=$(openssl rand -base64 12)

# Создание базы данных через aaPanel CLI
bt 5
# Создание новой базы данных
printf "1\ntgpanel\ntgpanel\n${DB_PASSWORD}\n" | bt

echo -e "${YELLOW}[6/7] Копирование файлов TG Panel...${NC}"
# Копирование файлов в директорию сайта
cp -r ./* /www/wwwroot/tgpanel.local/
chown -R www:www /www/wwwroot/tgpanel.local/

echo -e "${YELLOW}[7/7] Настройка прав доступа...${NC}"
chmod -R 755 /www/wwwroot/tgpanel.local/
chmod -R 777 /www/wwwroot/tgpanel.local/app/cache
chmod -R 777 /www/wwwroot/tgpanel.local/app/logs
chmod -R 777 /www/wwwroot/tgpanel.local/session.madeline
chmod -R 777 /www/wwwroot/tgpanel.local/assets/upload

# Получение данных для входа в aaPanel
AAPANEL_INFO=$(bt 14 | grep -A 2 "External IP")
AAPANEL_URL=$(echo "$AAPANEL_INFO" | grep "External IP" | awk '{print $3}')
AAPANEL_USERNAME=$(echo "$AAPANEL_INFO" | grep "username" | awk '{print $2}')
AAPANEL_PASSWORD=$(echo "$AAPANEL_INFO" | grep "password" | awk '{print $2}')

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка завершена успешно!${NC}"
echo -e "${GREEN}============================================${NC}"
echo -e "${YELLOW}Данные для доступа к aaPanel:${NC}"
echo "URL: http://$AAPANEL_URL:7800"
echo "Логин: $AAPANEL_USERNAME"
echo "Пароль: $AAPANEL_PASSWORD"
echo ""
echo -e "${YELLOW}Данные для базы данных TG Panel:${NC}"
echo "База данных: tgpanel"
echo "Пользователь: tgpanel"
echo "Пароль: $DB_PASSWORD"
echo ""
echo -e "${YELLOW}FFmpeg установлен:${NC}"
ffmpeg -version | head -n 1
echo ""
echo -e "${YELLOW}Что дальше:${NC}"
echo "1. Добавьте в /etc/hosts:"
echo "   $SERVER_IP tgpanel.local"
echo "2. Откройте http://tgpanel.local"
echo "3. Следуйте инструкциям установщика"
echo ""
echo -e "${GREEN}============================================${NC}"

# Сохраняем данные в файл
echo "aaPanel URL: http://$AAPANEL_URL:7800" > credentials.txt
echo "aaPanel Login: $AAPANEL_USERNAME" >> credentials.txt
echo "aaPanel Password: $AAPANEL_PASSWORD" >> credentials.txt
echo "Database: tgpanel" >> credentials.txt
echo "Database User: tgpanel" >> credentials.txt
echo "Database Password: $DB_PASSWORD" >> credentials.txt

echo -e "${YELLOW}Данные для доступа сохранены в файл credentials.txt${NC}"
