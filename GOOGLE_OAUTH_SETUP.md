# Інструкція з налаштування авторизації через Google

## Що потрібно зробити

Для того, щоб кнопки "Вхід через Google" та "Зареєструватись через Google" працювали, потрібно налаштувати Google OAuth 2.0. Це безкоштовно та займе близько 10-15 хвилин.

---

## Крок 1: Створення проекту в Google Cloud Console

1. Перейдіть на сайт: **https://console.cloud.google.com/**
2. Увійдіть у свій Google аккаунт
3. У верхній частині сторінки натисніть на випадаючий список проектів (або створіть новий проект)
4. Натисніть кнопку **"Новий проект"** (або "New Project")
5. Введіть назву проекту (наприклад: "Natura Market OAuth")
6. Натисніть **"Створити"** (або "Create")

---

## Крок 2: Увімкнення Google Identity API

1. У меню зліва виберіть **"APIs & Services"** → **"Library"** (Бібліотека)
2. У пошуку введіть: **"Google Identity"** або **"Google+ API"**
3. Знайдіть **"Google Identity Services API"** або **"Google+ API"**
4. Натисніть кнопку **"Enable"** (Увімкнути)

---

## Крок 3: Налаштування екрану згоди OAuth

1. У меню зліва виберіть **"APIs & Services"** → **"OAuth consent screen"** (Екран згоди OAuth)
2. Виберіть **"External"** (Зовнішній) та натисніть **"Create"**
3. Заповніть обов'язкові поля:
   - **App name** (Назва додатку): `Natura Market` (або будь-яка назва)
   - **User support email** (Email підтримки): ваш email
   - **Developer contact information** (Контакти розробника): ваш email
4. Натисніть **"Save and Continue"** (Зберегти та продовжити)
5. На екрані **"Scopes"** (Області доступу) просто натисніть **"Save and Continue"**
6. На екрані **"Test users"** (Тестові користувачі) натисніть **"Save and Continue"**
7. На екрані **"Summary"** (Підсумок) натисніть **"Back to Dashboard"** (Повернутися до панелі)

---

## Крок 4: Створення OAuth 2.0 Credentials (облікових даних)

1. У меню зліва виберіть **"APIs & Services"** → **"Credentials"** (Облікові дані)
2. Натисніть кнопку **"Create Credentials"** → **"OAuth client ID"**
3. Виберіть тип додатку: **"Web application"** (Веб-додаток)
4. Введіть назву (наприклад: `Natura Market Web Client`)
5. У полі **"Authorized JavaScript origins"** (Дозволені джерела JavaScript) додайте:
   ```
   https://ваш-домен.com
   ```
   (Замініть `ваш-домен.com` на реальну адресу вашого сайту, наприклад: `https://natura-market.com`)
   
   ⚠️ **Важливо**: Якщо тестуєте на локальному сервері, також додайте:
   ```
   http://localhost
   ```

6. У полі **"Authorized redirect URIs"** (Дозволені URI перенаправлення) додайте:
   ```
   https://ваш-домен.com/wp-admin/admin-ajax.php?action=natura_google_oauth_callback
   ```
   (Замініть `ваш-домен.com` на реальну адресу вашого сайту)
   
   ⚠️ **Важливо**: Якщо тестуєте на локальному сервері, також додайте:
   ```
   http://localhost/wp-admin/admin-ajax.php?action=natura_google_oauth_callback
   ```

7. Натисніть **"Create"** (Створити)

---

## Крок 5: Збереження Client ID та Client Secret

Після створення OAuth Client ID, Google покаже вам два важливі значення:

1. **Client ID** (Ідентифікатор клієнта) - виглядає приблизно так:
   ```
   123456789-abcdefghijklmnop.apps.googleusercontent.com
   ```

2. **Client Secret** (Секретний ключ клієнта) - виглядає приблизно так:
   ```
   GOCSPX-abcdefghijklmnopqrstuvwxyz
   ```

⚠️ **ВАЖЛИВО**: Обов'язково скопіюйте та збережіть ці значення в безпечному місці! Вони знадобляться для налаштування WordPress.

---

## Крок 6: Додавання даних у WordPress

Після отримання Client ID та Client Secret, їх потрібно додати в WordPress. Є два способи:

### Спосіб 1: Через wp-config.php (рекомендовано для безпеки) ⭐

1. Відкрийте файл `wp-config.php` у кореневій папці вашого WordPress сайту
2. Знайдіть рядок `/* That's all, stop editing! */`
3. **ПЕРЕД** цим рядком додайте наступні рядки:
   ```php
   define('NATURA_GOOGLE_CLIENT_ID', 'ваш-client-id');
   define('NATURA_GOOGLE_CLIENT_SECRET', 'ваш-client-secret');
   ```
4. Замініть `ваш-client-id` та `ваш-client-secret` на реальні значення, які ви отримали з Google Cloud Console
5. Збережіть файл

**Приклад:**
```php
define('NATURA_GOOGLE_CLIENT_ID', '123456789-abcdefghijklmnop.apps.googleusercontent.com');
define('NATURA_GOOGLE_CLIENT_SECRET', 'GOCSPX-abcdefghijklmnopqrstuvwxyz');
```

### Спосіб 2: Через налаштування в адмін-панелі WordPress

1. Увійдіть в адмін-панель WordPress
2. Перейдіть в **"Налаштування"** → **"Google OAuth"**
3. Введіть **Client ID** та **Client Secret** у відповідні поля
4. Натисніть **"Зберегти зміни"**

---

## Крок 7: Перевірка роботи

Після налаштування перевірте:

1. Перейдіть на сторінку авторизації вашого сайту
2. Переконайтеся, що кнопки **"Вхід через Google"** та **"Зареєструватись через Google"** більше не сірі (не disabled)
3. Натисніть на кнопку "Вхід через Google"
4. Має відкритися вікно з вибором Google аккаунта
5. Після вибору аккаунта та підтвердження, ви маєте автоматично увійти в систему

---

## Важливі зауваження

- ✅ **Authorized redirect URIs** повинні точно збігатися з URL вашого сайту
- ✅ Для продакшн-сайту використовуйте тільки HTTPS (не HTTP)
- ✅ Client Secret повинен зберігатися в безпеці (не публікуйте його в публічних файлах)
- ✅ Після змін в Google Cloud Console може знадобитися кілька хвилин для застосування
- ✅ Якщо щось не працює, перевірте, чи правильно скопійовані Client ID та Client Secret (без зайвих пробілів)

---

## Якщо виникли проблеми

1. **Кнопки Google все ще сірі (disabled)**
   - Перевірте, чи правильно додані Client ID та Client Secret в `wp-config.php` або в налаштуваннях
   - Переконайтеся, що немає помилок у файлі `wp-config.php`

2. **Помилка "redirect_uri_mismatch"**
   - Перевірте, чи правильно вказаний **Authorized redirect URI** в Google Cloud Console
   - Він повинен точно збігатися з: `https://ваш-домен.com/wp-admin/admin-ajax.php?action=natura_google_oauth_callback`

3. **Помилка "invalid_client"**
   - Перевірте, чи правильно скопійовані Client ID та Client Secret
   - Переконайтеся, що немає зайвих пробілів

4. **Після авторизації нічого не відбувається**
   - Перевірте консоль браузера (F12) на наявність помилок
   - Переконайтеся, що API Google Identity увімкнено в Google Cloud Console

---

## Підтримка

Якщо у вас виникли питання або проблеми з налаштуванням, зверніться до розробника сайту.
