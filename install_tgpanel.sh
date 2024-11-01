#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root:${NC}"
    echo -e "${YELLOW}curl -sSL https://raw.githubusercontent.com/your-repo/tgpanel/main/install_tgpanel.sh | sudo bash${NC}"
    exit 1
fi

# Функция для проверки успешности операции
check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}[✓] $1${NC}"
        return 0
    else
        echo -e "${RED}[✗] $1${NC}"
        return 1
    fi
}

echo -e "\n${YELLOW}[1/6] Обновление системы...${NC}"
apt update && apt upgrade -y
check "Обновление системы"

echo -e "\n${YELLOW}[2/6] Установка необходимых пакетов...${NC}"
apt install -y wget curl unzip git ffmpeg cron
check "Установка пакетов"

echo -e "\n${YELLOW}[3/6] Установка aaPanel...${NC}"
wget -O install.sh http://www.aapanel.com/script/install-ubuntu_6.0_en.sh
bash install.sh aapanel
check "Установка aaPanel"

echo -e "\n${YELLOW}[4/6] Настройка PHP и MySQL...${NC}"
# Ждем запуск aaPanel
sleep 10

# Установка PHP 8.2
bt 12
printf "11\n" | bt

# Установка расширений PHP
bt 13
printf "1\ncurl,gd,mbstring,mysql,xml,zip,json\n" | bt

echo -e "\n${YELLOW}[5/6] Настройка прав доступа...${NC}"
chmod -R 755 /www/wwwroot/
chmod -R 777 /www/wwwroot/*/app/cache
chmod -R 777 /www/wwwroot/*/app/logs
chmod -R 777 /www/wwwroot/*/session.madeline
chmod -R 777 /www/wwwroot/*/assets/upload
check "Настройка прав"

echo -e "\n${YELLOW}[6/6] Настройка Cron...${NC}"
# Получаем путь к сайту
SITE_PATH=$(find /var/www -type d -name "www" 2>/dev/null | head -n 1)
if [ -z "$SITE_PATH" ]; then
    SITE_PATH="/www/wwwroot/tgpanel.local"
fi

# Добавляем задание в cron
CRON_CMD="* * * * * php $SITE_PATH/index.php cron_run initiate > /dev/null 2>&1"
(crontab -l 2>/dev/null | grep -v "cron_run initiate" ; echo "$CRON_CMD") | crontab -
check "Настройка Cron"

# Перезапускаем cron
systemctl restart cron
check "Перезапуск Cron"

# Получение данных для входа
PANEL_INFO=$(bt 14 | grep -A 2 "External IP")
PANEL_URL=$(echo "$PANEL_INFO" | grep "External IP" | awk '{print $3}')
PANEL_USER=$(echo "$PANEL_INFO" | grep "username" | awk '{print $2}')
PANEL_PASS=$(echo "$PANEL_INFO" | grep "password" | awk '{print $2}')

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка завершена!${NC}"
echo -e "\n${YELLOW}Данные для входа в aaPanel:${NC}"
echo "URL: http://$PANEL_URL:7800"
echo "Логин: $PANEL_USER"
echo "Пароль: $PANEL_PASS"
echo -e "\n${YELLOW}Настроенные задания Cron:${NC}"
crontab -l | grep "cron_run"
echo -e "\n${YELLOW}Что дальше:${NC}"
echo "1. Откройте панель управления: http://$PANEL_URL:7800"
echo "2. Войдите с указанными выше данными"
echo "3. Добавьте ваш домен в разделе 'Websites'"
echo "4. Настройте SSL сертификат"
echo -e "\n${YELLOW}Важно:${NC}"
echo "- Смените пароль администратора"
echo "- Настройте файрвол"
echo "- Настройте регулярные бэкапы"
echo "- Проверьте работу Cron заданий"
echo -e "${GREEN}============================================${NC}"

# Сохранение данных
echo "Installation Date: $(date)" > /root/tgpanel_info.txt
echo "Panel URL: http://$PANEL_URL:7800" >> /root/tgpanel_info.txt
echo "Username: $PANEL_USER" >> /root/tgpanel_info.txt
echo "Password: $PANEL_PASS" >> /root/tgpanel_info.txt
echo "Cron Job: $CRON_CMD" >> /root/tgpanel_info.txt

echo -e "\n${YELLOW}Данные сохранены в файл: /root/tgpanel_info.txt${NC}"
echo -e "${GREEN}Установка успешно завершена!${NC}"
