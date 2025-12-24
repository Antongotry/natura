<?php
/**
 * The template for displaying product content within loops
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

global $product;

// Check if the product is a valid WooCommerce product and ensure its visibility before proceeding.
if ( ! is_a( $product, WC_Product::class ) || ! $product->is_visible() ) {
	return;
}

// Get product unit
$product_unit = get_post_meta( $product->get_id(), '_product_unit', true );
if ( empty( $product_unit ) ) {
	$product_unit = get_option( 'woocommerce_weight_unit', 'кг' ); // Fallback to WooCommerce weight unit
}

// Optional: custom title for product cards only (catalog/loops). Allows manual line breaks.
$product_card_title = get_post_meta( $product->get_id(), '_product_card_title', true );
?>
<li <?php wc_product_class( '', $product ); ?>>
	<div class="product-card">
		<div class="product-card__image-wrapper">
			<a href="<?php echo esc_url( get_permalink() ); ?>" class="woocommerce-LoopProduct-link woocommerce-loop-product__link">
				<?php echo $product->get_image( 'medium' ); ?>
			</a>
		</div>

		<?php if ( $product->is_on_sale() ) : ?>
			<div class="product-card__badge-wrapper">
				<?php woocommerce_show_product_loop_sale_flash(); ?>
			</div>
		<?php endif; ?>

		<div class="product-card__content">
			<div class="product-card__title-price-wrapper">
				<div class="product-card__title-wrapper">
					<a href="<?php echo esc_url( get_permalink() ); ?>" class="product-card__title-link">
						<span class="woocommerce-loop-product__title">
							<?php
							if ( ! empty( $product_card_title ) ) {
								// Newline (Enter) -> <br>, and escape everything else.
								echo nl2br( esc_html( $product_card_title ) );
							} else {
								echo esc_html( get_the_title() );
							}
							?>
							<?php if ( $product_unit ) : ?>
								<span class="product-card__unit">( <?php echo esc_html( $product_unit ); ?> )</span>
							<?php endif; ?>
						</span>
					</a>
				</div>

				<div class="product-card__price-wrapper">
					<?php woocommerce_template_loop_price(); ?>
				</div>
			</div>

			<?php if ( $product->get_short_description() ) : ?>
				<div class="product-card__description">
					<?php echo wp_kses_post( $product->get_short_description() ); ?>
				</div>
			<?php endif; ?>

			<div class="product-card__button-wrapper">
				<?php woocommerce_template_loop_add_to_cart(); ?>
				<?php
				// Выводим поле количества под кнопкой
				wc_get_template(
					'loop/quantity-input.php',
					array(
						'product'     => $product,
						'input_value' => 1,
					)
				);
				?>
			</div>
		</div>
	</div>
</li>



