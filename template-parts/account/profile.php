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
		$first_name = sanitize_text_field($_POST['first_name']);
		$email = sanitize_email($_POST['email']);
		$new_password = $_POST['new_password'];
		$confirm_password = $_POST['confirm_password'];

		$errors = [];

		// Валідація email
		if ($email !== $current_user->user_email && email_exists($email)) {
			$errors[] = 'Цей email вже використовується';
		}

		// Валідація пароля
		if (!empty($new_password)) {
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
				'first_name' => $first_name,
				'display_name' => $first_name,
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
?>

<div class="account-profile">
	<?php if ($message) : ?>
		<div class="account-form__message account-form__message--<?php echo esc_attr($message_type); ?>">
			<?php echo esc_html($message); ?>
		</div>
	<?php endif; ?>

	<form class="account-form" method="POST">
		<div class="account-form__field">
			<label class="account-form__label" for="first_name">Ваше ім'я *</label>
			<input type="text" class="account-form__input" id="first_name" name="first_name" 
				   value="<?php echo esc_attr($current_user->first_name); ?>" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="email">Email *</label>
			<input type="email" class="account-form__input" id="email" name="email" 
				   value="<?php echo esc_attr($current_user->user_email); ?>" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="new_password">
				Новий пароль <span class="account-form__label--optional">( залиште порожнім, щоб не змінювати )</span>
			</label>
			<input type="password" class="account-form__input" id="new_password" name="new_password" autocomplete="new-password">
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="confirm_password">Підтвердження пароля</label>
			<input type="password" class="account-form__input" id="confirm_password" name="confirm_password" autocomplete="new-password">
		</div>

		<?php wp_nonce_field('natura_update_profile', 'natura_profile_nonce'); ?>
		<button type="submit" class="account-form__submit">Зберегти зміни</button>
	</form>
</div>






