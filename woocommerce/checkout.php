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

// Endpoint: order received (thank you page).
// ВАЖНО: is_wc_endpoint_url('order-received') тут не срабатывает стабильно, поэтому используем is_order_received_page().
// Рендерим наш woocommerce/checkout/thankyou.php, иначе будет показываться форма checkout с пустой корзиной.
if ( function_exists( 'is_order_received_page' ) && is_order_received_page() && function_exists( 'wc_get_order' ) ) {
	$order_id  = absint( get_query_var( 'order-received' ) );
	$order_key = isset( $_GET['key'] ) ? wc_clean( wp_unslash( $_GET['key'] ) ) : '';

	$order = $order_id ? wc_get_order( $order_id ) : false;
	if ( $order && $order_key && ! hash_equals( (string) $order->get_order_key(), (string) $order_key ) ) {
		$order = false;
	}

	wc_get_template( 'checkout/thankyou.php', array( 'order' => $order ) );
	get_footer();
	return;
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

