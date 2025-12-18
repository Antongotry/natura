<?php

function natura_add_woocommerce_support(): void {
	add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'natura_add_woocommerce_support');

/**
 * Убеждаемся, что WooCommerce скрипты загружены
 */
function natura_enqueue_woocommerce_scripts() {
	if (class_exists('WooCommerce')) {
		wp_enqueue_script('wc-cart-fragments');
		wp_enqueue_script('wc-add-to-cart');
	}
}
add_action('wp_enqueue_scripts', 'natura_enqueue_woocommerce_scripts', 20);

function natura_disable_wc_default_styles(array $styles): array {
	return array();
}
add_filter('woocommerce_enqueue_styles', 'natura_disable_wc_default_styles');

/**
 * Убираем стандартные обертки WooCommerce
 */
function natura_remove_wc_wrappers() {
	remove_action('woocommerce_before_main_content', 'woocommerce_output_content_wrapper', 10);
	remove_action('woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end', 10);
	
	// Убираем хлебные крошки из хука на странице каталога (они уже есть в баннере)
	if (is_shop() || is_product_category() || is_product_tag()) {
		remove_action('woocommerce_before_main_content', 'woocommerce_breadcrumb', 20);
	}
}
add_action('wp', 'natura_remove_wc_wrappers');

/**
 * Настройка хлебных крошек WooCommerce
 */
function natura_customize_woocommerce_breadcrumbs($args) {
	$args['wrap_before'] = '<nav class="woocommerce-breadcrumb" aria-label="' . esc_attr__('Хлібні крихти', 'natura') . '">';
	$args['wrap_after'] = '</nav>';
	$args['delimiter'] = ''; // Убираем стандартный разделитель, используем иконку
	$args['before'] = '';
	$args['after'] = '';
	$args['home'] = __('Головна', 'natura');
	return $args;
}
add_filter('woocommerce_breadcrumb_defaults', 'natura_customize_woocommerce_breadcrumbs');

/**
 * Переопределяем шаблон страницы checkout
 */
function natura_override_checkout_page_template( $template ) {
	if ( is_checkout() && ! is_wc_endpoint_url() ) {
		$custom_template = locate_template( array( 'woocommerce/checkout.php' ) );
		if ( $custom_template ) {
			return $custom_template;
		}
	}
	return $template;
}
add_filter( 'template_include', 'natura_override_checkout_page_template', 99 );

/**
 * Переопределяем шаблон review order
 */
function natura_override_checkout_review_order_template( $template, $template_name, $template_path ) {
	if ( 'checkout/review-order.php' === $template_name ) {
		$custom_template = locate_template( 'woocommerce/checkout/review-order.php' );
		if ( $custom_template ) {
			return $custom_template;
		}
	}
	return $template;
}
add_filter( 'woocommerce_locate_template', 'natura_override_checkout_review_order_template', 10, 3 );

/**
 * Убираем категорию и вкладки на странице товара
 */
