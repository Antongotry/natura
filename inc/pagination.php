<?php

/**
 * Кастомизация пагинации WooCommerce:
 * - Показываем всегда «окно» из 3 страниц (текущая + соседи).
 * - Убираем большие хвосты чисел, оставляя логику WooCommerce.
 */
function natura_customize_woocommerce_pagination_args( $args ) {
	// 3 видимых номера рядом: текущая страница + по одному соседу
	$args['mid_size'] = 1;
	// Не тянем длинные хвосты по краям
	$args['end_size'] = 0;
	return $args;
}
add_filter( 'woocommerce_pagination_args', 'natura_customize_woocommerce_pagination_args' );


