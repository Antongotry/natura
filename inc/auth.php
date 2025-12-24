<?php
/**
 * Natura Auth Functions
 * AJAX handlers для входу та реєстрації
 */

if (!defined('ABSPATH')) {
	exit;
}

/**
 * AJAX: Реєстрація користувача
 * Для реєстрації потрібні тільки email та пароль.
 * Ім'я користувач може заповнити пізніше в особистому кабінеті.
 */
add_action('wp_ajax_nopriv_natura_register', 'natura_ajax_register');
function natura_ajax_register() {
	// Перевірка nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'natura_auth_nonce')) {
		wp_send_json_error(['message' => 'Помилка безпеки. Оновіть сторінку.']);
		return;
	}

	$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';

	// Валідація
	if (empty($email) || !is_email($email)) {
		wp_send_json_error(['message' => 'Введіть коректний email']);
		return;
	}

	if (email_exists($email)) {
		wp_send_json_error(['message' => 'Цей email вже зареєстрований']);
		return;
	}

	if (strlen($password) < 6) {
		wp_send_json_error(['message' => 'Пароль має містити мінімум 6 символів']);
		return;
	}

	// Створення користувача
	$user_id = wp_create_user($email, $password, $email);

	if (is_wp_error($user_id)) {
		wp_send_json_error(['message' => 'Помилка при реєстрації. Спробуйте пізніше.']);
		return;
	}

	// Встановлюємо роль customer для WooCommerce
	$user = new WP_User($user_id);
	$user->set_role('customer');

	// Авторизуємо користувача
	wp_set_current_user($user_id);
	wp_set_auth_cookie($user_id, true);

	wp_send_json_success([
		'message' => 'Реєстрація успішна!',
		'redirect' => wc_get_account_endpoint_url('dashboard') ?: home_url('/my-account/'),
	]);
}

/**
 * AJAX: Вхід користувача
 */
add_action('wp_ajax_nopriv_natura_login', 'natura_ajax_login');
function natura_ajax_login() {
	// Перевірка nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'natura_auth_nonce')) {
		wp_send_json_error(['message' => 'Помилка безпеки. Оновіть сторінку.']);
		return;
	}

	$email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
	$password = isset($_POST['password']) ? $_POST['password'] : '';
	$remember = isset($_POST['remember']) && $_POST['remember'] === 'true';

	// Валідація
	if (empty($email)) {
		wp_send_json_error(['message' => 'Введіть email']);
		return;
	}

	if (empty($password)) {
		wp_send_json_error(['message' => 'Введіть пароль']);
		return;
	}

	// Спроба входу
	$user = wp_authenticate($email, $password);

	if (is_wp_error($user)) {
		wp_send_json_error(['message' => 'Невірний email або пароль']);
		return;
	}

	// Авторизуємо користувача
	wp_set_current_user($user->ID);
	wp_set_auth_cookie($user->ID, $remember);

	wp_send_json_success([
		'message' => 'Вхід успішний!',
		'redirect' => wc_get_account_endpoint_url('dashboard') ?: home_url('/my-account/'),
	]);
}

/**
 * AJAX: Soft registration (create account from guest order on thank you page)
 * Security: requires both order_id and order_key.
 */
