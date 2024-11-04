#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка окружения для TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root:${NC}"
    echo -e "${YELLOW}sudo ./install.sh${NC}"
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

# Функция для проверки наличия команды
check_command() {
    if ! command -v $1 &> /dev/null; then
        echo -e "${YELLOW}Установка $1...${NC}"
        apt update
        apt install -y $1
        check "Установка $1"
    fi
}

echo -e "\n${YELLOW}[1/8] Проверка необходимых утилит...${NC}"
check_command curl
check_command wget
check_command git
check "Проверка утилит"

echo -e "\n${YELLOW}[2/8] Очистка системы...${NC}"
# Удаляем конфликтующие пакеты
apt remove -y apache2* nginx* php* mysql* ufw fail2ban
apt autoremove -y
apt clean
check "Очистка системы"

echo -e "\n${YELLOW}[3/8] Обновление системы...${NC}"
apt update && apt upgrade -y
check "Обновление системы"

echo -e "\n${YELLOW}[4/8] Установка минимальных зависимостей...${NC}"
apt install -y ffmpeg
check "Установка зависимостей"

echo -e "\n${YELLOW}[5/8] Установка HestiaCP...${NC}"
# Скачиваем установщик HestiaCP
wget https://raw.githubusercontent.com/hestiacp/hestiacp/release/install/hst-install.sh

# Делаем скрипт исполняемым
chmod +x hst-install.sh

# Генерируем случайный пароль
ADMIN_PASS=$(openssl rand -base64 12)

# Запускаем установку
bash hst-install.sh --force \
    --interactive no \
    --email admin@localhost \
    --password $ADMIN_PASS \
    --hostname $(hostname -f) \
    --apache no \
    --nginx yes \
    --php yes \
    --multiphp yes \
    --vsftpd yes \
    --proftpd no \
    --named yes \
    --mysql yes \
    --postgresql no \
    --exim yes \
    --dovecot yes \
    --sieve no \
    --clamav no \
    --spamassassin no \
    --iptables yes \
    --fail2ban yes \
    --quota yes \
    --api yes

# Проверяем успешность установки
if [ ! -f "/usr/local/hestia/bin/v-list-sys-info" ]; then
    echo -e "${RED}Ошибка установки HestiaCP. Проверьте логи установки.${NC}"
    exit 1
fi

check "Установка HestiaCP"

echo -e "\n${YELLOW}[6/8] Настройка PHP и дополнительных компонентов...${NC}"
# Устанавливаем PHP 8.2 и необходимые расширения
/usr/local/hestia/bin/v-add-web-php 8.2
apt install -y php8.2-curl php8.2-gd php8.2-mbstring php8.2-mysql php8.2-xml php8.2-zip
check "Настройка PHP"

echo -e "\n${YELLOW}[7/8] Установка Ioncube...${NC}"
cd /tmp
wget https://downloads.ioncube.com/loader_downloads/ioncube_loaders_lin_x86-64.tar.gz
tar xzf ioncube_loaders_lin_x86-64.tar.gz
PHP_INI_DIR=$(php -i | grep "Loaded Configuration File" | awk '{print $5}' | sed 's/.\{9\}$//')
cp ioncube/ioncube_loader_lin_8.2.so $PHP_INI_DIR/
echo "zend_extension = $PHP_INI_DIR/ioncube_loader_lin_8.2.so" > $PHP_INI_DIR/conf.d/00-ioncube.ini
check "Установка Ioncube"

echo -e "\n${YELLOW}[8/8] Создание базовой структуры директорий...${NC}"
mkdir -p /home/admin/web
chmod -R 755 /home/admin/web
check "Создание директорий"

# Получение данных для входа
PANEL_PORT="8083"
EXTERNAL_IP=$(curl -s https://api.ipify.org)

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка окружения завершена!${NC}"
echo -e "\n${YELLOW}Данные для входа в HestiaCP:${NC}"
echo "URL: https://$EXTERNAL_IP:$PANEL_PORT"
echo "Логин: admin"
echo "Пароль: $ADMIN_PASS"

echo -e "\n${YELLOW}Следующие шаги:${NC}"
echo "1. Войдите в панель управления по указанному выше URL"
echo "2. Создайте новый домен для tgpanel"
echo "3. Настройте SSL сертификат для домена"
echo "4. Установите tgpanel, используя отдельный установочный скрипт"

echo -e "\n${YELLOW}Важно:${NC}"
echo "- Смените пароль администратора"
echo "- Настройте регулярные бэкапы"
echo -e "${GREEN}============================================${NC}"

# Сохранение данных установки
echo "Installation Date: $(date)" > /root/hestia_info.txt
echo "Panel URL: https://$EXTERNAL_IP:$PANEL_PORT" >> /root/hestia_info.txt
echo "Username: admin" >> /root/hestia_info.txt
echo "Password: $ADMIN_PASS" >> /root/hestia_info.txt

echo -e "\n${YELLOW}Данные сохранены в файл: /root/hestia_info.txt${NC}"
echo -e "${GREEN}Установка окружения успешно завершена!${NC}"
