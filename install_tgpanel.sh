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
    echo -e "${YELLOW}curl -sSL https://raw.githubusercontent.com/guloc/tgpanel/master/install_tgpanel.sh | sudo bash${NC}"
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

echo -e "\n${YELLOW}[1/7] Обновление системы...${NC}"
apt update && apt upgrade -y
check "Обновление системы"

echo -e "\n${YELLOW}[2/7] Установка необходимых пакетов...${NC}"
apt install -y wget curl git ffmpeg cron ufw python3 python3-pip
check "Установка пакетов"

echo -e "\n${YELLOW}[3/7] Настройка файрвола...${NC}"
# Открываем необходимые порты
ufw allow ssh
ufw allow 80
ufw allow 443
ufw allow 8083
ufw allow 'Nginx Full'
echo "y" | ufw enable
check "Настройка файрвола"

echo -e "\n${YELLOW}[4/7] Удаление старой установки...${NC}"
if [ -f /usr/local/hestia/bin/v-list-sys-hestia-autoupdate ]; then
    /usr/local/hestia/bin/v-delete-sys-hestia-autoupdate
fi
apt remove -y hestia hestia-nginx hestia-php
rm -rf /usr/local/hestia
check "Удаление старой установки"

echo -e "\n${YELLOW}[5/7] Установка HestiaCP...${NC}"
# Скачиваем установщик HestiaCP
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh

# Создаем файл конфигурации для автоматической установки
cat > hst-install-config << EOF
apache=false
phpfpm=true
multiphp=true
vsftpd=true
proftpd=false
named=true
mysql=true
postgresql=false
exim=true
dovecot=true
sieve=true
clamav=false
spamassassin=false
iptables=true
fail2ban=true
quota=true
EOF

# Запускаем установку
bash hst-install.sh --interactive no --email admin@localhost --password $(openssl rand -base64 12) --hostname $(hostname -f) --with-debs /tmp/hestiacp-src/debs

# Проверяем успешность установки
if [ ! -f "/usr/local/hestia/bin/v-list-sys-info" ]; then
    echo -e "${RED}Ошибка установки HestiaCP. Проверьте логи установки.${NC}"
    exit 1
fi

check "Установка HestiaCP"

echo -e "\n${YELLOW}[6/7] Настройка PHP и дополнительных компонентов...${NC}"
# Устанавливаем PHP 8.2 и необходимые расширения
/usr/local/hestia/bin/v-add-web-php 8.2
apt install -y php8.2-curl php8.2-gd php8.2-mbstring php8.2-mysql php8.2-xml php8.2-zip php8.2-ioncube-loader
check "Настройка PHP"

echo -e "\n${YELLOW}[7/7] Настройка прав доступа...${NC}"
mkdir -p /home/admin/web/tgpanel/public_html
chmod -R 755 /home/admin/web/tgpanel/public_html
mkdir -p /home/admin/web/tgpanel/public_html/app/cache
mkdir -p /home/admin/web/tgpanel/public_html/app/logs
mkdir -p /home/admin/web/tgpanel/public_html/session.madeline
mkdir -p /home/admin/web/tgpanel/public_html/assets/upload
chmod -R 777 /home/admin/web/tgpanel/public_html/app/cache
chmod -R 777 /home/admin/web/tgpanel/public_html/app/logs
chmod -R 777 /home/admin/web/tgpanel/public_html/session.madeline
chmod -R 777 /home/admin/web/tgpanel/public_html/assets/upload
check "Настройка прав"

# Получение данных для входа
ADMIN_PASS=$(cat /usr/local/hestia/conf/defaults/hst-install.conf | grep "ADMIN_PASS" | cut -d "=" -f2)
PANEL_PORT="8083"
EXTERNAL_IP=$(curl -s https://api.ipify.org)

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка завершена!${NC}"
echo -e "\n${YELLOW}Данные для входа в HestiaCP:${NC}"
echo "URL: https://$EXTERNAL_IP:$PANEL_PORT"
echo "Логин: admin"
echo "Пароль: $ADMIN_PASS"

echo -e "\n${YELLOW}Следующие шаги:${NC}"
echo "1. Войдите в панель управления по указанному выше URL"
echo "2. Создайте новый домен для tgpanel"
echo "3. Настройте SSL сертификат для домена"
echo "4. Загрузите файлы tgpanel в /home/admin/web/tgpanel/public_html/"

echo -e "\n${YELLOW}Важно:${NC}"
echo "- Смените пароль администратора"
echo "- Настройте регулярные бэкапы"
echo "- Проверьте работу Cron заданий"
echo -e "${GREEN}============================================${NC}"

# Сохранение данных установки
echo "Installation Date: $(date)" > /root/tgpanel_info.txt
echo "Panel URL: https://$EXTERNAL_IP:$PANEL_PORT" >> /root/tgpanel_info.txt
echo "Username: admin" >> /root/tgpanel_info.txt
echo "Password: $ADMIN_PASS" >> /root/tgpanel_info.txt

echo -e "\n${YELLOW}Данные сохранены в файл: /root/tgpanel_info.txt${NC}"
echo -e "${GREEN}Установка успешно завершена!${NC}"
