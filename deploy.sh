#!/bin/bash
# Force update files from Git
git fetch origin
git reset --hard origin/main
git clean -fd

