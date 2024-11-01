#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./manage.sh)${NC}"
    exit 1
fi

# Функция для отображения меню
show_menu() {
    clear
    echo -e "${GREEN}============================================${NC}"
    echo -e "${GREEN}         Управление TG Panel${NC}"
    echo -e "${GREEN}============================================${NC}"
    echo -e "${YELLOW}Выберите действие:${NC}"
    echo -e "${BLUE}1.${NC} Установить TG Panel и aaPanel"
    echo -e "${BLUE}2.${NC} Проверить установку"
    echo -e "${BLUE}3.${NC} Оптимизировать настройки"
    echo -e "${BLUE}4.${NC} Создать резервную копию"
    echo -e "${BLUE}5.${NC} Просмотреть статус служб"
    echo -e "${BLUE}6.${NC} Просмотреть логи"
    echo -e "${BLUE}7.${NC} Перезапустить службы"
    echo -e "${BLUE}8.${NC} Изменить права доступа"
    echo -e "${BLUE}9.${NC} Выход"
    echo -e "${GREEN}============================================${NC}"
}

# Функция для просмотра логов
view_logs() {
    echo -e "\n${YELLOW}Выберите лог для просмотра:${NC}"
    echo -e "${BLUE}1.${NC} Лог установки"
    echo -e "${BLUE}2.${NC} Лог TG Panel"
    echo -e "${BLUE}3.${NC} Лог PHP"
    echo -e "${BLUE}4.${NC} Лог MySQL"
    echo -e "${BLUE}5.${NC} Назад"
    
    read -p "Выберите опцию: " log_choice
    
    case $log_choice in
        1) tail -f /www/server/panel/logs/install.log ;;
        2) tail -f /www/wwwroot/tgpanel.local/app/logs/$(date +"%Y-%m-%d").log ;;
        3) tail -f /www/server/php/82/var/log/php-fpm.log ;;
        4) tail -f /www/server/data/mysql.log ;;
        5) return ;;
        *) echo -e "${RED}Неверный выбор${NC}" ;;
    esac
    
    read -p "Нажмите Enter для продолжения..."
}

# Функция для перезапуска служб
restart_services() {
    echo -e "\n${YELLOW}Выберите службу для перезапуска:${NC}"
    echo -e "${BLUE}1.${NC} PHP-FPM"
    echo -e "${BLUE}2.${NC} MySQL"
    echo -e "${BLUE}3.${NC} Nginx/Apache"
    echo -e "${BLUE}4.${NC} Все службы"
    echo -e "${BLUE}5.${NC} Назад"
    
    read -p "Выберите опцию: " service_choice
    
    case $service_choice in
        1)
            systemctl restart php-fpm-82
            echo -e "${GREEN}PHP-FPM перезапущен${NC}"
            ;;
        2)
            systemctl restart mysql
            echo -e "${GREEN}MySQL перезапущен${NC}"
            ;;
        3)
            if [ -f "/www/server/nginx/sbin/nginx" ]; then
                systemctl restart nginx
                echo -e "${GREEN}Nginx перезапущен${NC}"
            else
                systemctl restart httpd
                echo -e "${GREEN}Apache перезапущен${NC}"
            fi
            ;;
        4)
            systemctl restart php-fpm-82
            systemctl restart mysql
            if [ -f "/www/server/nginx/sbin/nginx" ]; then
                systemctl restart nginx
            else
                systemctl restart httpd
            fi
            echo -e "${GREEN}Все службы перезапущены${NC}"
            ;;
        5) return ;;
        *) echo -e "${RED}Неверный выбор${NC}" ;;
    esac
    
    read -p "Нажмите Enter для продолжения..."
}

# Функция для проверки статуса служб
check_services() {
    echo -e "\n${YELLOW}Статус служб:${NC}"
    
    echo -e "\n${BLUE}PHP-FPM:${NC}"
    systemctl status php-fpm-82 | grep -E "Active:|Memory:|Tasks:"
    
    echo -e "\n${BLUE}MySQL:${NC}"
    systemctl status mysql | grep -E "Active:|Memory:|Tasks:"
    
    if [ -f "/www/server/nginx/sbin/nginx" ]; then
        echo -e "\n${BLUE}Nginx:${NC}"
        systemctl status nginx | grep -E "Active:|Memory:|Tasks:"
    else
        echo -e "\n${BLUE}Apache:${NC}"
        systemctl status httpd | grep -E "Active:|Memory:|Tasks:"
    fi
    
    read -p "Нажмите Enter для продолжения..."
}

# Функция для изменения прав доступа
fix_permissions() {
    echo -e "\n${YELLOW}Установка прав доступа...${NC}"
    
    chmod -R 755 /www/wwwroot/tgpanel.local/
    chmod -R 777 /www/wwwroot/tgpanel.local/app/cache
    chmod -R 777 /www/wwwroot/tgpanel.local/app/logs
    chmod -R 777 /www/wwwroot/tgpanel.local/session.madeline
    chmod -R 777 /www/wwwroot/tgpanel.local/assets/upload
    
    echo -e "${GREEN}Права доступа обновлены${NC}"
    read -p "Нажмите Enter для продолжения..."
}

# Основной цикл
while true; do
    show_menu
    read -p "Выберите опцию: " choice
    
    case $choice in
        1)
            ./install.sh
            ;;
        2)
            ./check_installation.sh
            read -p "Нажмите Enter для продолжения..."
            ;;
        3)
            ./optimize.sh
            read -p "Нажмите Enter для продолжения..."
            ;;
        4)
            ./backup.sh
            read -p "Нажмите Enter для продолжения..."
            ;;
        5)
            check_services
            ;;
        6)
            view_logs
            ;;
        7)
            restart_services
            ;;
        8)
            fix_permissions
            ;;
        9)
            echo -e "${GREEN}До свидания!${NC}"
            exit 0
            ;;
        *)
            echo -e "${RED}Неверный выбор${NC}"
            read -p "Нажмите Enter для продолжения..."
            ;;
    esac
done
