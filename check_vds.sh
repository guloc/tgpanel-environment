#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Проверка VDS для установки TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Функция для проверки
check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}[✓] $1${NC}"
        return 0
    else
        echo -e "${RED}[✗] $1${NC}"
        return 1
    fi
}

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./check_vds.sh)${NC}"
    exit 1
fi

echo -e "\n${YELLOW}Проверка системных требований:${NC}"

# Проверка CPU
CPU_CORES=$(nproc)
echo -e "CPU ядер: $CPU_CORES"
if [ $CPU_CORES -lt 2 ]; then
    echo -e "${RED}[✗] Рекомендуется минимум 2 ядра CPU${NC}"
else
    echo -e "${GREEN}[✓] CPU соответствует требованиям${NC}"
fi

# Проверка RAM
TOTAL_RAM=$(free -m | awk '/^Mem:/{print $2}')
echo -e "Оперативная память: $TOTAL_RAM MB"
if [ $TOTAL_RAM -lt 1024 ]; then
    echo -e "${RED}[✗] Рекомендуется минимум 1GB RAM${NC}"
else
    echo -e "${GREEN}[✓] RAM соответствует требованиям${NC}"
fi

# Проверка места на диске
DISK_SPACE=$(df -m / | awk 'NR==2 {print $4}')
echo -e "Свободное место на диске: $DISK_SPACE MB"
if [ $DISK_SPACE -lt 10240 ]; then
    echo -e "${RED}[✗] Рекомендуется минимум 10GB свободного места${NC}"
else
    echo -e "${GREEN}[✓] Дисковое пространство соответствует требованиям${NC}"
fi

echo -e "\n${YELLOW}Проверка сетевых портов:${NC}"
# Проверка занятости портов
netstat -tuln | grep ':80 ' > /dev/null
if [ $? -eq 0 ]; then
    echo -e "${RED}[✗] Порт 80 уже используется${NC}"
else
    echo -e "${GREEN}[✓] Порт 80 свободен${NC}"
fi

netstat -tuln | grep ':443 ' > /dev/null
if [ $? -eq 0 ]; then
    echo -e "${RED}[✗] Порт 443 уже используется${NC}"
else
    echo -e "${GREEN}[✓] Порт 443 свободен${NC}"
fi

netstat -tuln | grep ':7800 ' > /dev/null
if [ $? -eq 0 ]; then
    echo -e "${RED}[✗] Порт 7800 уже используется${NC}"
else
    echo -e "${GREEN}[✓] Порт 7800 свободен${NC}"
fi

echo -e "\n${YELLOW}Проверка установленных компонентов:${NC}"
# Проверка установленных пакетов
command -v nginx >/dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${RED}[✗] Nginx уже установлен${NC}"
else
    echo -e "${GREEN}[✓] Nginx не установлен${NC}"
fi

command -v apache2 >/dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${RED}[✗] Apache уже установлен${NC}"
else
    echo -e "${GREEN}[✓] Apache не установлен${NC}"
fi

command -v mysql >/dev/null 2>&1
if [ $? -eq 0 ]; then
    echo -e "${RED}[✗] MySQL уже установлен${NC}"
else
    echo -e "${GREEN}[✓] MySQL не установлен${NC}"
fi

echo -e "\n${YELLOW}Проверка сетевого подключения:${NC}"
# Проверка интернет-соединения
ping -c 1 google.com >/dev/null 2>&1
check "Доступ в интернет"

# Проверка DNS
nslookup google.com >/dev/null 2>&1
check "DNS работает корректно"

echo -e "\n${YELLOW}Проверка системных ограничений:${NC}"
# Проверка лимитов системы
ULIMIT=$(ulimit -n)
echo -e "Максимальное количество открытых файлов: $ULIMIT"
if [ $ULIMIT -lt 65535 ]; then
    echo -e "${YELLOW}[!] Рекомендуется увеличить лимит открытых файлов${NC}"
fi

# Сохранение результатов
echo -e "\n${YELLOW}Сохранение отчета...${NC}"
{
    echo "Отчет о проверке VDS $(date)"
    echo "================================"
    echo "CPU ядер: $CPU_CORES"
    echo "RAM: $TOTAL_RAM MB"
    echo "Свободное место: $DISK_SPACE MB"
    echo "Лимит открытых файлов: $ULIMIT"
    echo "================================"
    echo "Версия системы:"
    cat /etc/os-release
    echo "================================"
    echo "Сетевые интерфейсы:"
    ip addr
} > vds_check_report.txt

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Проверка завершена!${NC}"
echo -e "${YELLOW}Отчет сохранен в файл: vds_check_report.txt${NC}"
echo -e "\n${YELLOW}Рекомендации:${NC}"
echo "1. Убедитесь, что все критические требования выполнены"
echo "2. Освободите порты 80, 443 и 7800 если они заняты"
echo "3. Удалите конфликтующие веб-серверы и базы данных"
echo "4. При необходимости увеличьте лимиты системы"
echo -e "${GREEN}============================================${NC}"
