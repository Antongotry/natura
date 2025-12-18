<?php
/**
 * Review Order
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<div class="checkout-order-review">
	<?php
	do_action( 'woocommerce_checkout_before_order_review' );
	?>

	<div class="checkout-order-review__items">
		<?php
		do_action( 'woocommerce_review_order_before_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_checkout_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				$product_unit = get_post_meta( $_product->get_id(), '_product_unit', true );
				if ( empty( $product_unit ) ) {
					$product_unit = 'шт';
				}
				?>
				<div class="checkout-order-review__item">
					<div class="checkout-order-review__item-image">
						<?php
						$thumbnail = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
						if ( ! $product_permalink ) {
							echo $thumbnail; // PHPCS: XSS ok.
						} else {
							printf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $thumbnail ); // PHPCS: XSS ok.
						}
						?>
					</div>
					<div class="checkout-order-review__item-details">
						<div class="checkout-order-review__item-name">
							<?php
							if ( ! $product_permalink ) {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' );
							} else {
								echo wp_kses_post( apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) );
							}
							?>
							<span class="checkout-order-review__item-unit">( <?php echo esc_html( $product_unit ); ?> )</span>
						</div>
						<div class="checkout-order-review__item-price">
							<?php
							$item_price = $_product->get_price() * $cart_item['quantity'];
							echo wc_price( $item_price );
							?>
						</div>
					</div>
				</div>
				<?php
			}
		}

		do_action( 'woocommerce_review_order_after_cart_contents' );
		?>
	</div>

	<?php do_action( 'woocommerce_review_order_before_order_total' ); ?>

	<?php if ( wc_coupons_enabled() ) : ?>
		<div class="checkout-order-review__coupon">
			<label for="coupon_code" class="checkout-order-review__coupon-label">
				<?php esc_html_e( 'Купон / Промокод', 'woocommerce' ); ?> <span class="required">*</span>
			</label>
			<div class="checkout-order-review__coupon-input-wrapper">
				<input type="text" name="coupon_code" class="checkout-order-review__coupon-input" id="coupon_code" value="" placeholder="<?php esc_attr_e( 'Введіть промокод', 'woocommerce' ); ?>" />
				<button type="submit" class="checkout-order-review__coupon-button" name="apply_coupon" value="<?php esc_attr_e( 'Застосувати', 'woocommerce' ); ?>">
					<?php esc_html_e( 'Застосувати', 'woocommerce' ); ?>
				</button>
			</div>
		</div>
	<?php endif; ?>

	<div class="checkout-order-review__totals">
		<div class="checkout-order-review__subtotal">
			<div class="checkout-order-review__subtotal-label"><?php esc_html_e( 'Проміжний підсумок', 'woocommerce' ); ?></div>
			<div class="checkout-order-review__subtotal-value"><?php wc_cart_totals_subtotal_html(); ?></div>
		</div>

		<?php do_action( 'woocommerce_review_order_after_subtotal' ); ?>

		<?php foreach ( WC()->cart->get_coupons() as $code => $coupon ) : ?>
			<div class="checkout-order-review__discount">
				<div class="checkout-order-review__discount-label">
					<?php wc_cart_totals_coupon_label( $coupon ); ?>
				</div>
				<div class="checkout-order-review__discount-value">
					<?php wc_cart_totals_coupon_html( $coupon ); ?>
				</div>
			</div>
		<?php endforeach; ?>

		<div class="checkout-order-review__total">
			<div class="checkout-order-review__total-label"><?php esc_html_e( 'Всього', 'woocommerce' ); ?>:</div>
			<div class="checkout-order-review__total-value"><?php wc_cart_totals_order_total_html(); ?></div>
		</div>
	</div>

	<?php do_action( 'woocommerce_review_order_after_order_total' ); ?>

	<!-- Кнопка подтверждения для мобильной версии -->
	<div class="checkout-order-review__mobile-submit">
		<button type="submit" class="checkout-submit-button" name="woocommerce_checkout_place_order" id="place_order_mobile" value="<?php esc_attr_e( 'Підтвердити замовлення', 'woocommerce' ); ?>" data-value="<?php esc_attr_e( 'Підтвердити замовлення', 'woocommerce' ); ?>">
			<?php esc_html_e( 'Підтвердити замовлення', 'woocommerce' ); ?>
		</button>
	</div>

	<?php
	do_action( 'woocommerce_checkout_after_order_review' );
	?>
</div>

