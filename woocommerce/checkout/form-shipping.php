<?php
/**
 * Checkout shipping information form
 *
 * @package Natura
 * @version 3.6.0
 * @global WC_Checkout $checkout
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="woocommerce-shipping-fields">
	<div class="shipping_address">

		<?php do_action( 'woocommerce_before_checkout_shipping_form', $checkout ); ?>

		<div class="woocommerce-shipping-fields__field-wrapper">
			<?php
			$fields = $checkout->get_checkout_fields( 'shipping' );

			if ( ! empty( $fields ) ) {
				foreach ( $fields as $key => $field ) {
					woocommerce_form_field( $key, $field, $checkout->get_value( $key ) );
				}
			}
			?>
		</div>

		<?php do_action( 'woocommerce_after_checkout_shipping_form', $checkout ); ?>

	</div>
</div>
<div class="woocommerce-additional-fields">
	<?php do_action( 'woocommerce_before_order_notes', $checkout ); ?>

	<?php if ( apply_filters( 'woocommerce_enable_order_notes_field', 'yes' === get_option( 'woocommerce_enable_order_comments', 'yes' ) ) ) : ?>

		<div class="woocommerce-additional-fields__field-wrapper">
			<?php foreach ( $checkout->get_checkout_fields( 'order' ) as $key => $field ) : ?>
				<?php woocommerce_form_field( $key, $field, $checkout->get_value( $key ) ); ?>
			<?php endforeach; ?>
		</div>

	<?php endif; ?>

	<?php do_action( 'woocommerce_after_order_notes', $checkout ); ?>
</div>


