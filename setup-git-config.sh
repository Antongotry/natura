#!/bin/bash
# Скрипт для налаштування git config на сервері
# Виконується один раз при деплої

cd "$(git rev-parse --show-toplevel)" || exit 1

# Налаштування git для обробки розбіжних гілок
git config pull.rebase false
git config pull.ff only
git config --global pull.rebase false
git config --global pull.ff only

echo "Git config налаштовано"

