<?php

if (!defined('NATURA_THEME_VERSION')) {
	define('NATURA_THEME_VERSION', '0.1.0');
}

require_once get_template_directory() . '/inc/setup.php';
require_once get_template_directory() . '/inc/enqueue.php';
require_once get_template_directory() . '/inc/template-helpers.php';
require_once get_template_directory() . '/inc/category-cover.php';

if (class_exists('WooCommerce')) {
	require_once get_template_directory() . '/inc/woocommerce.php';
	require_once get_template_directory() . '/inc/pagination.php';
	require_once get_template_directory() . '/inc/sales-products.php';
	require_once get_template_directory() . '/inc/user-pricing.php';
}

require_once get_template_directory() . '/inc/auth.php';
require_once get_template_directory() . '/inc/forms.php';

// Одноразовий скрипт: товари з ціною 0 -> "Немає в наявності" (видаліть після використання)
if ( is_admin() && file_exists( get_template_directory() . '/set-zero-price-out-of-stock.php' ) ) {
	require_once get_template_directory() . '/set-zero-price-out-of-stock.php';
}

// Отключить админ-бар для всех пользователей
add_filter('show_admin_bar', '__return_false');

// AJAX обработчик для поиска товаров
add_action('wp_ajax_natura_product_search', 'natura_product_search');
add_action('wp_ajax_nopriv_natura_product_search', 'natura_product_search');

function natura_product_search() {
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'natura_search_nonce')) {
		wp_send_json_error(array('message' => 'Invalid nonce'));
		return;
	}
	
	$query = isset($_POST['query']) ? sanitize_text_field($_POST['query']) : '';
	
	if (strlen($query) < 2) {
		wp_send_json_error(array('message' => 'Query too short'));
		return;
	}
	
	$args = array(
		'post_type' => 'product',
		'post_status' => 'publish',
		's' => $query,
		'posts_per_page' => 10,
	);
	
	$products_query = new WP_Query($args);
	$products = array();
	
	if ($products_query->have_posts()) {
		while ($products_query->have_posts()) {
			$products_query->the_post();
			$product = wc_get_product(get_the_ID());
			
			if (!$product) {
				continue;
			}
			
			$image_id = $product->get_image_id();
			$image_url = $image_id ? wp_get_attachment_image_url($image_id, 'woocommerce_thumbnail') : wc_placeholder_img_src('woocommerce_thumbnail');
			
			$products[] = array(
				'id' => $product->get_id(),
				'title' => $product->get_name(),
				'unit' => get_post_meta($product->get_id(), '_product_unit', true) ?: 'шт',
				'price' => $product->get_price_html(),
				'image' => $image_url,
				'permalink' => $product->get_permalink(),
			);
		}
		wp_reset_postdata();
	}
	
	wp_send_json_success($products);
}

