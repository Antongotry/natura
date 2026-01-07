#!/bin/bash
# Скрипт для автоматичного деплою на Hostinger
# Використовується для обробки розбіжних гілок

# Визначаємо директорію проекту
if [ -d "/wp-content/themes/natura" ]; then
    cd /wp-content/themes/natura || exit 1
else
    cd "$(git rev-parse --show-toplevel)" || exit 1
fi

# Налаштування git для обробки розбіжних гілок (глобально та локально)
git config --global pull.rebase false 2>/dev/null || true
git config --global pull.ff only 2>/dev/null || true
git config pull.rebase false
git config pull.ff only

# Fetch останні зміни
git fetch origin main

# Force reset до remote (вирішує проблему розбіжних гілок)
git reset --hard origin/main

# Очищення невідстежуваних файлів
git clean -fd

# Оновлення timestamp для запобігання кешуванню (якщо файли існують)
[ -f "assets/css/main.css" ] && touch assets/css/main.css
[ -f "assets/js/main.js" ] && touch assets/js/main.js
[ -f "header.php" ] && touch header.php

echo "✅ Deployment completed successfully"

