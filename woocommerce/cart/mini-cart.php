<?php
/**
 * Mini-cart
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

do_action( 'woocommerce_before_mini_cart' ); ?>

<?php if ( WC()->cart && ! WC()->cart->is_empty() ) : ?>

	<ul class="woocommerce-mini-cart cart_list product_list_widget <?php echo esc_attr( $args['list_class'] ); ?>">
		<?php
		do_action( 'woocommerce_before_mini_cart_contents' );

		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
			$_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
			$product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

			if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
				$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
				$thumbnail         = apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key );
				$product_price     = apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key );
				$product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
				$product_unit      = get_post_meta( $product_id, '_product_unit', true ) ?: 'шт';
				?>
				<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?>">
					<div class="mini-cart-item__image">
						<?php if ( empty( $product_permalink ) ) : ?>
							<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						<?php else : ?>
							<a href="<?php echo esc_url( $product_permalink ); ?>">
								<?php echo $thumbnail; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							</a>
						<?php endif; ?>
					</div>
					<div class="mini-cart-item__content">
						<div class="mini-cart-item__name" data-unit="<?php echo esc_attr( $product_unit ); ?>">
							<?php if ( empty( $product_permalink ) ) : ?>
								<?php echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
							<?php else : ?>
								<a href="<?php echo esc_url( $product_permalink ); ?>">
									<?php echo wp_kses_post( $product_name ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
								</a>
							<?php endif; ?>
						</div>
						<div class="mini-cart-item__quantity-wrapper">
							<button type="button" class="mini-cart-item__quantity-button mini-cart-item__quantity-button--minus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php esc_attr_e( 'Зменшити кількість', 'natura' ); ?>">−</button>
							<span class="mini-cart-item__quantity-value"><?php echo esc_html( $cart_item['quantity'] ); ?> <?php echo esc_html( $product_unit ); ?></span>
							<button type="button" class="mini-cart-item__quantity-button mini-cart-item__quantity-button--plus" data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>" data-product-id="<?php echo esc_attr( $product_id ); ?>" aria-label="<?php esc_attr_e( 'Збільшити кількість', 'natura' ); ?>">+</button>
						</div>
						<div class="mini-cart-item__price">
							<span class="mini-cart-item__price-label">Вартість товару : </span>
							<?php echo wp_kses_post( $product_price ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
						</div>
					</div>
					<a href="<?php echo esc_url( wc_get_cart_remove_url( $cart_item_key ) ); ?>" class="mini-cart-item__remove" data-product_id="<?php echo esc_attr( $product_id ); ?>" data-cart_item_key="<?php echo esc_attr( $cart_item_key ); ?>" aria-label="<?php echo esc_attr( sprintf( __( 'Видалити %s з кошика', 'natura' ), wp_strip_all_tags( $product_name ) ) ); ?>">
						<img
							class="mini-cart-item__remove-icon"
							src="<?php echo esc_url( 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/group-85.svg' ); ?>"
							alt="<?php esc_attr_e( 'Видалити товар з кошика', 'natura' ); ?>"
							loading="lazy"
						/>
					</a>
				</li>
				<?php
			}
		}

		do_action( 'woocommerce_mini_cart_contents' );
		?>
	</ul>

	<p class="woocommerce-mini-cart__total total">
		<?php
		$mini_cart_subtotal = WC()->cart ? WC()->cart->get_cart_subtotal() : '';
		?>
		<span class="mini-cart-total">
			<span class="mini-cart-total__label">
				<?php esc_html_e( 'Всього :', 'natura' ); ?>
			</span>
			<span class="mini-cart-total__price">
				<?php echo wp_kses_post( $mini_cart_subtotal ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>
			</span>
		</span>
	</p>

	<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>

	<p class="woocommerce-mini-cart__buttons buttons"><?php do_action( 'woocommerce_widget_shopping_cart_buttons' ); ?></p>

	<div class="mini-cart-delivery">
		<img 
			class="mini-cart-delivery__icon" 
			src="<?php echo esc_url( 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/delivery_truck_speed_24dp_303030_fill1_wght400_grad0_opsz24-1.svg' ); ?>" 
			alt="<?php esc_attr_e( 'Безкоштовна доставка', 'natura' ); ?>"
			loading="lazy"
		/>
		<span class="mini-cart-delivery__text">
			<?php esc_html_e( 'Замовляйте на 1500 грн і більше — і ми доставимо ваше замовлення безкоштовно', 'natura' ); ?>
		</span>
	</div>

	<?php do_action( 'woocommerce_widget_shopping_cart_after_buttons' ); ?>

<?php else : ?>

	<p class="woocommerce-mini-cart__empty-message"><?php esc_html_e( 'No products in the cart.', 'woocommerce' ); ?></p>

<?php endif; ?>

<?php do_action( 'woocommerce_after_mini_cart' ); ?>

