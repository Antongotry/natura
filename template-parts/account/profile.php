<?php
/**
 * Account - Profile Section
 */

$current_user = wp_get_current_user();
$message = '';
$message_type = '';

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['natura_profile_nonce'])) {
	if (wp_verify_nonce($_POST['natura_profile_nonce'], 'natura_update_profile')) {
		$full_name_raw = isset($_POST['full_name']) ? wp_unslash($_POST['full_name']) : '';
		$phone_raw = isset($_POST['phone']) ? wp_unslash($_POST['phone']) : '';
		$email_raw = isset($_POST['email']) ? wp_unslash($_POST['email']) : '';
		$current_password = isset($_POST['current_password']) ? (string) wp_unslash($_POST['current_password']) : '';
		$new_password = isset($_POST['new_password']) ? (string) wp_unslash($_POST['new_password']) : '';
		$confirm_password = isset($_POST['confirm_password']) ? (string) wp_unslash($_POST['confirm_password']) : '';

		$full_name = sanitize_text_field($full_name_raw);
		$phone = sanitize_text_field($phone_raw);
		$email = sanitize_email($email_raw);

		$errors = [];

		// Валідація імені/телефону
		if (empty($full_name)) {
			$errors[] = 'Вкажіть ім\'я та прізвище';
		}
		if (empty($phone)) {
			$errors[] = 'Вкажіть номер телефону';
		}

		// Валідація email
		if ($email !== $current_user->user_email && email_exists($email)) {
			$errors[] = 'Цей email вже використовується';
		}

		// Валідація пароля
		if (!empty($new_password)) {
			// Поточний пароль потрібен лише якщо змінюємо пароль
			if (empty($current_password)) {
				$errors[] = 'Введіть поточний пароль';
			} elseif (!wp_check_password($current_password, $current_user->user_pass, $current_user->ID)) {
				$errors[] = 'Невірний поточний пароль';
			}

			if (strlen($new_password) < 6) {
				$errors[] = 'Пароль має містити мінімум 6 символів';
			}
			if ($new_password !== $confirm_password) {
				$errors[] = 'Паролі не співпадають';
			}
		}

		if (empty($errors)) {
			$user_data = [
				'ID' => $current_user->ID,
				'first_name' => $full_name,
				'display_name' => $full_name,
				'user_email' => $email,
			];

			if (!empty($new_password)) {
				$user_data['user_pass'] = $new_password;
			}

			$result = wp_update_user($user_data);

			if (is_wp_error($result)) {
				$message = 'Помилка при оновленні профілю';
				$message_type = 'error';
			} else {
				// Зберігаємо дані WooCommerce (щоб підтягувались в checkout)
				update_user_meta($current_user->ID, 'billing_first_name', $full_name);
				update_user_meta($current_user->ID, 'billing_last_name', '');
				update_user_meta($current_user->ID, 'billing_phone', $phone);

				$message = 'Профіль успішно оновлено';
				$message_type = 'success';
				$current_user = wp_get_current_user();
			}
		} else {
			$message = implode('. ', $errors);
			$message_type = 'error';
		}
	}
}

$full_name_value = get_user_meta($current_user->ID, 'billing_first_name', true);
if (empty($full_name_value)) {
	$full_name_value = $current_user->display_name ?: $current_user->first_name;
}
$phone_value = get_user_meta($current_user->ID, 'billing_phone', true);
?>

<div class="account-profile">
	<?php if ($message) : ?>
		<div class="account-form__message account-form__message--<?php echo esc_attr($message_type); ?>">
			<?php echo esc_html($message); ?>
		</div>
	<?php endif; ?>

	<form class="account-form" method="POST">
		<div class="account-form__field">
			<label class="account-form__label" for="full_name">Ваше ім'я та прізвище *</label>
			<input type="text" class="account-form__input" id="full_name" name="full_name"
				   value="<?php echo esc_attr($full_name_value); ?>" placeholder="Олександр Степаненко" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="phone">Номер телефону *</label>
			<input type="tel" class="account-form__input" id="phone" name="phone"
				   value="<?php echo esc_attr($phone_value); ?>" placeholder="+38 (093) 200 22 11" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="email">Email *</label>
			<input type="email" class="account-form__input" id="email" name="email"
				   value="<?php echo esc_attr($current_user->user_email); ?>" placeholder="zakaz@naturamarket.kiev.ua" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="current_password">
				Поточний пароль <span class="account-form__label--optional">( залиште порожнім, щоб не змінювати )</span>
			</label>
			<input type="password" class="account-form__input" id="current_password" name="current_password" autocomplete="current-password">
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="new_password">
				Новий пароль <span class="account-form__label--optional">( залиште порожнім, щоб не змінювати )</span>
			</label>
			<input type="password" class="account-form__input" id="new_password" name="new_password" autocomplete="new-password">
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="confirm_password">Підтвердити новий пароль</label>
			<input type="password" class="account-form__input" id="confirm_password" name="confirm_password" autocomplete="new-password">
		</div>

		<?php wp_nonce_field('natura_update_profile', 'natura_profile_nonce'); ?>
		<button type="submit" class="account-form__submit">Зберегти зміни</button>
	</form>
</div>






