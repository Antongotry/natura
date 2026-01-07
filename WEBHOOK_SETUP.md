# Налаштування GitHub Webhook для автоматичного деплою на Hostinger

## URL Webhook
```
https://webhooks.hostinger.com/deploy/f8add29293caab24b51da9b07d93cf9b
```

## Інструкція з налаштування

### Крок 1: Відкрийте налаштування webhooks в GitHub
Перейдіть за посиланням:
https://github.com/Antongotry/natura/settings/hooks/new

### Крок 2: Заповніть форму

1. **Payload URL**: 
   ```
   https://webhooks.hostinger.com/deploy/f8add29293caab24b51da9b07d93cf9b
   ```

2. **Content type**: 
   - Виберіть `application/json`

3. **Which events would you like to trigger this webhook?**
   - Виберіть **"Just the push event"** або **"Let me select individual events"** і оберіть:
     - ✅ Push
     - ✅ Branch or tag creation
     - ✅ Branch or tag deletion (опціонально)

4. **Active**: 
   - ✅ Поставте галочку (має бути увімкнено за замовчуванням)

5. **Secret** (опціонально):
   - Залиште порожнім, якщо Hostinger не вимагає secret

### Крок 3: Збережіть webhook
Натисніть кнопку **"Add webhook"**

### Крок 4: Перевірка
1. Зробіть тестовий коміт:
   ```bash
   git commit --allow-empty -m "TEST: Перевірка webhook"
   git push
   ```

2. Перевірте в GitHub:
   - Перейдіть в Settings → Webhooks
   - Знайдіть ваш webhook
   - Перевірте останні delivery (останні виклики)
   - Має бути статус 200 (успішно)

3. Перевірте в Hostinger:
   - Перейдіть в панель Hostinger
   - Перевірте логи деплою
   - Файли мають оновитися автоматично

## Усунення проблем

### Якщо webhook не спрацьовує:
1. Перевірте, чи правильно вказано URL
2. Перевірте логи delivery в GitHub (Settings → Webhooks → ваш webhook → Recent Deliveries)
3. Перевірте, чи правильно налаштований git config на сервері (див. нижче)

### Якщо деплой падає з помилкою про розбіжні гілки:

**Крок 1: Виконайте на сервері через SSH:**
```bash
cd /wp-content/themes/natura
bash fix-deployment.sh
```

Або вручну:
```bash
cd /wp-content/themes/natura
git config --global pull.rebase false
git config --global pull.ff only
git config pull.rebase false
git config pull.ff only
```

**Крок 2: Змініть команду деплою в Hostinger:**

В налаштуваннях автоматичного деплою Hostinger замініть стандартну команду `git pull` на:

**Варіант 1 (РЕКОМЕНДОВАНО):**
```bash
bash deploy.sh
```

**Варіант 2:**
```bash
git fetch origin && git reset --hard origin/main
```

**Варіант 3:**
```bash
git pull --no-rebase --ff-only
```

### Якщо webhook працював раніше, але зараз не працює:

1. Перевірте логи delivery в GitHub (Settings → Webhooks → Recent Deliveries)
2. Перевірте, чи не змінилася команда деплою в Hostinger
3. Виконайте `fix-deployment.sh` на сервері
4. Зробіть тестовий коміт для перевірки

