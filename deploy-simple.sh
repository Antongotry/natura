#!/bin/bash
# Простий скрипт деплою для Hostinger
# Використовується для вирішення проблеми розбіжних гілок

cd /wp-content/themes/natura || exit 1

# Налаштування git
git config pull.rebase false
git config pull.ff only

# Примусова синхронізація з GitHub
git fetch origin
git reset --hard origin/main
git clean -fd

echo "✅ Deployment completed"


