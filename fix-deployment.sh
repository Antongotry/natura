#!/bin/bash
# Скрипт для виправлення деплою на сервері Hostinger
# Виконайте цей скрипт один раз на сервері через SSH

echo "🔧 Налаштування git config для автоматичного деплою..."

# Переходимо в директорію теми
cd /wp-content/themes/natura || {
    echo "❌ Помилка: не вдалося знайти директорію /wp-content/themes/natura"
    exit 1
}

# Налаштування git config глобально
git config --global pull.rebase false
git config --global pull.ff only

# Налаштування git config локально для репозиторію
git config pull.rebase false
git config pull.ff only

echo "✅ Git config налаштовано!"
echo ""
echo "Тепер перевірте налаштування деплою в Hostinger:"
echo "1. Перейдіть в налаштування деплою"
echo "2. Змініть команду з 'git pull' на одну з наступних:"
echo ""
echo "   Варіант 1 (рекомендовано):"
echo "   bash deploy.sh"
echo ""
echo "   Варіант 2:"
echo "   git fetch origin && git reset --hard origin/main"
echo ""
echo "   Варіант 3:"
echo "   git pull --no-rebase --ff-only"
echo ""

