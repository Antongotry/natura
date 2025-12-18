<?php
/**
 * Account - Address Section
 */

$current_user = wp_get_current_user();
$customer_id = $current_user->ID;
$message = '';
$message_type = '';

// Обробка форми
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['natura_address_nonce'])) {
	if (wp_verify_nonce($_POST['natura_address_nonce'], 'natura_update_address')) {
		$city = sanitize_text_field($_POST['city']);
		$address = sanitize_text_field($_POST['address']);
		$address_details = sanitize_text_field($_POST['address_details']);
		$company = sanitize_text_field($_POST['company']);

		// Зберігаємо адресу доставки WooCommerce
		update_user_meta($customer_id, 'shipping_city', $city);
		update_user_meta($customer_id, 'shipping_address_1', $address);
		update_user_meta($customer_id, 'shipping_address_2', $address_details);
		update_user_meta($customer_id, 'shipping_company', $company);
		update_user_meta($customer_id, 'shipping_country', 'UA');

		$message = 'Адресу успішно збережено';
		$message_type = 'success';
	}
}

// Отримуємо збережені дані
$city = get_user_meta($customer_id, 'shipping_city', true);
$address = get_user_meta($customer_id, 'shipping_address_1', true);
$address_details = get_user_meta($customer_id, 'shipping_address_2', true);
$company = get_user_meta($customer_id, 'shipping_company', true);
?>

<div class="account-address">
	<h2 class="account-section__title">Адреса доставки</h2>
	
	<?php if ($message) : ?>
		<div class="account-form__message account-form__message--<?php echo esc_attr($message_type); ?>">
			<?php echo esc_html($message); ?>
		</div>
	<?php endif; ?>

	<form class="account-form" method="POST">
		<div class="account-form__field">
			<label class="account-form__label" for="city">Місто / Населений пункт *</label>
			<input type="text" class="account-form__input" id="city" name="city" 
				   value="<?php echo esc_attr($city); ?>" placeholder="Київ" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="address">Адреса *</label>
			<input type="text" class="account-form__input" id="address" name="address" 
				   value="<?php echo esc_attr($address); ?>" placeholder="Каховська 60" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="address_details">Під'їзд / Поверх / Квартира *</label>
			<input type="text" class="account-form__input" id="address_details" name="address_details" 
				   value="<?php echo esc_attr($address_details); ?>" placeholder="19/1/192" required>
		</div>

		<div class="account-form__field">
			<label class="account-form__label" for="company">
				Назва компанії <span class="account-form__label--optional">( необов'язково )</span>
			</label>
			<input type="text" class="account-form__input" id="company" name="company" 
				   value="<?php echo esc_attr($company); ?>" placeholder="Натура Маркет">
		</div>

		<?php wp_nonce_field('natura_update_address', 'natura_address_nonce'); ?>
		<button type="submit" class="account-form__submit">Зберегти зміни</button>
	</form>
</div>







