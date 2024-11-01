#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Текущая дата для имени бэкапа
DATE=$(date +"%Y-%m-%d_%H-%M-%S")
BACKUP_DIR="/www/backup/tgpanel_$DATE"

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Создание резервной копии TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Проверка прав root
if [ "$EUID" -ne 0 ]; then 
    echo -e "${RED}Запустите скрипт от имени root (sudo ./backup.sh)${NC}"
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

# Создание директории для бэкапа
mkdir -p $BACKUP_DIR
check "Создание директории бэкапа"

echo -e "\n${YELLOW}Бэкап файлов сайта...${NC}"
# Бэкап файлов TG Panel
tar -czf $BACKUP_DIR/tgpanel_files.tar.gz /www/wwwroot/tgpanel.local/
check "Бэкап файлов сайта"

echo -e "\n${YELLOW}Бэкап базы данных...${NC}"
# Бэкап MySQL базы данных
MYSQL_PWD=$(cat /www/server/panel/config/config.json | grep -o '"mysql_root":.*' | cut -d'"' -f4)
/www/server/mysql/bin/mysqldump -uroot -p$MYSQL_PWD tgpanel > $BACKUP_DIR/tgpanel_db.sql
check "Бэкап базы данных"

echo -e "\n${YELLOW}Бэкап конфигурационных файлов...${NC}"
# Бэкап конфигурационных файлов
mkdir -p $BACKUP_DIR/configs

# PHP конфиг
cp /www/server/php/82/etc/php.ini $BACKUP_DIR/configs/
check "Бэкап PHP конфигурации"

# Конфиг веб-сервера
if [ -f "/www/server/nginx/conf/nginx.conf" ]; then
    cp /www/server/nginx/conf/nginx.conf $BACKUP_DIR/configs/
    check "Бэкап Nginx конфигурации"
elif [ -f "/www/server/apache/conf/httpd.conf" ]; then
    cp /www/server/apache/conf/httpd.conf $BACKUP_DIR/configs/
    check "Бэкап Apache конфигурации"
fi

# MySQL конфиг
cp /www/server/mysql/etc/my.cnf $BACKUP_DIR/configs/
check "Бэкап MySQL конфигурации"

echo -e "\n${YELLOW}Бэкап сессий и кэша...${NC}"
# Бэкап важных директорий
tar -czf $BACKUP_DIR/sessions.tar.gz /www/wwwroot/tgpanel.local/session.madeline/
check "Бэкап сессий Telegram"

tar -czf $BACKUP_DIR/cache.tar.gz /www/wwwroot/tgpanel.local/app/cache/
check "Бэкап кэша"

# Создание файла с информацией о бэкапе
echo "Backup created at: $(date)" > $BACKUP_DIR/backup_info.txt
echo "Server IP: $(hostname -I | awk '{print $1}')" >> $BACKUP_DIR/backup_info.txt
echo "PHP version: $(/www/server/php/82/bin/php -v | head -n 1)" >> $BACKUP_DIR/backup_info.txt
echo "MySQL version: $(/www/server/mysql/bin/mysql -V)" >> $BACKUP_DIR/backup_info.txt

# Архивация всего бэкапа
echo -e "\n${YELLOW}Создание архива...${NC}"
cd /www/backup/
tar -czf tgpanel_backup_$DATE.tar.gz tgpanel_$DATE/
check "Создание финального архива"

# Удаление временной директории
rm -rf $BACKUP_DIR
check "Очистка временных файлов"

# Проверка размера бэкапа
BACKUP_SIZE=$(du -h /www/backup/tgpanel_backup_$DATE.tar.gz | cut -f1)

echo -e "\n${GREEN}============================================${NC}"
echo -e "${GREEN}Бэкап успешно создан!${NC}"
echo -e "${YELLOW}Информация:${NC}"
echo "Путь: /www/backup/tgpanel_backup_$DATE.tar.gz"
echo "Размер: $BACKUP_SIZE"
echo -e "\n${YELLOW}Для восстановления:${NC}"
echo "1. Распакуйте архив:"
echo "   tar -xzf tgpanel_backup_$DATE.tar.gz"
echo "2. Восстановите базу данных:"
echo "   mysql -u root -p tgpanel < tgpanel_db.sql"
echo "3. Восстановите файлы:"
echo "   cp -r tgpanel_files/* /www/wwwroot/tgpanel.local/"
echo "4. Восстановите конфигурации при необходимости"
echo -e "${GREEN}============================================${NC}"

# Создание скрипта для восстановления
cat > /www/backup/restore_${DATE}.sh << 'EOF'
#!/bin/bash
# Скрипт восстановления
echo "Распаковка архива..."
tar -xzf tgpanel_backup_$DATE.tar.gz

echo "Восстановление базы данных..."
mysql -u root -p tgpanel < tgpanel_$DATE/tgpanel_db.sql

echo "Восстановление файлов..."
cp -r tgpanel_$DATE/tgpanel_files/* /www/wwwroot/tgpanel.local/

echo "Восстановление конфигураций..."
cp tgpanel_$DATE/configs/* /www/server/php/82/etc/

echo "Установка прав доступа..."
chmod -R 755 /www/wwwroot/tgpanel.local/
chmod -R 777 /www/wwwroot/tgpanel.local/app/cache
chmod -R 777 /www/wwwroot/tgpanel.local/app/logs
chmod -R 777 /www/wwwroot/tgpanel.local/session.madeline
chmod -R 777 /www/wwwroot/tgpanel.local/assets/upload

echo "Восстановление завершено!"
EOF

chmod +x /www/backup/restore_${DATE}.sh
echo -e "${YELLOW}Создан скрипт восстановления: /www/backup/restore_${DATE}.sh${NC}"
