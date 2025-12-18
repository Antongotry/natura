<?php
/**
 * Custom quantity input with minus/plus buttons
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

$min_value   = isset( $args['min_value'] ) ? $args['min_value'] : $product->get_min_purchase_quantity();
$max_value   = isset( $args['max_value'] ) ? $args['max_value'] : $product->get_max_purchase_quantity();
$step        = isset( $args['step'] ) ? $args['step'] : 1;
$input_value = isset( $args['input_value'] ) ? $args['input_value'] : $min_value;
$input_id    = isset( $args['input_id'] ) ? $args['input_id'] : uniqid( 'quantity_' );
$input_name  = isset( $args['input_name'] ) ? $args['input_name'] : 'quantity';

// Получаем единицу измерения
// Сначала проверяем кастомное поле товара
$product_unit = get_post_meta( $product->get_id(), '_product_unit', true );
if ( ! empty( $product_unit ) ) {
	$unit_label = $product_unit;
} else {
	// Если кастомного поля нет, используем единицу веса из настроек WooCommerce
	$weight_unit = get_option( 'woocommerce_weight_unit', 'kg' );
	$unit_label  = $weight_unit;
}

?>
<div class="quantity-wrapper">
	<button type="button" class="quantity-button quantity-button--minus" aria-label="<?php esc_attr_e( 'Зменшити кількість', 'natura' ); ?>">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/minus-bas.svg" alt="" class="quantity-button__icon quantity-button__icon--default">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/minus-hover.svg" alt="" class="quantity-button__icon quantity-button__icon--hover">
	</button>
	
	<div class="quantity-input-wrapper">
		<input
			type="number"
			id="<?php echo esc_attr( $input_id ); ?>"
			class="input-text qty text"
			name="<?php echo esc_attr( $input_name ); ?>"
			value="<?php echo esc_attr( $input_value ); ?>"
			aria-label="<?php esc_attr_e( 'Кількість товару', 'woocommerce' ); ?>"
			min="<?php echo esc_attr( $min_value ); ?>"
			<?php if ( 0 < $max_value ) : ?>
				max="<?php echo esc_attr( $max_value ); ?>"
			<?php endif; ?>
			step="<?php echo esc_attr( $step ); ?>"
			placeholder=""
			inputmode="numeric"
			autocomplete="off"
		/>
		<span class="quantity-unit"><?php echo esc_html( $unit_label ); ?></span>
	</div>
	
	<button type="button" class="quantity-button quantity-button--plus" aria-label="<?php esc_attr_e( 'Збільшити кількість', 'natura' ); ?>">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/plus-bas.svg" alt="" class="quantity-button__icon quantity-button__icon--default">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/plus-hover.svg" alt="" class="quantity-button__icon quantity-button__icon--hover">
	</button>
</div>

