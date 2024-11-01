#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Оптимизация настроек для TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./optimize.sh)${NC}"
    exit 1
fi

# Функция для проверки успешности операции
check() {
    if [ $? -eq 0 ]; then
        echo -e "${GREEN}[✓] $1${NC}"
    else
        echo -e "${RED}[✗] $1${NC}"
    fi
}

echo -e "\n${YELLOW}Оптимизация PHP 8.2...${NC}"

# Путь к php.ini
PHP_INI="/www/server/php/82/etc/php.ini"

# Оптимизация PHP
if [ -f "$PHP_INI" ]; then
    # Основные настройки PHP
    sed -i 's/^max_execution_time.*/max_execution_time = 300/' $PHP_INI
    sed -i 's/^max_input_time.*/max_input_time = 300/' $PHP_INI
    sed -i 's/^memory_limit.*/memory_limit = 256M/' $PHP_INI
    sed -i 's/^post_max_size.*/post_max_size = 64M/' $PHP_INI
    sed -i 's/^upload_max_filesize.*/upload_max_filesize = 64M/' $PHP_INI
    sed -i 's/^max_file_uploads.*/max_file_uploads = 20/' $PHP_INI
    
    # Отключение ограничений для MadelineProto
    sed -i 's/^disable_functions.*/disable_functions = /' $PHP_INI
    sed -i 's/^open_basedir.*/open_basedir = /' $PHP_INI
    
    check "Настройка PHP выполнена"
    
    # Перезапуск PHP-FPM
    systemctl restart php-fpm-82
    check "PHP-FPM перезапущен"
else
    echo -e "${RED}[✗] Файл php.ini не найден${NC}"
fi

echo -e "\n${YELLOW}Оптимизация MySQL...${NC}"

# Путь к my.cnf
MYSQL_CNF="/www/server/mysql/etc/my.cnf"

if [ -f "$MYSQL_CNF" ]; then
    # Создаем резервную копию
    cp $MYSQL_CNF ${MYSQL_CNF}.backup
    
    # Оптимизация MySQL
    cat >> $MYSQL_CNF << EOF

# Оптимизация для TG Panel
[mysqld]
max_connections = 500
max_allowed_packet = 64M
innodb_buffer_pool_size = 256M
innodb_log_file_size = 64M
innodb_lock_wait_timeout = 50
interactive_timeout = 3600
wait_timeout = 3600
sql_mode = ''

[client]
default-character-set = utf8mb4

[mysql]
default-character-set = utf8mb4

[mysqld]
character-set-server = utf8mb4
collation-server = utf8mb4_unicode_ci
EOF

    check "Настройка MySQL выполнена"
    
    # Перезапуск MySQL
    systemctl restart mysql
    check "MySQL перезапущен"
else
    echo -e "${RED}[✗] Файл my.cnf не найден${NC}"
fi

echo -e "\n${YELLOW}Оптимизация веб-сервера...${NC}"

# Проверяем, какой веб-сервер используется
if [ -f "/www/server/nginx/sbin/nginx" ]; then
    # Оптимизация Nginx
    NGINX_CONF="/www/server/nginx/conf/nginx.conf"
    
    if [ -f "$NGINX_CONF" ]; then
        # Создаем резервную копию
        cp $NGINX_CONF ${NGINX_CONF}.backup
        
        # Оптимизация настроек Nginx
        sed -i 's/worker_connections.*/worker_connections 2048;/' $NGINX_CONF
        sed -i 's/keepalive_timeout.*/keepalive_timeout 65;/' $NGINX_CONF
        sed -i 's/client_max_body_size.*/client_max_body_size 64m;/' $NGINX_CONF
        
        check "Настройка Nginx выполнена"
        
        # Перезапуск Nginx
        systemctl restart nginx
        check "Nginx перезапущен"
    else
        echo -e "${RED}[✗] Файл конфигурации Nginx не найден${NC}"
    fi
elif [ -f "/www/server/apache/bin/httpd" ]; then
    # Оптимизация Apache
    APACHE_CONF="/www/server/apache/conf/httpd.conf"
    
    if [ -f "$APACHE_CONF" ]; then
        # Создаем резервную копию
        cp $APACHE_CONF ${APACHE_CONF}.backup
        
        # Оптимизация настроек Apache
        sed -i 's/^Timeout.*/Timeout 300/' $APACHE_CONF
        sed -i 's/^MaxKeepAliveRequests.*/MaxKeepAliveRequests 500/' $APACHE_CONF
        sed -i 's/^KeepAliveTimeout.*/KeepAliveTimeout 5/' $APACHE_CONF
        
        check "Настройка Apache выполнена"
        
        # Перезапуск Apache
        systemctl restart httpd
        check "Apache перезапущен"
    else
        echo -e "${RED}[✗] Файл конфигурации Apache не найден${NC}"
    fi
fi

echo -e "\n${YELLOW}Настройка прав доступа...${NC}"

# Установка правильных прав доступа
chmod -R 755 /www/wwwroot/tgpanel.local/
chmod -R 777 /www/wwwroot/tgpanel.local/app/cache
chmod -R 777 /www/wwwroot/tgpanel.local/app/logs
chmod -R 777 /www/wwwroot/tgpanel.local/session.madeline
chmod -R 777 /www/wwwroot/tgpanel.local/assets/upload

check "Права доступа установлены"

echo -e "\n${GREEN}============================================${NC}"
echo -e "${GREEN}Оптимизация завершена!${NC}"
echo -e "${YELLOW}Рекомендации:${NC}"
echo "1. Проверьте работу TG Panel после оптимизации"
echo "2. Мониторьте потребление ресурсов в aaPanel"
echo "3. При необходимости скорректируйте настройки"
echo "4. Резервные копии конфигураций сохранены с расширением .backup"
echo -e "${GREEN}============================================${NC}"
