<?php
/**
 * Simple product add to cart
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

global $product;

if ( ! $product->is_purchasable() ) {
	return;
}

echo wc_get_stock_html( $product ); // WPCS: XSS ok.

if ( $product->is_in_stock() ) : ?>

	<?php do_action( 'woocommerce_before_add_to_cart_form' ); ?>

	<form class="cart" action="<?php echo esc_url( apply_filters( 'woocommerce_add_to_cart_form_action', $product->get_permalink() ) ); ?>" method="post" enctype='multipart/form-data'>
		<?php do_action( 'woocommerce_before_add_to_cart_button' ); ?>

		<?php
		do_action( 'woocommerce_before_add_to_cart_quantity' );

		// Используем кастомный quantity input
		wc_get_template( 'single-product/add-to-cart/quantity-input.php', array(
			'min_value'   => $product->get_min_purchase_quantity(),
			'max_value'   => $product->get_max_purchase_quantity(),
			'input_value' => isset( $_POST['quantity'] ) ? wc_stock_amount( wp_unslash( $_POST['quantity'] ) ) : $product->get_min_purchase_quantity(), // WPCS: CSRF ok, input var ok.
		) );

		do_action( 'woocommerce_after_add_to_cart_quantity' );
		?>

		<button type="submit" name="add-to-cart" value="<?php echo esc_attr( $product->get_id() ); ?>" class="single_add_to_cart_button button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' ) : '' ); ?>"><?php echo esc_html( $product->single_add_to_cart_text() ); ?></button>

		<?php do_action( 'woocommerce_after_add_to_cart_button' ); ?>
	</form>

	<?php do_action( 'woocommerce_after_add_to_cart_form' ); ?>

<?php else : ?>
	<!-- Форма "Повідомити про наявність" для товарів outofstock -->
	<div class="stock-notification-form">
		<form class="stock-notification" method="post" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>">
			<?php wp_nonce_field( 'natura_stock_notification', 'stock_notification_nonce' ); ?>
			<div class="stock-notification__field">
				<?php if ( is_user_logged_in() ) : ?>
					<?php $current_user = wp_get_current_user(); ?>
					<input type="email" name="email" value="<?php echo esc_attr( $current_user->user_email ); ?>" placeholder="<?php esc_attr_e( 'Ваш email', 'natura' ); ?>" required class="stock-notification__input" />
				<?php else : ?>
					<input type="email" name="email" placeholder="<?php esc_attr_e( 'Ваш email', 'natura' ); ?>" required class="stock-notification__input" />
				<?php endif; ?>
			</div>
			<button type="submit" class="single_add_to_cart_button button alt stock-notification__button">
				<?php esc_html_e( 'Повідомити про наявність', 'natura' ); ?>
			</button>
			<div class="stock-notification__message" style="display: none;"></div>
		</form>
	</div>
<?php endif; ?>