add_action( 'wp_ajax_nopriv_natura_create_account_from_order', 'natura_ajax_create_account_from_order' );
add_action( 'wp_ajax_natura_create_account_from_order', 'natura_ajax_create_account_from_order' );
function natura_ajax_create_account_from_order() {
	// Nonce
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'natura_soft_account_nonce' ) ) {
		wp_send_json_error( array( 'message' => 'Помилка безпеки. Оновіть сторінку.' ), 403 );
		return;
	}

	if ( ! class_exists( 'WooCommerce' ) || ! function_exists( 'wc_get_order' ) ) {
		wp_send_json_error( array( 'message' => 'WooCommerce недоступний.' ), 500 );
		return;
	}

	// Already logged in — just send to orders
	if ( is_user_logged_in() ) {
		$redirect = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/' );
		wp_send_json_success(
			array(
				'message'  => 'Ви вже в акаунті.',
				'redirect' => $redirect,
			)
		);
		return;
	}

	$order_id  = isset( $_POST['order_id'] ) ? absint( wp_unslash( $_POST['order_id'] ) ) : 0;
	$order_key = isset( $_POST['order_key'] ) ? sanitize_text_field( wp_unslash( $_POST['order_key'] ) ) : '';
	$password  = isset( $_POST['password'] ) ? (string) wp_unslash( $_POST['password'] ) : '';

	if ( $order_id <= 0 || empty( $order_key ) ) {
		wp_send_json_error( array( 'message' => 'Некоректні дані замовлення.' ), 400 );
		return;
	}

	if ( strlen( $password ) < 6 ) {
		wp_send_json_error( array( 'message' => 'Пароль має містити мінімум 6 символів.' ), 400 );
		return;
	}

	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		wp_send_json_error( array( 'message' => 'Замовлення не знайдено.' ), 404 );
		return;
	}

	// Validate order key
	$current_key = (string) $order->get_order_key();
	if ( empty( $current_key ) || ! hash_equals( $current_key, (string) $order_key ) ) {
		wp_send_json_error( array( 'message' => 'Невірний ключ замовлення.' ), 403 );
		return;
	}

	// Only for guest orders
	if ( (int) $order->get_customer_id() > 0 ) {
		wp_send_json_error( array( 'message' => 'Це замовлення вже привʼязане до акаунту.' ), 409 );
		return;
	}

	$email = (string) $order->get_billing_email();
	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => 'У замовленні немає коректного email.' ), 400 );
		return;
	}

	// If user exists — ask to login
	if ( email_exists( $email ) ) {
		$auth_url = function_exists( 'natura_get_auth_url' ) ? natura_get_auth_url( 'login' ) : wp_login_url();
		wp_send_json_error(
			array(
				'code'     => 'email_exists',
				'message'  => 'Цей email вже має акаунт. Будь ласка, увійдіть.',
				'auth_url' => $auth_url,
			),
			409
		);
		return;
	}

	$user_id = wp_create_user( $email, $password, $email );
	if ( is_wp_error( $user_id ) ) {
		wp_send_json_error( array( 'message' => 'Помилка при створенні акаунту. Спробуйте пізніше.' ), 500 );
		return;
	}

	$user = new WP_User( $user_id );
	$user->set_role( 'customer' );

	// Basic profile from order
	$first_name = method_exists( $order, 'get_billing_first_name' ) ? (string) $order->get_billing_first_name() : '';
	$last_name  = method_exists( $order, 'get_billing_last_name' ) ? (string) $order->get_billing_last_name() : '';
	if ( $first_name ) {
		update_user_meta( $user_id, 'first_name', sanitize_text_field( $first_name ) );
	}
	if ( $last_name ) {
		update_user_meta( $user_id, 'last_name', sanitize_text_field( $last_name ) );
	}

	// Link order to newly created user
	$order->set_customer_id( $user_id );
	$order->save();

	// Log in
	wp_set_current_user( $user_id );
	wp_set_auth_cookie( $user_id, true );

	$redirect = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : home_url( '/my-account/' );
	wp_send_json_success(
		array(
			'message'  => 'Кабінет створено. Перенаправляємо…',
			'redirect' => $redirect,
		)
	);
}

/**
 * AJAX: Вихід користувача
 */
add_action('wp_ajax_natura_logout', 'natura_ajax_logout');
function natura_ajax_logout() {
	wp_logout();
	wp_send_json_success([
		'message' => 'Ви вийшли з акаунту',
		'redirect' => home_url('/'),
	]);
}

/**
 * AJAX: Відновлення пароля
 */
add_action('wp_ajax_nopriv_natura_lost_password', 'natura_ajax_lost_password');
add_action('wp_ajax_natura_lost_password', 'natura_ajax_lost_password');
function natura_ajax_lost_password() {
	// Перевірка nonce
	if (!isset($_POST['nonce']) || !wp_verify_nonce($_POST['nonce'], 'lost_password')) {
		wp_send_json_error(['message' => 'Помилка безпеки. Оновіть сторінку.']);
		return;
	}

	$user_login = isset($_POST['user_login']) ? sanitize_text_field($_POST['user_login']) : '';

	if (empty($user_login)) {
		wp_send_json_error(['message' => 'Введіть email або логін']);
		return;
	}

	// Знаходимо користувача
	if (is_email($user_login)) {
		$user = get_user_by('email', $user_login);
	} else {
		$user = get_user_by('login', $user_login);
	}

	if (!$user) {
		wp_send_json_error(['message' => 'Користувача з таким email не знайдено']);
		return;
	}

	// Генеруємо ключ скидання пароля
	$key = get_password_reset_key($user);

	if (is_wp_error($key)) {
		wp_send_json_error(['message' => 'Не вдалося створити посилання для скидання пароля']);
		return;
	}

	// Формуємо посилання для скидання
	$reset_url = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user->user_login), 'login');

	// Відправляємо email
	$message = "Хтось запросив скидання пароля для наступного облікового запису:\n\n";
	$message .= sprintf("Сайт: %s\n", get_bloginfo('name'));
	$message .= sprintf("Логін: %s\n\n", $user->user_login);
	$message .= "Якщо це були не ви, просто проігноруйте цей лист.\n\n";
	$message .= "Щоб скинути пароль, перейдіть за посиланням:\n\n";
	$message .= $reset_url . "\n";

	$subject = sprintf('[%s] Скидання пароля', get_bloginfo('name'));

	$sent = wp_mail($user->user_email, $subject, $message);

	if (!$sent) {
		wp_send_json_error(['message' => 'Не вдалося відправити email. Спробуйте пізніше.']);
		return;
	}

	wp_send_json_success([
		'message' => 'Лист для відновлення паролю надіслано.'
	]);
}

