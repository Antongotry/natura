<?php
/**
 * Lost password confirmation text.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/myaccount/lost-password-confirmation.php.
 *
 * @see     https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates
 * @version 3.9.0
 */

defined( 'ABSPATH' ) || exit;
?>

<div class="auth-page__lost-password-confirmation">
	<div class="auth-page__form-top">
		<p class="auth-page__confirmation-title">Лист для відновлення паролю надіслано.</p>
		<p class="auth-page__confirmation-text">Лист з посиланням для відновлення паролю було<br>надіслано на адресу електронної пошти, прив'язану до<br>вашого облікового запису, доставка повідомлення може<br>зайняти кілька хвилин. Будь ласка, зачекайте не менше<br>10 хвилин, перш ніж ініціювати ще один запит.</p>
	</div>
	<div class="auth-page__form-bottom">
		<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="auth-page__submit auth-page__submit--dark">Повернутись до каталогу</a>
	</div>
</div>