function natura_remove_product_meta_and_tabs() {
	// Убираем категорию (meta)
	remove_action('woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40);
	// Убираем вкладки (описание и отзывы)
	remove_action('woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10);
}
add_action('wp', 'natura_remove_product_meta_and_tabs');

/**
 * Убираем заголовок связанных товаров
 */
function natura_remove_related_products_heading($heading) {
	return '';
}
add_filter('woocommerce_product_related_products_heading', 'natura_remove_related_products_heading');

/**
 * Убираем рейтинг из карточки товара
 */
function natura_remove_product_rating() {
	remove_action('woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5);
}
add_action('init', 'natura_remove_product_rating');

/**
 * Добавляем класс swiper-slide к товарам в связанных товарах
 */
function natura_add_swiper_slide_class_to_related($classes, $product) {
	if (wc_get_loop_prop('name') === 'related') {
		if (!in_array('swiper-slide', $classes, true)) {
			$classes[] = 'swiper-slide';
		}
	}
	return $classes;
}
add_filter('woocommerce_post_class', 'natura_add_swiper_slide_class_to_related', 10, 2);

/**
 * Добавляем поле единицы измерения в админку товара
 */
function natura_add_product_unit_field() {
	global $woocommerce, $post;
	
	echo '<div class="options_group">';
	
	woocommerce_wp_text_input(
		array(
			'id'          => '_product_unit',
			'label'       => __('Одиниця виміру', 'natura'),
			'placeholder' => __('кг, г, шт', 'natura'),
			'desc_tip'    => true,
			'description' => __('Вкажіть одиницю виміру для цього товару (наприклад: кг, г, шт)', 'natura'),
		)
	);
	
	echo '</div>';
}
add_action('woocommerce_product_options_general_product_data', 'natura_add_product_unit_field');

/**
 * Сохраняем поле единицы измерения
 */
function natura_save_product_unit_field($post_id) {
	$product_unit = isset($_POST['_product_unit']) ? sanitize_text_field($_POST['_product_unit']) : '';
	update_post_meta($post_id, '_product_unit', $product_unit);
}
add_action('woocommerce_process_product_meta', 'natura_save_product_unit_field');

/**
 * Кастомизация полей чекаута - лейблы и плейсхолдеры
 */
function natura_customize_checkout_fields($fields) {
	// Billing fields - оставляем только 3 поля: имя, телефон, email
	if (isset($fields['billing']['billing_first_name'])) {
		$fields['billing']['billing_first_name']['label'] = __('Ваше ім\'я та прізвище', 'natura');
		$fields['billing']['billing_first_name']['placeholder'] = 'Олександр Степаненко';
		$fields['billing']['billing_first_name']['class'] = array('form-row-wide');
	}
	
	// Скрываем last_name, так как имя и фамилия в одном поле
	if (isset($fields['billing']['billing_last_name'])) {
		$fields['billing']['billing_last_name']['required'] = false;
		$fields['billing']['billing_last_name']['class'] = array('form-row-wide', 'hidden');
	}
	
	if (isset($fields['billing']['billing_phone'])) {
		$fields['billing']['billing_phone']['label'] = __('Номер телефону', 'natura');
		$fields['billing']['billing_phone']['placeholder'] = '+38 (093) 200 22 11';
	}
	
	if (isset($fields['billing']['billing_email'])) {
		$fields['billing']['billing_email']['label'] = __('Email', 'natura');
		$fields['billing']['billing_email']['placeholder'] = 'zakaz@naturamarket.kiev.ua';
	}
	
	// Убираем все остальные billing поля
	unset($fields['billing']['billing_company']);
	unset($fields['billing']['billing_address_1']);
	unset($fields['billing']['billing_address_2']);
	unset($fields['billing']['billing_city']);
	unset($fields['billing']['billing_postcode']);
	unset($fields['billing']['billing_state']);
	unset($fields['billing']['billing_country']);
	
	// Shipping fields - настраиваем порядок и labels
	if (isset($fields['shipping']['shipping_city'])) {
		$fields['shipping']['shipping_city']['label'] = __('Місто / Населений пункт', 'natura');
		$fields['shipping']['shipping_city']['placeholder'] = 'Київ';
		$fields['shipping']['shipping_city']['priority'] = 10;
		$fields['shipping']['shipping_city']['class'] = array('form-row-wide');
	}
	
	if (isset($fields['shipping']['shipping_address_1'])) {
		$fields['shipping']['shipping_address_1']['label'] = __('Адреса', 'natura');
		$fields['shipping']['shipping_address_1']['placeholder'] = 'Каховська 60';
		$fields['shipping']['shipping_address_1']['priority'] = 20;
		$fields['shipping']['shipping_address_1']['class'] = array('form-row-wide');
	}
	
	// Під'їзд / Поверх / Квартира
	$fields['shipping']['shipping_address_2'] = array(
		'label' => __('Під\'їзд / Поверх / Квартира', 'natura'),
		'placeholder' => '19/1/192',
		'required' => true,
		'class' => array('form-row-wide'),
		'type' => 'text',
		'priority' => 25,
	);
	
	// День доставки
	$fields['shipping']['shipping_delivery_date'] = array(
		'label' => __('День доставки', 'natura'),
		'placeholder' => __('Виберіть дату', 'natura'),
		'required' => true,
		'class' => array('form-row-wide'),
		'type' => 'date',
		'priority' => 30,
	);
	
	// Час доставки
	$fields['shipping']['shipping_delivery_time'] = array(
		'label' => __('Час доставки', 'natura'),
		'placeholder' => __('Оберіть зручний час', 'natura'),
		'required' => true,
		'class' => array('form-row-wide'),
		'type' => 'select',
		'options' => array(
			'' => __('Оберіть зручний час', 'natura'),
			'09:00-12:00' => '09:00-12:00',
			'12:00-15:00' => '12:00-15:00',
			'15:00-18:00' => '15:00-18:00',
			'18:00-21:00' => '18:00-21:00',
		),
		'priority' => 35,
	);
	
	// Вид упакування
	$fields['shipping']['shipping_packaging'] = array(
		'label' => __('Вид упакування', 'natura'),
		'placeholder' => __('Оберіть вид упакування', 'natura'),
		'required' => true,
		'class' => array('form-row-wide'),
		'type' => 'select',
		'options' => array(
			'' => __('Оберіть вид упакування', 'natura'),
			'standard' => __('Стандартна', 'natura'),
			'gift' => __('Подарункова', 'natura'),
		),
		'priority' => 40,
	);
	
	// Комментарий - делаем однострочным полем
	if (isset($fields['order']['order_comments'])) {
		$fields['order']['order_comments']['label'] = __('Коментар', 'natura');
		$fields['order']['order_comments']['placeholder'] = __('Нотатки до вашого замовлення. Якщо маєте побажання або примітки по доставці - вкажіть.', 'natura');
		$fields['order']['order_comments']['required'] = false;
		$fields['order']['order_comments']['class'] = array('form-row-wide');
		$fields['order']['order_comments']['type'] = 'text';
	}
	
	// Убираем ненужные поля shipping (имя, фамилия и т.д.)
	unset($fields['shipping']['shipping_first_name']);
	unset($fields['shipping']['shipping_last_name']);
	unset($fields['shipping']['shipping_company']);
	unset($fields['shipping']['shipping_postcode']);
	unset($fields['shipping']['shipping_state']);
	unset($fields['shipping']['shipping_country']);
	
	// Сортируем shipping поля по priority
	if (isset($fields['shipping'])) {
		uasort($fields['shipping'], function($a, $b) {
			$priority_a = isset($a['priority']) ? $a['priority'] : 50;
			$priority_b = isset($b['priority']) ? $b['priority'] : 50;
			return $priority_a - $priority_b;
		});
	}
	
	return $fields;
}
add_filter('woocommerce_checkout_fields', 'natura_customize_checkout_fields');

/**
 * Сохраняем кастомные поля доставки
 */
function natura_save_custom_checkout_fields($order_id) {
	if (!empty($_POST['shipping_delivery_date'])) {
		update_post_meta($order_id, '_shipping_delivery_date', sanitize_text_field($_POST['shipping_delivery_date']));
	}
	
	if (!empty($_POST['shipping_delivery_time'])) {
		update_post_meta($order_id, '_shipping_delivery_time', sanitize_text_field($_POST['shipping_delivery_time']));
	}
	
	if (!empty($_POST['shipping_packaging'])) {
		update_post_meta($order_id, '_shipping_packaging', sanitize_text_field($_POST['shipping_packaging']));
	}
}
add_action('woocommerce_checkout_update_order_meta', 'natura_save_custom_checkout_fields');

/**
 * Убираем способы оплаты из правой колонки (review-order)
 * Они будут выводиться только в левой колонке
 */
function natura_remove_payment_from_review_order() {
	remove_action('woocommerce_checkout_order_review', 'woocommerce_checkout_payment', 20);
}
add_action('wp', 'natura_remove_payment_from_review_order');

/**
 * Принудительно включаем shipping поля, даже если доставка не требуется
 */
function natura_force_shipping_fields($needs_shipping) {
	return true;
}
add_filter('woocommerce_cart_needs_shipping_address', 'natura_force_shipping_fields');