/**
 * Редірект незалогінених користувачів зі сторінки акаунту на авторизацію
 */
add_action('template_redirect', 'natura_redirect_if_not_logged_in', 5);
function natura_redirect_if_not_logged_in() {
	// Перевіряємо чи це WooCommerce my-account сторінка
	if (function_exists('is_account_page') && is_account_page() && !is_user_logged_in()) {
		wp_redirect(natura_get_auth_url());
		exit;
	}
}

/**
 * Редірект залогінених користувачів зі сторінки авторизації в кабінет
 */
add_action('template_redirect', 'natura_redirect_if_logged_in', 5);
function natura_redirect_if_logged_in() {
	if (is_page('auth') && is_user_logged_in()) {
		wp_redirect(natura_get_account_url());
		exit;
	}
}

/**
 * Отримати URL сторінки авторизації
 */
function natura_get_auth_url($tab = 'login') {
	$auth_page = get_page_by_path('auth');
	if ($auth_page) {
		$url = get_permalink($auth_page->ID);
		if ($tab === 'register') {
			$url = add_query_arg('tab', 'register', $url);
		}
		return $url;
	}
	return wp_login_url();
}

/**
 * Отримати URL особистого кабінету
 */
function natura_get_account_url() {
	if (function_exists('wc_get_page_permalink')) {
		return wc_get_page_permalink('myaccount');
	}
	return home_url('/my-account/');
}

/**
 * Перенаправлення WooCommerce login URL на кастомну сторінку
 */
add_filter('woocommerce_login_redirect', 'natura_wc_login_redirect', 10, 2);
function natura_wc_login_redirect($redirect, $user) {
	return natura_get_account_url();
}

add_filter('woocommerce_registration_redirect', 'natura_wc_registration_redirect');
function natura_wc_registration_redirect($redirect) {
	return natura_get_account_url();
}

/**
 * После выхода из кабинета не возвращаем на /auth (вхід-реєстрація), а ведём на главную.
 * Это также исправляет выход через стандартную ссылку WooCommerce (customer-logout).
 */
add_filter( 'woocommerce_logout_redirect', 'natura_wc_logout_redirect', 10, 1 );
function natura_wc_logout_redirect( $redirect_to ) {
	return home_url( '/' );
}

add_filter( 'logout_redirect', 'natura_wp_logout_redirect', 10, 3 );
function natura_wp_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
	return home_url( '/' );
}

/**
 * Замінюємо WooCommerce login URL на нашу сторінку
 */
add_filter('woocommerce_get_myaccount_page_permalink', 'natura_check_myaccount_redirect');
function natura_check_myaccount_redirect($permalink) {
	return $permalink;
}

/**
 * Приховуємо стандартну форму WooCommerce для незалогінених
 */
add_action('woocommerce_before_customer_login_form', 'natura_redirect_wc_login');
function natura_redirect_wc_login() {
	// Не редиректимо якщо це сторінка відновлення паролю
	global $wp;
	if (isset($wp->query_vars['lost-password']) || strpos($_SERVER['REQUEST_URI'], 'lost-password') !== false) {
		return;
	}
	
	if (!is_user_logged_in()) {
		wp_redirect(natura_get_auth_url());
		exit;
	}
}

/**
 * Перенаправлення wp-login.php для не-адмінів на кастомну сторінку
 */
add_action('login_init', 'natura_redirect_wp_login');
function natura_redirect_wp_login() {
	// Пропускаємо якщо це logout, lostpassword або інші дії
	$action = isset($_REQUEST['action']) ? $_REQUEST['action'] : 'login';
	
	$allowed_actions = ['logout', 'lostpassword', 'rp', 'resetpass', 'postpass', 'register'];
	if (in_array($action, $allowed_actions)) {
		return;
	}
	
	// Пропускаємо POST запити (обробка форм)
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		return;
	}
	
	// Пропускаємо AJAX запити
	if (defined('DOING_AJAX') && DOING_AJAX) {
		return;
	}
	
	// Перевіряємо чи є interim-login (для адмінки)
	if (isset($_REQUEST['interim-login'])) {
		return;
	}
	
	// Перенаправляємо на кастомну сторінку авторизації
	$auth_url = natura_get_auth_url();
	if ($auth_url && strpos($auth_url, 'wp-login.php') === false) {
		wp_redirect($auth_url);
		exit;
	}
}

