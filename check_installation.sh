#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Проверка установки TG Panel и aaPanel${NC}"
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

# Проверка FFmpeg
echo -e "\n${YELLOW}Проверка FFmpeg:${NC}"
if command -v ffmpeg >/dev/null 2>&1; then
    check "FFmpeg установлен"
    FFMPEG_VERSION=$(ffmpeg -version | head -n 1)
    echo -e "${GREEN}Версия: $FFMPEG_VERSION${NC}"
    
    # Проверка основных кодеков
    echo -e "\n${YELLOW}Проверка кодеков FFmpeg:${NC}"
    CODECS=$(ffmpeg -codecs 2>/dev/null)
    
    echo "$CODECS" | grep -q "libx264" && check "Поддержка H.264"
    echo "$CODECS" | grep -q "libvpx" && check "Поддержка VP8/VP9"
    echo "$CODECS" | grep -q "libmp3lame" && check "Поддержка MP3"
    echo "$CODECS" | grep -q "libvorbis" && check "Поддержка Vorbis"
    echo "$CODECS" | grep -q "aac" && check "Поддержка AAC"
else
    echo -e "${RED}[✗] FFmpeg не установлен${NC}"
fi

# Проверка aaPanel
echo -e "\n${YELLOW}Проверка aaPanel:${NC}"
if [ -f "/www/server/panel/class/panelPlugin.py" ]; then
    check "aaPanel установлен"
    
    # Проверка статуса службы
    systemctl status bt > /dev/null 2>&1
    check "Служба aaPanel запущена"
    
    # Проверка порта панели
    netstat -tlpn | grep ":7800" > /dev/null 2>&1
    check "Порт панели (7800) открыт"
else
    echo -e "${RED}[✗] aaPanel не установлен${NC}"
fi

# Проверка PHP
echo -e "\n${YELLOW}Проверка PHP:${NC}"
if [ -f "/www/server/php/82/bin/php" ]; then
    check "PHP 8.2 установлен"
    
    # Проверка расширений PHP
    EXTENSIONS=("curl" "gd" "mbstring" "mysql" "xml" "zip" "json")
    for ext in "${EXTENSIONS[@]}"; do
        /www/server/php/82/bin/php -m | grep -i "$ext" > /dev/null 2>&1
        check "Расширение PHP: $ext"
    done
    
    # Проверка PHP-FPM
    systemctl status php-fpm-82 > /dev/null 2>&1
    check "PHP-FPM запущен"
else
    echo -e "${RED}[✗] PHP 8.2 не установлен${NC}"
fi

# Проверка веб-сервера
echo -e "\n${YELLOW}Проверка веб-сервера:${NC}"
if [ -f "/www/server/nginx/sbin/nginx" ]; then
    check "Nginx установлен"
    systemctl status nginx > /dev/null 2>&1
    check "Nginx запущен"
elif [ -f "/www/server/apache/bin/httpd" ]; then
    check "Apache установлен"
    systemctl status httpd > /dev/null 2>&1
    check "Apache запущен"
else
    echo -e "${RED}[✗] Веб-сервер не установлен${NC}"
fi

# Проверка MySQL
echo -e "\n${YELLOW}Проверка MySQL:${NC}"
if [ -f "/www/server/mysql/bin/mysql" ]; then
    check "MySQL установлен"
    systemctl status mysql > /dev/null 2>&1
    check "MySQL запущен"
    
    # Проверка базы данных TG Panel
    /www/server/mysql/bin/mysql -uroot -e "use tgpanel;" > /dev/null 2>&1
    check "База данных tgpanel существует"
else
    echo -e "${RED}[✗] MySQL не установлен${NC}"
fi

# Проверка директорий TG Panel
echo -e "\n${YELLOW}Проверка директорий TG Panel:${NC}"
DIRECTORIES=(
    "/www/wwwroot/tgpanel.local"
    "/www/wwwroot/tgpanel.local/app/cache"
    "/www/wwwroot/tgpanel.local/app/logs"
    "/www/wwwroot/tgpanel.local/session.madeline"
    "/www/wwwroot/tgpanel.local/assets/upload"
)

for dir in "${DIRECTORIES[@]}"; do
    if [ -d "$dir" ]; then
        check "Директория существует: $dir"
        
        # Проверка прав доступа
        if [ -w "$dir" ]; then
            check "Права на запись: $dir"
        else
            echo -e "${RED}[✗] Нет прав на запись: $dir${NC}"
        fi
    else
        echo -e "${RED}[✗] Директория не существует: $dir${NC}"
    fi
done

# Проверка доступности сайта
echo -e "\n${YELLOW}Проверка доступности сайта:${NC}"
curl -s -o /dev/null -w "%{http_code}" http://localhost > /dev/null 2>&1
check "Веб-сервер отвечает на localhost"

curl -s -o /dev/null -w "%{http_code}" http://tgpanel.local > /dev/null 2>&1
check "TG Panel доступен по домену tgpanel.local"

# Итоговый отчет
echo -e "\n${GREEN}============================================${NC}"
echo -e "${YELLOW}Рекомендации:${NC}"
echo "1. Если есть ошибки, проверьте лог установки"
echo "2. Убедитесь, что все службы запущены"
echo "3. Проверьте права доступа к директориям"
echo "4. Проверьте настройки PHP в aaPanel"
echo "5. Убедитесь, что домен добавлен в /etc/hosts"
echo "6. Проверьте работу FFmpeg на тестовом файле"
echo -e "${GREEN}============================================${NC}"
