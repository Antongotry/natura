#!/bin/bash
# Скрипт для налаштування git pull на сервері
# Виконується один раз для налаштування

cd /wp-content/themes/natura || exit 1

# Налаштування git для обробки розбіжних гілок
git config --global pull.rebase false
git config --global pull.ff only
git config pull.rebase false
git config pull.ff only

echo "✅ Git config налаштовано"

