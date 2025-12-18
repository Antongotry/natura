#!/bin/bash
# Автоматический деплой: push в GitHub + деплой через SFTP
# Использование: ./deploy-auto.sh "описание коммита"

GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

COMMIT_MSG="${1:-Auto deploy}"

echo -e "${GREEN}🚀 Начинаю автоматический деплой...${NC}"

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

# 3. Проверяем наличие SFTP credentials
if [ -z "$HOSTINGER_SFTP_HOST" ] || [ -z "$HOSTINGER_SFTP_USER" ] || [ -z "$HOSTINGER_SFTP_PASS" ]; then
    echo -e "${YELLOW}⚠️  SFTP credentials не установлены. Используй:${NC}"
    echo "export HOSTINGER_SFTP_HOST='твой-хост'"
    echo "export HOSTINGER_SFTP_USER='твой-логин'"
    echo "export HOSTINGER_SFTP_PASS='твой-пароль'"
    echo -e "${GREEN}✅ GitHub Actions автоматически задеплоит через ~1 минуту${NC}"
    exit 0
fi

# 4. Деплой через SFTP
echo -e "${YELLOW}📤 Деплою через SFTP...${NC}"

if ! command -v lftp &> /dev/null; then
    echo -e "${RED}❌ lftp не установлен. Устанавливаю...${NC}"
    if [[ "$OSTYPE" == "darwin"* ]]; then
        brew install lftp
    else
        echo "Установи lftp: sudo apt-get install lftp"
        exit 1
    fi
fi

lftp -c "
set ftp:ssl-allow no
set sftp:auto-confirm yes
open -u ${HOSTINGER_SFTP_USER},${HOSTINGER_SFTP_PASS} sftp://${HOSTINGER_SFTP_HOST}
cd /wp-content/themes/natura/
mirror -R --delete --verbose --exclude-glob .git* --exclude-glob .DS_Store --exclude-glob .github .
bye
"

if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Деплой успешен!${NC}"
else
    echo -e "${RED}❌ Ошибка деплоя через SFTP. GitHub Actions задеплоит автоматически.${NC}"
fi

