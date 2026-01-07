#!/bin/bash
# Force update files from Git
# This script should be run by Hostinger on deployment
cd "$(git rev-parse --show-toplevel)" || exit 1

# Налаштування git для обробки розбіжних гілок
git config pull.rebase false
git config pull.ff only

# Fetch latest changes
git fetch origin

# Reset to match remote exactly (force sync)
git reset --hard origin/main

# Clean untracked files
git clean -fd

# Force update file timestamps to prevent caching
touch assets/css/main.css assets/js/main.js header.php

echo "✅ Deployment completed successfully"

