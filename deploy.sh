#!/bin/bash
# Простий скрипт для автоматичного деплою на Hostinger
# Вирішує проблему розбіжних гілок

cd /wp-content/themes/natura || exit 1

# Налаштування git (глобально та локально)
git config --global pull.rebase false 2>/dev/null
git config --global pull.ff only 2>/dev/null
git config pull.rebase false
git config pull.ff only

# Отримуємо зміни з GitHub
git fetch origin main

# Примусова синхронізація з GitHub (видаляє локальні зміни)
git reset --hard origin/main

# Очищення
git clean -fd

echo "✅ Деплой завершено успішно"
