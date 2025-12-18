<?php
/**
 * The Template for displaying the checkout page
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

// Проверяем, что это действительно страница checkout
if ( ! is_checkout() ) {
	wp_die( 'This is not a checkout page' );
}

// Получаем объект checkout
$checkout = WC()->checkout();

// Проверяем, что корзина не пуста
if ( WC()->cart->is_empty() && ! is_wc_endpoint_url( 'order-received' ) ) {
	// Если корзина пуста, показываем сообщение
	?>
	<main class="checkout-page">
		<div class="container checkout-page__container">
			<?php wc_print_notice( __( 'Ваш кошик порожній.', 'woocommerce' ), 'notice' ); ?>
		</div>
	</main>
	<?php
} else {
	// Загружаем кастомный шаблон формы checkout
	if ( file_exists( get_template_directory() . '/woocommerce/checkout/form-checkout.php' ) ) {
		include get_template_directory() . '/woocommerce/checkout/form-checkout.php';
	} else {
		// Fallback на стандартный шаблон WooCommerce
		wc_get_template( 'checkout/form-checkout.php', array( 'checkout' => $checkout ) );
	}
}

get_footer();

