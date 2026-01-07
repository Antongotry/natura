#!/bin/bash
# Force update files from Git
# This script should be run by Hostinger on deployment
cd "$(git rev-parse --show-toplevel)" || exit 1

# КРИТИЧНО: Налаштування git для обробки розбіжних гілок (виконується ПЕРШИМ)
git config pull.rebase false 2>/dev/null || true
git config pull.ff only 2>/dev/null || true
git config --global pull.rebase false 2>/dev/null || true
git config --global pull.ff only 2>/dev/null || true

# Fetch latest changes
git fetch origin

# Reset to match remote exactly (force sync) - вирішує проблему розбіжних гілок
git reset --hard origin/main

# Clean untracked files
git clean -fd

# Force update file timestamps to prevent caching
touch assets/css/main.css assets/js/main.js header.php 2>/dev/null || true

echo "✅ Deployment completed successfully"

