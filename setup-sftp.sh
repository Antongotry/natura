#!/bin/bash
# Скрипт для настройки SFTP credentials
# Запусти один раз: ./setup-sftp.sh

echo "🔧 Настройка SFTP для автоматического деплоя"
echo ""
echo "Введи данные из hPanel → FTP Accounts:"
echo ""

read -p "SFTP Host (например: ftp.yoursite.com): " SFTP_HOST
read -p "SFTP User (логин): " SFTP_USER
read -s -p "SFTP Password (пароль): " SFTP_PASS
echo ""

# Добавляем в ~/.zshrc или ~/.bashrc
SHELL_RC="$HOME/.zshrc"
if [ ! -f "$SHELL_RC" ]; then
    SHELL_RC="$HOME/.bashrc"
fi

echo "" >> "$SHELL_RC"
echo "# Hostinger SFTP для автоматического деплоя" >> "$SHELL_RC"
echo "export HOSTINGER_SFTP_HOST='$SFTP_HOST'" >> "$SHELL_RC"
echo "export HOSTINGER_SFTP_USER='$SFTP_USER'" >> "$SHELL_RC"
echo "export HOSTINGER_SFTP_PASS='$SFTP_PASS'" >> "$SHELL_RC"

echo ""
echo "✅ Credentials сохранены в $SHELL_RC"
echo "Перезапусти терминал или выполни: source $SHELL_RC"
echo ""
echo "Теперь используй: ./deploy-auto.sh \"описание изменений\""

