#!/bin/bash
# Скрипт для автоматичного деплою на Hostinger
# Використовується для обробки розбіжних гілок

cd "$(git rev-parse --show-toplevel)" || exit 1

# Налаштування git для обробки розбіжних гілок
git config pull.rebase false
git config pull.ff only

# Fetch останні зміни
git fetch origin

# Force reset до remote (вирішує проблему розбіжних гілок)
git reset --hard origin/main

# Очищення невідстежуваних файлів
git clean -fd

# Оновлення timestamp для запобігання кешуванню
touch assets/css/main.css assets/js/main.js header.php

echo "✅ Deployment completed successfully"

