<?php
/**
 * Lost password form
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/form-lost-password.php.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 9.2.0
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_lost_password_form' );
?>

<div class="auth-page__lost-password-wrapper">
	<form method="post" class="auth-page__form auth-page__form--active woocommerce-ResetPassword lost_reset_password">
		<p class="auth-page__lost-password-text">Забули пароль? Будь ласка, введіть ваш логін або адресу електронної пошти. Ви отримаєте електронною поштою посилання для створення нового паролю.</p>

		<div class="auth-page__field">
			<label class="auth-page__label" for="user_login">Email *</label>
			<input class="auth-page__input" type="text" name="user_login" id="user_login" autocomplete="username" required aria-required="true" />
		</div>

		<?php do_action( 'woocommerce_lostpassword_form' ); ?>

		<div class="auth-page__buttons">
			<input type="hidden" name="wc_reset_password" value="true" />
			<button type="submit" class="auth-page__submit">Скинути пароль</button>
		</div>

		<?php wp_nonce_field( 'lost_password', 'woocommerce-lost-password-nonce' ); ?>
	</form>
</div>
<?php
do_action( 'woocommerce_after_lost_password_form' );






