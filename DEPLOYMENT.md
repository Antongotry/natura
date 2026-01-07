# Інструкції для налаштування автоматичного деплою на Hostinger

## Проблема
Hostinger виконує `git pull`, який не може обробити розбіжні гілки.

## Рішення 1: Використання скрипту deploy.sh (РЕКОМЕНДОВАНО)

В налаштуваннях деплою Hostinger вкажіть команду:
```bash
bash deploy.sh
```

Або повний шлях:
```bash
cd /wp-content/themes/natura && bash deploy.sh
```

## Рішення 2: Змінити команду git pull

В налаштуваннях деплою Hostinger замініть стандартну команду `git pull` на:
```bash
git config pull.rebase false && git config pull.ff only && git pull --no-rebase --ff-only
```

Або використайте force reset:
```bash
git fetch origin && git reset --hard origin/main
```

## Рішення 3: Налаштувати git config глобально на сервері

Виконайте один раз на сервері через SSH:
```bash
cd /wp-content/themes/natura
git config --global pull.rebase false
git config --global pull.ff only
```

## Перевірка

Після налаштування зробіть тестовий коміт і перевірте, чи спрацював деплой.

