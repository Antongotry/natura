<?php

function natura_is_front_page(): bool {
	return is_front_page() || (is_home() && 'page' === get_option('show_on_front'));
}

/**
 * Отримати URL сторінки договору оферти
 */
function natura_get_offer_url() {
	$offer_page = get_page_by_path('offer');
	if ($offer_page) {
		return get_permalink($offer_page->ID);
	}
	// Якщо сторінка не знайдена за slug 'offer', шукаємо за template
	$pages = get_pages(array(
		'meta_key' => '_wp_page_template',
		'meta_value' => 'page-offer.php'
	));
	if (!empty($pages)) {
		return get_permalink($pages[0]->ID);
	}
	return home_url('/offer/');
}

/**
 * Отримати URL сторінки угоди користувача
 */
function natura_get_terms_url() {
	$terms_page = get_page_by_path('terms');
	if ($terms_page) {
		return get_permalink($terms_page->ID);
	}
	// Якщо сторінка не знайдена за slug 'terms', шукаємо за template
	$pages = get_pages(array(
		'meta_key' => '_wp_page_template',
		'meta_value' => 'page-terms.php'
	));
	if (!empty($pages)) {
		return get_permalink($pages[0]->ID);
	}
	return home_url('/terms/');
}








