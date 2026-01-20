<?php

function natura_register_assets(): void {
	$theme_uri = get_template_directory_uri();
	$theme_path = get_template_directory();
	
	// Get file modification time for cache busting
	$style_version = file_exists($theme_path . '/style.css') ? filemtime($theme_path . '/style.css') : NATURA_THEME_VERSION;
	$main_css_version = file_exists($theme_path . '/assets/css/main.css') ? filemtime($theme_path . '/assets/css/main.css') : NATURA_THEME_VERSION;
	$home_css_version = file_exists($theme_path . '/assets/css/pages/home.css') ? filemtime($theme_path . '/assets/css/pages/home.css') : NATURA_THEME_VERSION;
	$main_js_version = file_exists($theme_path . '/assets/js/main.js') ? filemtime($theme_path . '/assets/js/main.js') : NATURA_THEME_VERSION;
	
	wp_enqueue_style('natura-style', get_stylesheet_uri(), array(), $style_version);
	wp_enqueue_style('natura-main', $theme_uri . '/assets/css/main.css', array('natura-style'), $main_css_version);
	wp_enqueue_style('natura-home', $theme_uri . '/assets/css/pages/home.css', array('natura-main'), $home_css_version);
	
	// Auth & Account CSS
	if (is_page_template('page-auth.php') || is_page('auth')) {
		$auth_css_version = file_exists($theme_path . '/assets/css/pages/auth.css') ? filemtime($theme_path . '/assets/css/pages/auth.css') : NATURA_THEME_VERSION;
		wp_enqueue_style('natura-auth', $theme_uri . '/assets/css/pages/auth.css', array('natura-main'), $auth_css_version);
	}
	
	// Error 404 CSS
	if (is_404()) {
		$error_css_version = file_exists($theme_path . '/assets/css/pages/error.css') ? filemtime($theme_path . '/assets/css/pages/error.css') : NATURA_THEME_VERSION;
		wp_enqueue_style('natura-error', $theme_uri . '/assets/css/pages/error.css', array('natura-main'), $error_css_version);
	}
	if (function_exists('is_account_page') && is_account_page()) {
		$account_css_version = file_exists($theme_path . '/assets/css/pages/account.css') ? filemtime($theme_path . '/assets/css/pages/account.css') : NATURA_THEME_VERSION;
		wp_enqueue_style('natura-account', $theme_uri . '/assets/css/pages/account.css', array('natura-main'), $account_css_version);
	}
	
	// Offer Page CSS
	if (is_page_template('page-offer.php') || is_page('offer')) {
		$offer_css_version = file_exists($theme_path . '/assets/css/pages/offer.css') ? filemtime($theme_path . '/assets/css/pages/offer.css') : NATURA_THEME_VERSION;
		wp_enqueue_style('natura-offer', $theme_uri . '/assets/css/pages/offer.css', array('natura-main'), $offer_css_version);
	}
	
	// Умовне завантаження бібліотек (контролюється в Natura > Оптимізація)
	$load_swiper = ! function_exists( 'natura_should_load_swiper' ) || natura_should_load_swiper();
	$load_gsap   = ! function_exists( 'natura_should_load_gsap' ) || natura_should_load_gsap();
	$load_lenis  = ! function_exists( 'natura_should_load_lenis' ) || natura_should_load_lenis();
	
	// Lenis CSS & JS
	if ( $load_lenis ) {
		wp_enqueue_style('lenis', 'https://cdn.jsdelivr.net/npm/lenis@1.2.3/dist/lenis.css', array(), '1.2.3');
	wp_enqueue_script('lenis', 'https://cdn.jsdelivr.net/npm/lenis@1.2.3/dist/lenis.min.js', array(), '1.2.3', true);
	}
	
	// Swiper CSS & JS
	if ( $load_swiper ) {
		wp_enqueue_style('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css', array(), '11');
	wp_enqueue_script('swiper', 'https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js', array(), '11', true);
	}
	
	// GSAP JS
	if ( $load_gsap ) {
	wp_enqueue_script('gsap', 'https://cdn.jsdelivr.net/npm/gsap@3.12.5/dist/gsap.min.js', array(), '3.12.5', true);
	}
	
	// Main JS - залежності динамічні
	$main_deps = array( 'jquery' );
	if ( $load_swiper ) $main_deps[] = 'swiper';
	if ( $load_lenis ) $main_deps[] = 'lenis';
	if ( $load_gsap ) $main_deps[] = 'gsap';
	
	wp_enqueue_script('natura-main', $theme_uri . '/assets/js/main.js', $main_deps, $main_js_version, true);
	
	// Auth JS
	if (is_page_template('page-auth.php') || is_page('auth')) {
		$auth_js_version = file_exists($theme_path . '/assets/js/auth.js') ? filemtime($theme_path . '/assets/js/auth.js') : NATURA_THEME_VERSION;
		wp_enqueue_script('natura-auth', $theme_uri . '/assets/js/auth.js', array(), $auth_js_version, true);
		wp_localize_script('natura-auth', 'naturaAuth', array(
			'ajaxUrl' => admin_url('admin-ajax.php'),
			'shopUrl' => get_permalink(wc_get_page_id('shop')) ?: home_url('/'),
		));
	}
	
	// Локализация для AJAX поиска
	wp_localize_script('natura-main', 'naturaSearch', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('natura_search_nonce'),
	));

	// Локализация для AJAX обновления корзины (количество)
	wp_localize_script('natura-main', 'naturaCart', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('natura_cart_nonce'),
	));

	// Soft registration on thank you page (guest -> create account from order)
	wp_localize_script('natura-main', 'naturaSoftAccount', array(
		'ajax_url'  => admin_url('admin-ajax.php'),
		'nonce'     => wp_create_nonce('natura_soft_account_nonce'),
		'auth_url'  => function_exists('natura_get_auth_url') ? natura_get_auth_url('login') : wp_login_url(),
	));

	// Forms (collaboration and feedback)
	wp_localize_script('natura-main', 'naturaForms', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('natura_forms_nonce'),
	));

	// Stock notification
	wp_localize_script('natura-main', 'naturaStockNotification', array(
		'ajax_url' => admin_url('admin-ajax.php'),
		'nonce'    => wp_create_nonce('natura_stock_notification'),
	));
}
add_action('wp_enqueue_scripts', 'natura_register_assets');


