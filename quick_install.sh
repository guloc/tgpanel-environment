#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Быстрая установка TG Panel на VDS${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./quick_install.sh)${NC}"
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
apt install -y git wget unzip curl
check "Установка базовых пакетов"

echo -e "\n${YELLOW}[3/7] Создание рабочей директории...${NC}"
mkdir -p /home/tgpanel
cd /home/tgpanel
check "Создание директории"

echo -e "\n${YELLOW}[4/7] Загрузка TG Panel...${NC}"
wget https://github.com/your-repo/tgpanel/archive/main.zip
check "Загрузка архива"

echo -e "\n${YELLOW}[5/7] Распаковка файлов...${NC}"
unzip main.zip
cd tgpanel-main
check "Распаковка файлов"

echo -e "\n${YELLOW}[6/7] Настройка прав доступа...${NC}"
chmod +x *.sh
check "Установка прав на скрипты"

echo -e "\n${YELLOW}[7/7] Запуск установки...${NC}"
./install.sh
check "Запуск установщика"

# Настройка файрвола
echo -e "\n${YELLOW}Настройка файрвола...${NC}"
ufw allow 80/tcp
ufw allow 443/tcp
ufw allow 7800/tcp
ufw allow 22/tcp
check "Настройка файрвола"

# Создание задания для автоматического бэкапа
echo -e "\n${YELLOW}Настройка автоматического бэкапа...${NC}"
(crontab -l 2>/dev/null; echo "0 3 * * * /home/tgpanel/tgpanel-main/backup.sh") | crontab -
check "Настройка автобэкапа"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Установка завершена!${NC}"
echo -e "${YELLOW}Что дальше:${NC}"
echo "1. Проверьте установку: ./check_installation.sh"
echo "2. Оптимизируйте настройки: ./optimize.sh"
echo "3. Создайте бэкап: ./backup.sh"
echo ""
echo -e "${YELLOW}Данные для доступа:${NC}"
echo "- aaPanel: http://ваш-ip:7800"
echo "- Данные в файле: credentials.txt"
echo ""
echo -e "${YELLOW}Команда для мониторинга логов:${NC}"
echo "tail -f /www/wwwroot/tgpanel.local/app/logs/\$(date +\"%Y-%m-%d\").log"
echo -e "${GREEN}============================================${NC}"

# Проверка успешности установки
if [ -f "/www/server/panel/class/panelPlugin.py" ]; then
    echo -e "${GREEN}aaPanel успешно установлен!${NC}"
else
    echo -e "${RED}Возникли проблемы при установке. Проверьте логи.${NC}"
fi

# Сохранение информации о системе
echo "Installation Date: $(date)" > system_info.txt
echo "Server IP: $(hostname -I | awk '{print $1}')" >> system_info.txt
echo "PHP Version: $(php -v | head -n 1)" >> system_info.txt
echo "MySQL Version: $(mysql -V)" >> system_info.txt
echo "FFmpeg Version: $(ffmpeg -version | head -n 1)" >> system_info.txt

echo -e "\n${YELLOW}Информация о системе сохранена в system_info.txt${NC}"
echo -e "${GREEN}Установка успешно завершена!${NC}"
