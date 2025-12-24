<?php
/**
 * Single Product title with unit
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

global $product;

// Получаем единицу измерения
$product_unit = get_post_meta( $product->get_id(), '_product_unit', true );
if ( empty( $product_unit ) ) {
	// Если кастомного поля нет, используем единицу веса из настроек WooCommerce
	$weight_unit = get_option( 'woocommerce_weight_unit', 'kg' );
	$product_unit = $weight_unit;
}

?>
<h1 class="product_title entry-title">
	<?php the_title(); ?>
	<?php if ( ! empty( $product_unit ) ) : ?>
		<span class="product-title__unit">( <?php echo esc_html( $product_unit ); ?> )</span>
	<?php endif; ?>
</h1>












