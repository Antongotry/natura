#!/bin/bash
# Простий скрипт для автоматичного деплою на Hostinger
# Вирішує проблему розбіжних гілок

cd /wp-content/themes/natura || exit 1

# Налаштування git
git config pull.rebase false
git config pull.ff only

# Отримуємо зміни з GitHub
git fetch origin

# Примусова синхронізація з GitHub (видаляє локальні зміни)
git reset --hard origin/main

# Очищення
git clean -fd

echo "✅ Деплой завершено успішно"
