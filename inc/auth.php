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
	$shop_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/catalog');
	wp_send_json_success([
		'message' => 'Ви вийшли з акаунту',
		'redirect' => $shop_url,
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
	if ( ! empty( $redirect_to ) ) {
		return $redirect_to;
	}

	return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/catalog');
}

add_filter( 'logout_redirect', 'natura_wp_logout_redirect', 10, 3 );
function natura_wp_logout_redirect( $redirect_to, $requested_redirect_to, $user ) {
	// Respect explicit redirect_to if provided (e.g., wp_logout_url($url))
	if ( ! empty( $requested_redirect_to ) ) {
		return $requested_redirect_to;
	}
	if ( ! empty( $redirect_to ) ) {
		return $redirect_to;
	}

	return function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/catalog');
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

/**
 * Google OAuth 2.0 Integration
 */

/**
 * Получить Google Client ID (из wp-config.php или опций)
 */
function natura_get_google_client_id() {
	if (defined('NATURA_GOOGLE_CLIENT_ID')) {
		return NATURA_GOOGLE_CLIENT_ID;
	}
	return get_option('natura_google_client_id', '');
}

/**
 * Получить Google Client Secret (из wp-config.php или опций)
 */
function natura_get_google_client_secret() {
	if (defined('NATURA_GOOGLE_CLIENT_SECRET')) {
		return NATURA_GOOGLE_CLIENT_SECRET;
	}
	return get_option('natura_google_client_secret', '');
}

/**
 * AJAX: Получить Google OAuth URL для авторизации
 */
add_action('wp_ajax_nopriv_natura_google_oauth_url', 'natura_ajax_google_oauth_url');
add_action('wp_ajax_natura_google_oauth_url', 'natura_ajax_google_oauth_url');
function natura_ajax_google_oauth_url() {
	$client_id = natura_get_google_client_id();
	
	if (empty($client_id)) {
		wp_send_json_error(['message' => 'Google OAuth не настроен. Обратитесь к администратору.']);
		return;
	}
	
	// Генерируем state для защиты от CSRF
	$state = wp_generate_password(32, false);
	set_transient('natura_google_oauth_state_' . $state, time(), 600); // 10 минут
	
	// Определяем тип действия (login или register)
	$action = isset($_POST['action_type']) ? sanitize_text_field($_POST['action_type']) : 'login';
	set_transient('natura_google_oauth_action_' . $state, $action, 600);
	
	// Формируем URL для авторизации
	$redirect_uri = admin_url('admin-ajax.php?action=natura_google_oauth_callback');
	$scope = 'openid email profile';
	
	$auth_url = add_query_arg([
		'client_id' => $client_id,
		'redirect_uri' => urlencode($redirect_uri),
		'response_type' => 'code',
		'scope' => urlencode($scope),
		'state' => $state,
		'access_type' => 'offline',
		'prompt' => 'consent',
	], 'https://accounts.google.com/o/oauth2/v2/auth');
	
	wp_send_json_success([
		'auth_url' => $auth_url,
		'state' => $state,
	]);
}

/**
 * AJAX: Callback от Google OAuth
 */
add_action('wp_ajax_nopriv_natura_google_oauth_callback', 'natura_ajax_google_oauth_callback');
add_action('wp_ajax_natura_google_oauth_callback', 'natura_ajax_google_oauth_callback');
function natura_ajax_google_oauth_callback() {
	$code = isset($_GET['code']) ? sanitize_text_field($_GET['code']) : '';
	$state = isset($_GET['state']) ? sanitize_text_field($_GET['state']) : '';
	$error = isset($_GET['error']) ? sanitize_text_field($_GET['error']) : '';
	
	// Проверка на ошибку от Google
	if (!empty($error)) {
		$error_message = isset($_GET['error_description']) ? sanitize_text_field($_GET['error_description']) : 'Ошибка авторизации через Google';
		wp_redirect(add_query_arg('google_error', urlencode($error_message), natura_get_auth_url()));
		exit;
	}
	
	// Проверка state (защита от CSRF)
	if (empty($state) || !get_transient('natura_google_oauth_state_' . $state)) {
		wp_redirect(add_query_arg('google_error', urlencode('Неверный запрос. Попробуйте снова.'), natura_get_auth_url()));
		exit;
	}
	
	// Получаем тип действия
	$action_type = get_transient('natura_google_oauth_action_' . $state);
	delete_transient('natura_google_oauth_state_' . $state);
	delete_transient('natura_google_oauth_action_' . $state);
	
	$client_id = natura_get_google_client_id();
	$client_secret = natura_get_google_client_secret();
	
	if (empty($client_id) || empty($client_secret)) {
		wp_redirect(add_query_arg('google_error', urlencode('Google OAuth не настроен.'), natura_get_auth_url()));
		exit;
	}
	
	// Обмениваем код на access token
	$token_url = 'https://oauth2.googleapis.com/token';
	$redirect_uri = admin_url('admin-ajax.php?action=natura_google_oauth_callback');
	
	$response = wp_remote_post($token_url, [
		'body' => [
			'code' => $code,
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'redirect_uri' => $redirect_uri,
			'grant_type' => 'authorization_code',
		],
	]);
	
	if (is_wp_error($response)) {
		wp_redirect(add_query_arg('google_error', urlencode('Ошибка при получении токена.'), natura_get_auth_url()));
		exit;
	}
	
	$body = json_decode(wp_remote_retrieve_body($response), true);
	
	if (empty($body['access_token'])) {
		wp_redirect(add_query_arg('google_error', urlencode('Не удалось получить токен доступа.'), natura_get_auth_url()));
		exit;
	}
	
	$access_token = $body['access_token'];
	
	// Получаем информацию о пользователе
	$user_info_url = 'https://www.googleapis.com/oauth2/v2/userinfo';
	$user_response = wp_remote_get($user_info_url, [
		'headers' => [
			'Authorization' => 'Bearer ' . $access_token,
		],
	]);
	
	if (is_wp_error($user_response)) {
		wp_redirect(add_query_arg('google_error', urlencode('Ошибка при получении данных пользователя.'), natura_get_auth_url()));
		exit;
	}
	
	$user_data = json_decode(wp_remote_retrieve_body($user_response), true);
	
	if (empty($user_data['email'])) {
		wp_redirect(add_query_arg('google_error', urlencode('Не удалось получить email от Google.'), natura_get_auth_url()));
		exit;
	}
	
	$email = sanitize_email($user_data['email']);
	$first_name = isset($user_data['given_name']) ? sanitize_text_field($user_data['given_name']) : '';
	$last_name = isset($user_data['family_name']) ? sanitize_text_field($user_data['family_name']) : '';
	$google_id = isset($user_data['id']) ? sanitize_text_field($user_data['id']) : '';
	$avatar_url = isset($user_data['picture']) ? esc_url_raw($user_data['picture']) : '';
	
	// Проверяем, существует ли пользователь с таким email
	$user = get_user_by('email', $email);
	
	if ($user) {
		// Пользователь существует - логиним
		// Обновляем Google ID для связи
		if ($google_id) {
			update_user_meta($user->ID, 'natura_google_id', $google_id);
		}
		if ($avatar_url) {
			update_user_meta($user->ID, 'natura_google_avatar', $avatar_url);
		}
		
		wp_set_current_user($user->ID);
		wp_set_auth_cookie($user->ID, true);
		
		wp_redirect(natura_get_account_url());
		exit;
	} else {
		// Пользователь не существует
		if ($action_type === 'register') {
			// Регистрируем нового пользователя
			$username = $email; // Используем email как username
			$password = wp_generate_password(20, true, true); // Генерируем случайный пароль
			
			$user_id = wp_create_user($username, $password, $email);
			
			if (is_wp_error($user_id)) {
				wp_redirect(add_query_arg('google_error', urlencode('Ошибка при создании аккаунта.'), natura_get_auth_url('register')));
				exit;
			}
			
			// Устанавливаем роль customer для WooCommerce
			$new_user = new WP_User($user_id);
			$new_user->set_role('customer');
			
			// Сохраняем дополнительные данные
			if ($first_name) {
				update_user_meta($user_id, 'first_name', $first_name);
			}
			if ($last_name) {
				update_user_meta($user_id, 'last_name', $last_name);
			}
			if ($google_id) {
				update_user_meta($user_id, 'natura_google_id', $google_id);
			}
			if ($avatar_url) {
				update_user_meta($user_id, 'natura_google_avatar', $avatar_url);
			}
			
			// Логиним пользователя
			wp_set_current_user($user_id);
			wp_set_auth_cookie($user_id, true);
			
			wp_redirect(natura_get_account_url());
			exit;
		} else {
			// Пытаемся войти, но пользователя нет - предлагаем зарегистрироваться
			wp_redirect(add_query_arg('google_error', urlencode('Аккаунт с таким email не найден. Пожалуйста, зарегистрируйтесь.'), natura_get_auth_url('register')));
			exit;
		}
	}
}

/**
 * Добавить настройки Google OAuth в админку (опционально)
 */
add_action('admin_init', 'natura_google_oauth_settings');
function natura_google_oauth_settings() {
	// Регистрируем настройки только если не определены константы
	if (!defined('NATURA_GOOGLE_CLIENT_ID')) {
		register_setting('natura_settings', 'natura_google_client_id');
		register_setting('natura_settings', 'natura_google_client_secret');
	}
}

/**
 * Добавить страницу настроек в админку (опционально)
 */
add_action('admin_menu', 'natura_google_oauth_admin_menu');
function natura_google_oauth_admin_menu() {
	if (!defined('NATURA_GOOGLE_CLIENT_ID')) {
		add_options_page(
			'Настройки Google OAuth',
			'Google OAuth',
			'manage_options',
			'natura-google-oauth',
			'natura_google_oauth_settings_page'
		);
	}
}

function natura_google_oauth_settings_page() {
	if (!current_user_can('manage_options')) {
		return;
	}
	
	if (isset($_POST['submit'])) {
		check_admin_referer('natura_google_oauth_settings');
		update_option('natura_google_client_id', sanitize_text_field($_POST['natura_google_client_id']));
		update_option('natura_google_client_secret', sanitize_text_field($_POST['natura_google_client_secret']));
		echo '<div class="notice notice-success"><p>Настройки сохранены!</p></div>';
	}
	
	$client_id = get_option('natura_google_client_id', '');
	$client_secret = get_option('natura_google_client_secret', '');
	?>
	<div class="wrap">
		<h1>Настройки Google OAuth</h1>
		<form method="post" action="">
			<?php wp_nonce_field('natura_google_oauth_settings'); ?>
			<table class="form-table">
				<tr>
					<th scope="row"><label for="natura_google_client_id">Client ID</label></th>
					<td><input type="text" id="natura_google_client_id" name="natura_google_client_id" value="<?php echo esc_attr($client_id); ?>" class="regular-text" /></td>
				</tr>
				<tr>
					<th scope="row"><label for="natura_google_client_secret">Client Secret</label></th>
					<td><input type="password" id="natura_google_client_secret" name="natura_google_client_secret" value="<?php echo esc_attr($client_secret); ?>" class="regular-text" /></td>
				</tr>
			</table>
			<p class="submit">
				<input type="submit" name="submit" id="submit" class="button button-primary" value="Сохранить изменения">
			</p>
		</form>
		<p><strong>Примечание:</strong> Для большей безопасности рекомендуется добавить эти значения в <code>wp-config.php</code> как константы <code>NATURA_GOOGLE_CLIENT_ID</code> и <code>NATURA_GOOGLE_CLIENT_SECRET</code>.</p>
	</div>
	<?php
}

