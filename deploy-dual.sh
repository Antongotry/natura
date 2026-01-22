#!/bin/bash
# Автоматический деплой на два хостинга одновременно
# Использование: ./deploy-dual.sh "описание коммита"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

COMMIT_MSG="${1:-Auto deploy}"

echo -e "${GREEN}🚀 Начинаю автоматический деплой на два хостинга...${NC}"

# 1. Git add и commit
echo -e "${YELLOW}📝 Коммичу изменения...${NC}"
git add .
git commit -m "$COMMIT_MSG" || echo "Нет изменений для коммита"

# 2. Push в GitHub
echo -e "${YELLOW}📤 Пущу в GitHub...${NC}"
git push || {
    echo -e "${RED}❌ Ошибка при push в GitHub${NC}"
    exit 1
}

echo -e "${GREEN}✅ Изменения запушены в GitHub${NC}"

# Проверка наличия lftp
if ! command -v lftp &> /dev/null; then
    echo -e "${RED}❌ lftp не установлен. Устанавливаю...${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install lftp
    elif [[ "$OSTYPE" == "linux-gnu"* ]]; then
        sudo apt-get update && sudo apt-get install -y lftp
    else
        echo -e "${RED}❌ Установи lftp вручную${NC}"
        exit 1
    fi
fi

# Настройки для Hostinger
HOSTINGER_HOST="${HOSTINGER_SFTP_HOST:-}"
HOSTINGER_USER="${HOSTINGER_SFTP_USER:-}"
HOSTINGER_PASS="${HOSTINGER_SFTP_PASS:-}"
HOSTINGER_PATH="/wp-content/themes/natura/"

# Настройки для нового хостинга (mx395217.ftp.tools)
NEW_HOST="mx395217.ftp.tools"
NEW_USER="mx395217"
NEW_PASS="Nature0211"
NEW_PATH="/wp-content/themes/natura/"

# Функция деплоя через SFTP
deploy_via_sftp() {
    local HOST=$1
    local USER=$2
    local PASS=$3
    local REMOTE_PATH=$4
    local NAME=$5
    
    echo -e "${BLUE}📤 Деплою на ${NAME}...${NC}"
    
    lftp -c "
    set ftp:ssl-allow no
    set sftp:auto-confirm yes
    set net:timeout 30
    set net:max-retries 3
    open -u ${USER},${PASS} sftp://${HOST}
    cd ${REMOTE_PATH}
    mirror -R --delete --verbose --exclude-glob .git* --exclude-glob .DS_Store --exclude-glob .github --exclude-glob deploy*.sh --exclude-glob '*.md' --exclude-glob '*.txt' .
    bye
    " 2>&1
    
    return $?
}

# Счетчики успешных деплоев
SUCCESS_COUNT=0
FAILED_HOSTS=()

# Деплой на Hostinger (если настроены credentials)
if [ -n "$HOSTINGER_HOST" ] && [ -n "$HOSTINGER_USER" ] && [ -n "$HOSTINGER_PASS" ]; then
    if deploy_via_sftp "$HOSTINGER_HOST" "$HOSTINGER_USER" "$HOSTINGER_PASS" "$HOSTINGER_PATH" "Hostinger"; then
        echo -e "${GREEN}✅ Деплой на Hostinger успешен!${NC}"
        ((SUCCESS_COUNT++))
    else
        echo -e "${RED}❌ Ошибка деплоя на Hostinger${NC}"
        FAILED_HOSTS+=("Hostinger")
    fi
else
    echo -e "${YELLOW}⚠️  Hostinger credentials не настроены. Пропускаю...${NC}"
    echo -e "${YELLOW}Установи переменные окружения:${NC}"
    echo "  export HOSTINGER_SFTP_HOST='твой-хост.hostingersite.com'"
    echo "  export HOSTINGER_SFTP_USER='твой-логин'"
    echo "  export HOSTINGER_SFTP_PASS='твой-пароль'"
fi

# Деплой на новый хостинг (mx395217.ftp.tools)
if deploy_via_sftp "$NEW_HOST" "$NEW_USER" "$NEW_PASS" "$NEW_PATH" "mx395217.ftp.tools"; then
    echo -e "${GREEN}✅ Деплой на mx395217.ftp.tools успешен!${NC}"
    ((SUCCESS_COUNT++))
else
    echo -e "${RED}❌ Ошибка деплоя на mx395217.ftp.tools${NC}"
    FAILED_HOSTS+=("mx395217.ftp.tools")
fi

# Итоговый отчет
echo ""
echo -e "${BLUE}═══════════════════════════════════════${NC}"
if [ $SUCCESS_COUNT -eq 2 ] || ([ $SUCCESS_COUNT -eq 1 ] && [ -z "$HOSTINGER_HOST" ]); then
    echo -e "${GREEN}✅ Деплой завершен!${NC}"
    if [ ${#FAILED_HOSTS[@]} -gt 0 ]; then
        echo -e "${YELLOW}⚠️  Ошибки на: ${FAILED_HOSTS[*]}${NC}"
    fi
    exit 0
else
    echo -e "${RED}❌ Деплой завершен с ошибками${NC}"
    if [ ${#FAILED_HOSTS[@]} -gt 0 ]; then
        echo -e "${RED}Ошибки на: ${FAILED_HOSTS[*]}${NC}"
    fi
    exit 1
fi
