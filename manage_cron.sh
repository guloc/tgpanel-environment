#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Управление Cron заданиями TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./manage_cron.sh)${NC}"
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

# Функция для добавления cron задания
add_cron() {
    local site_path="$1"
    if [ -f "$site_path/index.php" ]; then
        CRON_CMD="* * * * * php $site_path/index.php cron_run initiate > /dev/null 2>&1"
        (crontab -l 2>/dev/null | grep -v "cron_run initiate" ; echo "$CRON_CMD") | crontab -
        systemctl restart cron
        check "Добавление cron задания для $site_path"
    else
        echo -e "${RED}[✗] Файл index.php не найден в $site_path${NC}"
    fi
}

# Функция для удаления cron задания
remove_cron() {
    crontab -l 2>/dev/null | grep -v "cron_run initiate" | crontab -
    systemctl restart cron
    check "Удаление cron задания"
}

# Функция для проверки статуса cron
check_cron() {
    echo -e "\n${YELLOW}Текущие cron задания:${NC}"
    crontab -l | grep "cron_run"
    
    echo -e "\n${YELLOW}Статус службы cron:${NC}"
    systemctl status cron | grep "Active:"
    
    echo -e "\n${YELLOW}Последние записи в логе:${NC}"
    tail -n 5 /var/log/syslog | grep CRON
}

# Функция для тестирования cron задания
test_cron() {
    local site_path="$1"
    if [ -f "$site_path/index.php" ]; then
        echo -e "\n${YELLOW}Тестирование cron задания...${NC}"
        php "$site_path/index.php" cron_run initiate
        check "Выполнение тестового запуска"
    else
        echo -e "${RED}[✗] Файл index.php не найден в $site_path${NC}"
    fi
}

# Основное меню
show_menu() {
    echo -e "\n${YELLOW}Выберите действие:${NC}"
    echo "1. Добавить cron задание"
    echo "2. Удалить cron задание"
    echo "3. Проверить статус"
    echo "4. Протестировать выполнение"
    echo "5. Выход"
    echo -e "${GREEN}============================================${NC}"
}

# Основной цикл
while true; do
    show_menu
    read -p "Введите номер действия: " choice
    
    case $choice in
        1)
            echo -e "\n${YELLOW}Поиск путей установки...${NC}"
            # Поиск всех возможных путей
            PATHS=(
                "/var/www/*/data/www/*"
                "/www/wwwroot/*"
            )
            
            FOUND_PATHS=()
            for pattern in "${PATHS[@]}"; do
                while IFS= read -r path; do
                    if [ -f "$path/index.php" ]; then
                        FOUND_PATHS+=("$path")
                    fi
                done < <(eval "ls -d $pattern 2>/dev/null")
            done
            
            if [ ${#FOUND_PATHS[@]} -eq 0 ]; then
                echo -e "${RED}Пути установки не найдены${NC}"
                continue
            fi
            
            echo -e "\n${YELLOW}Найденные пути установки:${NC}"
            for i in "${!FOUND_PATHS[@]}"; do
                echo "$((i+1)). ${FOUND_PATHS[$i]}"
            done
            
            read -p "Выберите номер пути: " path_num
            if [ -n "${FOUND_PATHS[$((path_num-1))]}" ]; then
                add_cron "${FOUND_PATHS[$((path_num-1))]}"
            else
                echo -e "${RED}Неверный выбор${NC}"
            fi
            ;;
        2)
            remove_cron
            ;;
        3)
            check_cron
            ;;
        4)
            echo -e "\n${YELLOW}Поиск путей установки...${NC}"
            PATHS=(
                "/var/www/*/data/www/*"
                "/www/wwwroot/*"
            )
            
            FOUND_PATHS=()
            for pattern in "${PATHS[@]}"; do
                while IFS= read -r path; do
                    if [ -f "$path/index.php" ]; then
                        FOUND_PATHS+=("$path")
                    fi
                done < <(eval "ls -d $pattern 2>/dev/null")
            done
            
            if [ ${#FOUND_PATHS[@]} -eq 0 ]; then
                echo -e "${RED}Пути установки не найдены${NC}"
                continue
            fi
            
            echo -e "\n${YELLOW}Найденные пути установки:${NC}"
            for i in "${!FOUND_PATHS[@]}"; do
                echo "$((i+1)). ${FOUND_PATHS[$i]}"
            done
            
            read -p "Выберите номер пути: " path_num
            if [ -n "${FOUND_PATHS[$((path_num-1))]}" ]; then
                test_cron "${FOUND_PATHS[$((path_num-1))]}"
            else
                echo -e "${RED}Неверный выбор${NC}"
            fi
            ;;
        5)
            echo -e "${GREEN}До свидания!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Неверный выбор${NC}"
            ;;
    esac
    
    echo -e "\nНажмите Enter для продолжения..."
    read
done
