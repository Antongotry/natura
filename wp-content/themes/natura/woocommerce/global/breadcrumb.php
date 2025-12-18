<?php
/**
 * Shop breadcrumb
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( ! empty( $breadcrumb ) ) {
	$icon_url = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/vector-12.svg';
	
	echo $wrap_before;
	
	foreach ( $breadcrumb as $key => $crumb ) {
		if ( $key > 0 ) {
			echo '<span class="woocommerce-breadcrumb__separator">';
			echo '<img src="' . esc_url( $icon_url ) . '" alt="" class="woocommerce-breadcrumb__icon">';
			echo '</span>';
		}
		
		echo $before;
		
		if ( ! empty( $crumb[1] ) && sizeof( $breadcrumb ) !== $key + 1 ) {
			echo '<a href="' . esc_url( $crumb[1] ) . '" class="woocommerce-breadcrumb__link">' . esc_html( $crumb[0] ) . '</a>';
		} else {
			echo '<span class="woocommerce-breadcrumb__current">' . esc_html( $crumb[0] ) . '</span>';
		}
		
		echo $after;
	}
	
	echo $wrap_after;
}

