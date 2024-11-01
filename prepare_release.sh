#!/bin/bash

# Установка цветного вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Подготовка релиза TG Panel${NC}"
echo -e "${GREEN}============================================${NC}"

# Создание временной директории
TEMP_DIR="tgpanel_release"
mkdir -p $TEMP_DIR

# Копирование файлов
echo -e "${YELLOW}Копирование файлов...${NC}"
cp check_vds.sh $TEMP_DIR/
cp quick_install.sh $TEMP_DIR/
cp install.sh $TEMP_DIR/
cp check_installation.sh $TEMP_DIR/
cp optimize.sh $TEMP_DIR/
cp backup.sh $TEMP_DIR/
cp manage.sh $TEMP_DIR/
cp README.md $TEMP_DIR/
cp SSH_INSTALL.md $TEMP_DIR/

# Копирование директории installer
echo -e "${YELLOW}Копирование установщика...${NC}"
cp -r installer $TEMP_DIR/

# Создание VERSION файла
echo -e "${YELLOW}Создание информации о версии...${NC}"
cat > $TEMP_DIR/VERSION << EOF
TG Panel Release
Version: 1.0.0
Release Date: $(date +"%Y-%m-%d")
Includes:
- aaPanel integration
- FFmpeg support
- Automatic backup system
- System monitoring
- Installation scripts
EOF

# Создание INSTALL файла
echo -e "${YELLOW}Создание краткой инструкции...${NC}"
cat > $TEMP_DIR/INSTALL << EOF
Быстрая установка:
1. Проверьте VDS:
   chmod +x check_vds.sh
   sudo ./check_vds.sh

2. Установите TG Panel:
   chmod +x quick_install.sh
   sudo ./quick_install.sh

Подробная инструкция в файле SSH_INSTALL.md
EOF

# Создание архива
echo -e "${YELLOW}Создание архива...${NC}"
tar -czf tgpanel_release.tar.gz $TEMP_DIR/

# Создание хеша
echo -e "${YELLOW}Создание хеша...${NC}"
sha256sum tgpanel_release.tar.gz > tgpanel_release.tar.gz.sha256

# Очистка
echo -e "${YELLOW}Очистка временных файлов...${NC}"
rm -rf $TEMP_DIR

echo -e "${GREEN}============================================${NC}"
echo -e "${GREEN}Релиз успешно создан!${NC}"
echo -e "${YELLOW}Файлы:${NC}"
echo "- tgpanel_release.tar.gz"
echo "- tgpanel_release.tar.gz.sha256"
echo -e "\n${YELLOW}Для установки на VDS:${NC}"
echo "1. Загрузите архив на сервер:"
echo "   scp tgpanel_release.tar.gz user@server:~/"
echo "2. Распакуйте архив:"
echo "   tar -xzf tgpanel_release.tar.gz"
echo "3. Следуйте инструкциям в INSTALL файле"
echo -e "${GREEN}============================================${NC}"
