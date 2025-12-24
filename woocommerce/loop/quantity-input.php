<?php
/**
 * Custom quantity input for product loop cards
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! isset( $product ) ) {
	global $product;
}

if ( ! $product ) {
	return;
}

$min_value   = $product->get_min_purchase_quantity();
$max_value   = $product->get_max_purchase_quantity();
$step        = $product->get_purchase_quantity_step();
$input_value = isset( $args['input_value'] ) ? $args['input_value'] : 1;

// Get product unit
$product_unit = get_post_meta( $product->get_id(), '_product_unit', true );
if ( empty( $product_unit ) ) {
	$product_unit = get_option( 'woocommerce_weight_unit', 'кг' );
}

// Если единица измерения "кг"/"kg" — шаг 100г (0.1)
$unit_normalized = trim( (string) $product_unit );
$unit_normalized = function_exists( 'mb_strtolower' ) ? mb_strtolower( $unit_normalized ) : strtolower( $unit_normalized );
$unit_normalized = preg_replace( '/\s+/', '', $unit_normalized );
$is_kg_unit       = in_array( $unit_normalized, array( 'кг', 'kg' ), true );

if ( $is_kg_unit ) {
	$step      = 0.1;
	$min_value = 0.1;
	if ( empty( $input_value ) || (float) $input_value < 0.1 ) {
		$input_value = 1;
	}
}

$input_mode = $is_kg_unit ? 'decimal' : 'numeric';

$input_id = 'quantity_' . $product->get_id() . '_' . wp_unique_id();
?>
<div class="product-card__quantity-wrapper quantity-wrapper" data-product-id="<?php echo esc_attr( $product->get_id() ); ?>" style="display: none;">
	<button type="button" class="quantity-button quantity-button--minus" aria-label="<?php esc_attr_e( 'Зменшити кількість', 'natura' ); ?>">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/minus-bas.svg" alt="" class="quantity-button__icon quantity-button__icon--default">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/minus-hover.svg" alt="" class="quantity-button__icon quantity-button__icon--hover">
	</button>
	
	<div class="quantity-input-wrapper">
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="input-text qty text product-card__quantity-input"
			name="quantity"
			value="<?php echo esc_attr( $input_value ); ?>"
			aria-label="<?php esc_attr_e( 'Кількість товару', 'woocommerce' ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			<?php if ( 0 < $max_value ) : ?>
				max="<?php echo esc_attr( $max_value ); ?>"
			<?php endif; ?>
			step="<?php echo esc_attr( $step ); ?>"
			placeholder=""
			inputmode="<?php echo esc_attr( $input_mode ); ?>"
			autocomplete="off"
			data-product-id="<?php echo esc_attr( $product->get_id() ); ?>"
		/>
		<span class="quantity-unit"><?php echo esc_html( $product_unit ); ?></span>
	</div>
	
	<button type="button" class="quantity-button quantity-button--plus" aria-label="<?php esc_attr_e( 'Збільшити кількість', 'natura' ); ?>">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/plus-bas.svg" alt="" class="quantity-button__icon quantity-button__icon--default">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/plus-hover.svg" alt="" class="quantity-button__icon quantity-button__icon--hover">
	</button>
</div>

