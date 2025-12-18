#!/bin/bash
# Force update files from Git
# This script should be run by Hostinger on deployment
cd "$(git rev-parse --show-toplevel)" || exit 1
git fetch origin
git reset --hard origin/main
git clean -fd
# Force update file timestamps
touch assets/css/main.css assets/js/main.js header.php

