#!/bin/bash
# Параллельный деплой на два хостинга одновременно (background processes)
# Использование: ./deploy-parallel.sh "описание коммита"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

COMMIT_MSG="${1:-Auto deploy}"

echo -e "${GREEN}🚀 Начинаю параллельный деплой на два хостинга...${NC}"

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

# Функция деплоя через SFTP (для background execution)
deploy_via_sftp_bg() {
    local HOST=$1
    local USER=$2
    local PASS=$3
    local REMOTE_PATH=$4
    local NAME=$5
    local LOG_FILE="/tmp/deploy_${NAME//[^a-zA-Z0-9]/_}.log"
    
    {
        echo "[$(date)] Начинаю деплой на ${NAME}..."
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
        echo "[$(date)] Деплой на ${NAME} завершен с кодом: $?"
    } > "$LOG_FILE" 2>&1 &
    
    echo "$!"  # Возвращаем PID процесса
}

# Запуск деплоев в фоне
PIDS=()
NAMES=()
LOG_FILES=()

# Деплой на Hostinger (если настроены credentials)
if [ -n "$HOSTINGER_HOST" ] && [ -n "$HOSTINGER_USER" ] && [ -n "$HOSTINGER_PASS" ]; then
    echo -e "${BLUE}📤 Запускаю деплой на Hostinger в фоне...${NC}"
    PID=$(deploy_via_sftp_bg "$HOSTINGER_HOST" "$HOSTINGER_USER" "$HOSTINGER_PASS" "$HOSTINGER_PATH" "Hostinger")
    PIDS+=("$PID")
    NAMES+=("Hostinger")
    LOG_FILES+=("/tmp/deploy_Hostinger.log")
else
    echo -e "${YELLOW}⚠️  Hostinger credentials не настроены. Пропускаю...${NC}"
fi

# Деплой на новый хостинг
echo -e "${BLUE}📤 Запускаю деплой на mx395217.ftp.tools в фоне...${NC}"
PID=$(deploy_via_sftp_bg "$NEW_HOST" "$NEW_USER" "$NEW_PASS" "$NEW_PATH" "mx395217.ftp.tools")
PIDS+=("$PID")
NAMES+=("mx395217.ftp.tools")
LOG_FILES+=("/tmp/deploy_mx395217.ftp.tools.log")

# Ждем завершения всех процессов
echo -e "${YELLOW}⏳ Ожидаю завершения деплоев...${NC}"
FAILED_HOSTS=()
SUCCESS_COUNT=0

for i in "${!PIDS[@]}"; do
    PID="${PIDS[$i]}"
    NAME="${NAMES[$i]}"
    
    # Ждем завершения процесса
    wait "$PID"
    EXIT_CODE=$?
    
    # Проверяем результат
    if [ $EXIT_CODE -eq 0 ]; then
        echo -e "${GREEN}✅ Деплой на ${NAME} успешен!${NC}"
        ((SUCCESS_COUNT++))
    else
        echo -e "${RED}❌ Ошибка деплоя на ${NAME}${NC}"
        FAILED_HOSTS+=("$NAME")
        # Показываем последние строки лога для отладки
        if [ -f "${LOG_FILES[$i]}" ]; then
            echo -e "${YELLOW}Последние строки лога ${NAME}:${NC}"
            tail -5 "${LOG_FILES[$i]}"
        fi
    fi
done

# Итоговый отчет
echo ""
echo -e "${BLUE}═══════════════════════════════════════${NC}"
if [ $SUCCESS_COUNT -eq ${#PIDS[@]} ]; then
    echo -e "${GREEN}✅ Все деплои завершены успешно!${NC}"
    exit 0
else
    echo -e "${RED}❌ Деплой завершен с ошибками${NC}"
    echo -e "${YELLOW}Успешно: ${SUCCESS_COUNT}/${#PIDS[@]}${NC}"
    if [ ${#FAILED_HOSTS[@]} -gt 0 ]; then
        echo -e "${RED}Ошибки на: ${FAILED_HOSTS[*]}${NC}"
        echo -e "${YELLOW}Логи сохранены в: ${LOG_FILES[*]}${NC}"
    fi
    exit 1
fi
