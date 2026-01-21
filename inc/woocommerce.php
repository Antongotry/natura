<?php

function natura_add_woocommerce_support(): void {
	add_theme_support('woocommerce');
}
add_action('after_setup_theme', 'natura_add_woocommerce_support');

/**
 * Увеличиваем количество похожих товаров для бесконечной карусели
 */
function natura_related_products_args( $args ) {
	$args['posts_per_page'] = 20; // Показываем до 20 товаров для карусели
	return $args;
}
add_filter( 'woocommerce_output_related_products_args', 'natura_related_products_args' );

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
 * Убираем "Сторінка X" из хлебных крошек на пагинации (catalog page 2, 3, ...)
 */
add_filter( 'woocommerce_get_breadcrumb', 'natura_woocommerce_breadcrumb_remove_paged', 10, 2 );
function natura_woocommerce_breadcrumb_remove_paged( $crumbs, $breadcrumb ) {
	if ( is_paged() && ! empty( $crumbs ) ) {
		$last = end( $crumbs );
		// Woo добавляет "Page X" последним элементом без ссылки
		if ( is_array( $last ) && isset( $last[1] ) && '' === (string) $last[1] ) {
			array_pop( $crumbs );
		}
	}

	return $crumbs;
}

/**
 * Переопределяем шаблон страницы checkout
 */
function natura_override_checkout_page_template( $template ) {
	// Для основного checkout используем кастомный шаблон.
	// ВАЖНО: для order-received тоже используем кастомный шаблон, иначе если в странице checkout нет shortcode,
	// endpoint будет пустым (header/footer есть, а контента нет).
	if ( is_checkout() && ( ! is_wc_endpoint_url() || is_wc_endpoint_url( 'order-received' ) ) ) {
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
 * Вычисляем процент скидки для товара
 */
function natura_calculate_sale_percentage( $product ) {
	if ( ! $product || ! $product->is_on_sale() ) {
		return 0;
	}

	$regular_price = $product->get_regular_price();
	$sale_price = $product->get_sale_price();

	if ( empty( $regular_price ) || empty( $sale_price ) ) {
		return 0;
	}

	$regular_price = (float) $regular_price;
	$sale_price = (float) $sale_price;

	if ( $regular_price <= 0 ) {
		return 0;
	}

	$percentage = ( ( $regular_price - $sale_price ) / $regular_price ) * 100;
	return round( $percentage );
}

/**
 * Изменяем текст бейджа "Sale" на процент скидки (например, -10%, -20%)
 */
function natura_change_sale_badge_text( $text, $post, $product ) {
	if ( ! $product ) {
		$product = wc_get_product( $post );
	}

	if ( ! $product || ! $product->is_on_sale() ) {
		return $text;
	}

	$percentage = natura_calculate_sale_percentage( $product );
	
	if ( $percentage > 0 ) {
		return '<span class="onsale">-' . esc_html( $percentage ) . '%</span>';
	}

	return '<span class="onsale">Акція</span>';
}
add_filter( 'woocommerce_sale_flash', 'natura_change_sale_badge_text', 10, 3 );

/**
 * Змінюємо текст кнопки "Читати далі" на "Немає в наявності" для товарів outofstock
 */
function natura_outofstock_button_text( $text, $product ) {
	if ( $product && ! $product->is_in_stock() ) {
		return __( 'Немає в наявності', 'natura' );
	}
	return $text;
}
add_filter( 'woocommerce_product_add_to_cart_text', 'natura_outofstock_button_text', 10, 2 );
add_filter( 'woocommerce_loop_add_to_cart_link', 'natura_outofstock_button_class', 10, 2 );

/**
 * Додаємо клас до кнопки для товарів outofstock
 */
function natura_outofstock_button_class( $html, $product ) {
	if ( $product && ! $product->is_in_stock() ) {
		$html = str_replace( 'class="button', 'class="button button--outofstock', $html );
	}
	return $html;
}

/**
 * Обертка для бейджа "Акція" на странице товара
 */
function natura_wrap_single_product_sale_badge() {
	global $product;
	if ( $product && $product->is_on_sale() ) {
		echo '<div class="single-product__sale-badge-wrapper">';
		woocommerce_show_product_sale_flash();
		echo '</div>';
	}
}

/**
 * Убираем стандартный вывод бейджа и заменяем на наш с оберткой
 */
function natura_replace_single_product_sale_badge() {
	remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
	add_action( 'woocommerce_before_single_product_summary', 'natura_wrap_single_product_sale_badge', 10 );
}
add_action( 'wp', 'natura_replace_single_product_sale_badge' );

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
 * Добавляем поле процента скидки и чекбокс "Показать на странице акций" в секцию цены товара
 */
function natura_add_sales_page_checkbox() {
	global $post;
	
	$show_on_sales = get_post_meta( $post->ID, '_show_on_sales_page', true );
	$checked = ( 'yes' === $show_on_sales ) ? 'yes' : 'no';
	
	$sale_percentage = get_post_meta( $post->ID, '_sale_percentage', true );
	
	echo '<div class="options_group show_if_simple show_if_external show_if_variable">';
	
	// Поле для процента скидки
	woocommerce_wp_text_input(
		array(
			'id'          => '_sale_percentage',
			'label'       => __('Знижка у відсотках (%)', 'natura'),
			'placeholder' => __('Наприклад: 10 для 10%', 'natura'),
			'value'       => $sale_percentage ? esc_attr( $sale_percentage ) : '',
			'type'        => 'number',
			'custom_attributes' => array(
				'step' => '1',
				'min'  => '0',
				'max'  => '100',
			),
			'desc_tip'    => true,
			'description' => __('Введіть відсоток знижки (0-100). Ціна зі знижкою буде розрахована автоматично. Якщо вказано відсоток, поле "Ціна зі знижкою" буде заповнено автоматично.', 'natura'),
			'wrapper_class' => 'form-field _sale_percentage_field',
		)
	);
	
	woocommerce_wp_checkbox(
		array(
			'id'          => '_show_on_sales_page',
			'label'       => __('Показати на сторінці акцій', 'natura'),
			'value'       => $checked,
			'cbvalue'     => 'yes',
			'desc_tip'    => true,
			'description' => __('Відмітьте цей товар, щоб він відображався в розділі "Акційні пропозиції" на сторінці акцій', 'natura'),
		)
	);
	
	echo '</div>';
	?>
	<script type="text/javascript">
	jQuery(document).ready(function($) {
		var $regularPrice = $('#_regular_price');
		var $salePrice = $('#_sale_price');
		var $salePercentage = $('#_sale_percentage');
		
		// Функция для вычисления цены со скидкой
		function calculateSalePrice() {
			var regularPrice = parseFloat($regularPrice.val());
			var percentage = parseFloat($salePercentage.val());
			
			if (regularPrice && percentage && percentage > 0 && percentage <= 100) {
				var salePrice = regularPrice * (1 - percentage / 100);
				// Округляем до целого числа
				salePrice = Math.round(salePrice);
				$salePrice.val(salePrice);
			}
		}
		
		// Вычисляем цену при изменении процента или обычной цены
		$salePercentage.on('input change', function() {
			if ($(this).val()) {
				calculateSalePrice();
			}
		});
		
		$regularPrice.on('input change', function() {
			if ($salePercentage.val()) {
				calculateSalePrice();
			}
		});
		
		// Если пользователь вручную изменил цену со скидкой, очищаем процент
		$salePrice.on('input', function() {
			if ($(this).val() && $salePercentage.val()) {
				// Можно оставить процент или очистить - оставляем для удобства
			}
		});
	});
	</script>
	<?php
}
add_action('woocommerce_product_options_pricing', 'natura_add_sales_page_checkbox');

/**
 * Сохраняем кастомные поля товара
 */
function natura_save_product_unit_field($post_id) {
	// Unit
	if ( isset( $_POST['_product_unit'] ) ) {
		$product_unit = sanitize_text_field( wp_unslash( $_POST['_product_unit'] ) );
		update_post_meta( $post_id, '_product_unit', $product_unit );
	}

	// Назва для картки (каталог): сохраняем только если поле было отправлено (чтобы quick edit не затирал)
	if ( isset( $_POST['_product_card_title'] ) ) {
		$card_title_raw = wp_unslash( $_POST['_product_card_title'] );
		$card_title_raw = preg_replace( '/<br\s*\/?>/i', "\n", (string) $card_title_raw );
		$card_title     = sanitize_textarea_field( (string) $card_title_raw );

		if ( '' === trim( $card_title ) ) {
			delete_post_meta( $post_id, '_product_card_title' );
		} else {
			update_post_meta( $post_id, '_product_card_title', $card_title );
		}
	}

	// Показать на странице акций
	if ( isset( $_POST['_show_on_sales_page'] ) ) {
		update_post_meta( $post_id, '_show_on_sales_page', 'yes' );
	} else {
		delete_post_meta( $post_id, '_show_on_sales_page' );
	}

	// Сохраняем процент скидки
	if ( isset( $_POST['_sale_percentage'] ) ) {
		$sale_percentage = sanitize_text_field( wp_unslash( $_POST['_sale_percentage'] ) );
		$sale_percentage = absint( $sale_percentage );
		
		if ( $sale_percentage > 0 && $sale_percentage <= 100 ) {
			update_post_meta( $post_id, '_sale_percentage', $sale_percentage );
			
			// Если указан процент и есть обычная цена, вычисляем цену со скидкой
			if ( isset( $_POST['_regular_price'] ) && ! empty( $_POST['_regular_price'] ) ) {
				$regular_price = (float) sanitize_text_field( wp_unslash( $_POST['_regular_price'] ) );
				if ( $regular_price > 0 ) {
					$sale_price = $regular_price * ( 1 - $sale_percentage / 100 );
					$sale_price = round( $sale_price ); // Округляем до целого
					
					// Обновляем цену со скидкой только если она не была задана вручную
					// или если процент был изменен
					if ( ! isset( $_POST['_sale_price'] ) || empty( $_POST['_sale_price'] ) ) {
						update_post_meta( $post_id, '_sale_price', $sale_price );
					}
				}
			}
		} else {
			delete_post_meta( $post_id, '_sale_percentage' );
		}
	} else {
		delete_post_meta( $post_id, '_sale_percentage' );
	}
}
add_action('woocommerce_process_product_meta', 'natura_save_product_unit_field');

/**
 * Поле "Назва для картки (каталог)" прямо под основным названием товара (post_title)
 * Чтобы переносы в названии не влияли на корзину/checkout/страницу товара.
 */
function natura_render_product_card_title_under_main_title( $post ) {
	if ( ! $post || 'product' !== $post->post_type ) {
		return;
	}

	$value = get_post_meta( $post->ID, '_product_card_title', true );
	?>
	<div class="natura-product-card-title-field" style="margin: 12px 0 0;">
		<label for="_product_card_title" style="display:block; font-weight:600; margin: 0 0 4px;">
			<?php echo esc_html__( 'Назва для картки (каталог)', 'natura' ); ?>
		</label>
		<textarea
			id="_product_card_title"
			name="_product_card_title"
			rows="2"
			class="widefat"
			placeholder="<?php echo esc_attr__( 'Опціонально. Можна зробити перенос (Enter)', 'natura' ); ?>"
		><?php echo esc_textarea( $value ); ?></textarea>
		<p class="description" style="margin: 6px 0 0;">
			<?php echo esc_html__( 'Використовується лише в каталозі/каруселях товарів. Не впливає на кошик/checkout/сторінку товару.', 'natura' ); ?>
		</p>
	</div>
	<?php
}
add_action( 'edit_form_after_title', 'natura_render_product_card_title_under_main_title' );

/**
 * Кастомизация полей чекаута - лейблы и плейсхолдеры
 */
function natura_customize_checkout_fields($fields) {
	// Добавляем поле "Назва закладу/компанії" (необязательное, сверху)
	$fields['billing']['billing_company'] = array(
		'label' => __('Назва закладу/компанії', 'natura'),
		'required' => false,
		'class' => array('form-row-wide'),
		'type' => 'text',
		'priority' => 5,
	);
	
	// Billing fields - оставляем только 3 поля: имя, телефон, email
	if (isset($fields['billing']['billing_first_name'])) {
		$fields['billing']['billing_first_name']['label'] = __('Ваше ім\'я та прізвище', 'natura');
		$fields['billing']['billing_first_name']['placeholder'] = 'Олександр Степаненко';
		$fields['billing']['billing_first_name']['class'] = array('form-row-wide');
		$fields['billing']['billing_first_name']['priority'] = 10;
	}
	
	// Скрываем last_name, так как имя и фамилия в одном поле
	if (isset($fields['billing']['billing_last_name'])) {
		$fields['billing']['billing_last_name']['required'] = false;
		$fields['billing']['billing_last_name']['class'] = array('form-row-wide', 'hidden');
	}
	
	if (isset($fields['billing']['billing_phone'])) {
		$fields['billing']['billing_phone']['label'] = __('Номер телефону', 'natura');
		$fields['billing']['billing_phone']['placeholder'] = '+38 (093) 200 22 11';
		$fields['billing']['billing_phone']['required'] = true;
		$fields['billing']['billing_phone']['priority'] = 20;
	}
	
	if (isset($fields['billing']['billing_email'])) {
		$fields['billing']['billing_email']['label'] = __('Email', 'natura');
		$fields['billing']['billing_email']['placeholder'] = 'zakaz@naturamarket.kiev.ua';
		$fields['billing']['billing_email']['priority'] = 30;
	}
	
	// Убираем все остальные billing поля
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
	
	// Убеждаемся, что все shipping поля обязательные
	if (isset($fields['shipping']['shipping_city'])) {
		$fields['shipping']['shipping_city']['required'] = true;
	}
	if (isset($fields['shipping']['shipping_address_1'])) {
		$fields['shipping']['shipping_address_1']['required'] = true;
	}
	
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
		$fields['order']['order_comments']['placeholder'] = __('Нотатки до вашого замовлення', 'natura');
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
	
	// Сортируем billing поля по priority
	if (isset($fields['billing'])) {
		uasort($fields['billing'], function($a, $b) {
			$priority_a = isset($a['priority']) ? $a['priority'] : 50;
			$priority_b = isset($b['priority']) ? $b['priority'] : 50;
			return $priority_a - $priority_b;
		});
	}
	
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
 * Зберігаємо кастомні поля доставки через WooCommerce Order object
 */
function natura_save_custom_checkout_fields($order_id) {
	$order = wc_get_order($order_id);
	if (!$order) {
		return;
	}
	
	// Shipping fields - зберігаємо через WooCommerce методи
	if (!empty($_POST['shipping_city'])) {
		$order->set_shipping_city(sanitize_text_field($_POST['shipping_city']));
	}
	
	if (!empty($_POST['shipping_address_1'])) {
		$order->set_shipping_address_1(sanitize_text_field($_POST['shipping_address_1']));
	}
	
	if (!empty($_POST['shipping_address_2'])) {
		$order->set_shipping_address_2(sanitize_text_field($_POST['shipping_address_2']));
	}
	
	// Кастомні поля доставки - через meta
	if (!empty($_POST['shipping_delivery_date'])) {
		$order->update_meta_data('_shipping_delivery_date', sanitize_text_field($_POST['shipping_delivery_date']));
	}
	
	if (!empty($_POST['shipping_delivery_time'])) {
		$order->update_meta_data('_shipping_delivery_time', sanitize_text_field($_POST['shipping_delivery_time']));
	}
	
	if (!empty($_POST['shipping_packaging'])) {
		$order->update_meta_data('_shipping_packaging', sanitize_text_field($_POST['shipping_packaging']));
	}
	
	// Billing fields
	if (!empty($_POST['billing_company'])) {
		$order->set_billing_company(sanitize_text_field($_POST['billing_company']));
	}
	
	// Додаємо SKU (артикул) до кожного товару в замовленні
	foreach ($order->get_items() as $item_id => $item) {
		$product = $item->get_product();
		if ($product) {
			$sku = $product->get_sku();
			if ($sku) {
				// Зберігаємо SKU в метаданих товару замовлення
				wc_add_order_item_meta($item_id, '_product_sku', $sku);
			}
		}
	}
	
	// Зберігаємо всі зміни
	$order->save();
}
add_action('woocommerce_checkout_update_order_meta', 'natura_save_custom_checkout_fields', 20);

/**
 * Додаємо SKU до назви товару в email-повідомленнях та інших місцях
 */
function natura_add_sku_to_order_item_name($name, $item) {
	$product = $item->get_product();
	if ($product) {
		$sku = $product->get_sku();
		if ($sku) {
			$name .= ' <small style="color: #666;">(Артикул: ' . esc_html($sku) . ')</small>';
		}
	}
	return $name;
}
add_filter('woocommerce_order_item_name', 'natura_add_sku_to_order_item_name', 10, 2);

/**
 * Додаємо SKU до метаданих товару в email-повідомленнях
 */
function natura_add_sku_to_order_item_meta($formatted_meta, $item) {
	$product = $item->get_product();
	if ($product) {
		$sku = $product->get_sku();
		if ($sku) {
			$formatted_meta['_product_sku'] = array(
				'label' => 'Артикул',
				'value' => $sku,
			);
		}
	}
	return $formatted_meta;
}
add_filter('woocommerce_order_item_get_formatted_meta_data', 'natura_add_sku_to_order_item_meta', 10, 2);

/**
 * Замінюємо стандартні billing поля в адмінці замовлення на наші
 */
function natura_admin_billing_fields($fields) {
	return array(
		'first_name' => array(
			'label' => "Ім'я та прізвище",
			'show'  => true,
		),
		'company' => array(
			'label' => 'Назва закладу/компанії',
			'show'  => true,
		),
		'phone' => array(
			'label' => 'Телефон',
			'show'  => true,
		),
		'email' => array(
			'label' => 'Email',
			'show'  => true,
		),
	);
}
add_filter('woocommerce_admin_billing_fields', 'natura_admin_billing_fields');

/**
 * Замінюємо стандартні shipping поля в адмінці замовлення на наші
 */
function natura_admin_shipping_fields($fields) {
	return array(
		'city' => array(
			'label' => 'Місто',
			'show'  => true,
		),
		'address_1' => array(
			'label' => 'Адреса',
			'show'  => true,
		),
		'address_2' => array(
			'label' => "Під'їзд / Поверх / Квартира",
			'show'  => true,
		),
	);
}
add_filter('woocommerce_admin_shipping_fields', 'natura_admin_shipping_fields');

/**
 * Відображення кастомних полів доставки в адмінці замовлення
 */
function natura_display_delivery_fields_in_admin_order($order) {
	$delivery_date = $order->get_meta('_shipping_delivery_date', true);
	$delivery_time = $order->get_meta('_shipping_delivery_time', true);
	$packaging = $order->get_meta('_shipping_packaging', true);
	
	// Час доставки - значення з чекауту
	$time_labels = array(
		'09:00-12:00' => '09:00 - 12:00',
		'12:00-15:00' => '12:00 - 15:00',
		'15:00-18:00' => '15:00 - 18:00',
		'18:00-21:00' => '18:00 - 21:00',
	);
	
	// Упакування - значення з чекауту
	$packaging_labels = array(
		'standard' => 'Стандартна упаковка',
		'gift' => 'Подарункова упаковка',
	);
	
	echo '<div class="natura-delivery-details" style="clear: both; margin-top: 20px; padding: 15px; background: #e8f5e9; border-radius: 8px; border-left: 4px solid #4CAF50;">';
	echo '<h3 style="margin: 0 0 12px 0; font-size: 14px; font-weight: 600; color: #2E7D32;">📦 Деталі доставки</h3>';
	
	if ($delivery_date) {
		$date_formatted = date_i18n('j F Y', strtotime($delivery_date));
		echo '<p style="margin: 6px 0;"><strong>Дата доставки:</strong> ' . esc_html($date_formatted) . '</p>';
	}
	
	if ($delivery_time) {
		$time_display = isset($time_labels[$delivery_time]) ? $time_labels[$delivery_time] : $delivery_time;
		echo '<p style="margin: 6px 0;"><strong>Час доставки:</strong> ' . esc_html($time_display) . '</p>';
	}
	
	if ($packaging) {
		$packaging_display = isset($packaging_labels[$packaging]) ? $packaging_labels[$packaging] : $packaging;
		echo '<p style="margin: 6px 0;"><strong>Упакування:</strong> ' . esc_html($packaging_display) . '</p>';
	}
	
	echo '</div>';
}
add_action('woocommerce_admin_order_data_after_shipping_address', 'natura_display_delivery_fields_in_admin_order');

/**
 * Змінюємо заголовки секцій в адмінці замовлення
 */
function natura_admin_order_section_titles() {
	?>
	<style>
		#order_data .order_data_column h3:first-child { display: none; }
		#order_data .order_data_column:nth-child(2) h3 { content: "Контактні дані"; }
		#order_data .order_data_column:nth-child(3) h3 { content: "Адреса доставки"; }
	</style>
	<?php
}
add_action('woocommerce_admin_order_data_after_order_details', 'natura_admin_order_section_titles');

/**
 * Додаємо кастомні поля в email замовлення
 */
function natura_add_custom_fields_to_order_email($order, $sent_to_admin, $plain_text, $email) {
	$shipping_city = $order->get_shipping_city();
	$shipping_address_1 = $order->get_shipping_address_1();
	$shipping_address_2 = $order->get_shipping_address_2();
	$delivery_date = $order->get_meta('_shipping_delivery_date', true);
	$delivery_time = $order->get_meta('_shipping_delivery_time', true);
	$packaging = $order->get_meta('_shipping_packaging', true);
	
	// Час доставки - значення з чекауту
	$time_labels = array(
		'09:00-12:00' => '09:00 - 12:00',
		'12:00-15:00' => '12:00 - 15:00',
		'15:00-18:00' => '15:00 - 18:00',
		'18:00-21:00' => '18:00 - 21:00',
	);
	
	// Упакування - значення з чекауту
	$packaging_labels = array(
		'standard' => 'Стандартна упаковка',
		'gift' => 'Подарункова упаковка',
	);
	
	if ($plain_text) {
		echo "\n\n=== ДЕТАЛІ ДОСТАВКИ ===\n";
		if ($shipping_city) echo "Місто: " . $shipping_city . "\n";
		if ($shipping_address_1) echo "Адреса: " . $shipping_address_1 . "\n";
		if ($shipping_address_2) echo "Під'їзд/Поверх/Кв: " . $shipping_address_2 . "\n";
		if ($delivery_date) echo "Дата: " . date_i18n('j F Y', strtotime($delivery_date)) . "\n";
		if ($delivery_time) echo "Час: " . (isset($time_labels[$delivery_time]) ? $time_labels[$delivery_time] : $delivery_time) . "\n";
		if ($packaging) echo "Упакування: " . (isset($packaging_labels[$packaging]) ? $packaging_labels[$packaging] : $packaging) . "\n";
	} else {
		echo '<h2 style="color: #4CAF50; font-size: 18px; margin-top: 30px;">📦 Деталі доставки</h2>';
		echo '<table cellspacing="0" cellpadding="6" style="width: 100%; border: 1px solid #e5e5e5; margin-bottom: 20px;">';
		
		if ($shipping_city) {
			echo '<tr><th style="text-align: left; border: 1px solid #e5e5e5; padding: 12px; background: #f8f8f8;">Місто</th><td style="text-align: left; border: 1px solid #e5e5e5; padding: 12px;">' . esc_html($shipping_city) . '</td></tr>';
		}
		if ($shipping_address_1) {
			echo '<tr><th style="text-align: left; border: 1px solid #e5e5e5; padding: 12px; background: #f8f8f8;">Адреса</th><td style="text-align: left; border: 1px solid #e5e5e5; padding: 12px;">' . esc_html($shipping_address_1) . '</td></tr>';
		}
		if ($shipping_address_2) {
			echo '<tr><th style="text-align: left; border: 1px solid #e5e5e5; padding: 12px; background: #f8f8f8;">Під\'їзд/Поверх/Кв</th><td style="text-align: left; border: 1px solid #e5e5e5; padding: 12px;">' . esc_html($shipping_address_2) . '</td></tr>';
		}
		if ($delivery_date) {
			echo '<tr><th style="text-align: left; border: 1px solid #e5e5e5; padding: 12px; background: #f8f8f8;">Дата доставки</th><td style="text-align: left; border: 1px solid #e5e5e5; padding: 12px;">' . esc_html(date_i18n('j F Y', strtotime($delivery_date))) . '</td></tr>';
		}
		if ($delivery_time) {
			$time_display = isset($time_labels[$delivery_time]) ? $time_labels[$delivery_time] : $delivery_time;
			echo '<tr><th style="text-align: left; border: 1px solid #e5e5e5; padding: 12px; background: #f8f8f8;">Час доставки</th><td style="text-align: left; border: 1px solid #e5e5e5; padding: 12px;">' . esc_html($time_display) . '</td></tr>';
		}
		if ($packaging) {
			$packaging_display = isset($packaging_labels[$packaging]) ? $packaging_labels[$packaging] : $packaging;
			echo '<tr><th style="text-align: left; border: 1px solid #e5e5e5; padding: 12px; background: #f8f8f8;">Упакування</th><td style="text-align: left; border: 1px solid #e5e5e5; padding: 12px;">' . esc_html($packaging_display) . '</td></tr>';
		}
		
		echo '</table>';
	}
}
add_action('woocommerce_email_after_order_table', 'natura_add_custom_fields_to_order_email', 10, 4);

/**
 * Валидация обязательных полей чекаута перед отправкой заказа
 */
add_action('woocommerce_checkout_process', 'natura_validate_checkout_required_fields');
function natura_validate_checkout_required_fields() {
	// Проверяем обязательные shipping поля
	if (empty($_POST['shipping_city'])) {
		wc_add_notice(__('Місто / Населений пункт є обов\'язковим полем.', 'natura'), 'error');
	}
	
	if (empty($_POST['shipping_address_1'])) {
		wc_add_notice(__('Адреса є обов\'язковим полем.', 'natura'), 'error');
	}
	
	if (empty($_POST['shipping_address_2'])) {
		wc_add_notice(__('Під\'їзд / Поверх / Квартира є обов\'язковим полем.', 'natura'), 'error');
	}
	
	if (empty($_POST['shipping_delivery_date'])) {
		wc_add_notice(__('День доставки є обов\'язковим полем.', 'natura'), 'error');
	}
	
	if (empty($_POST['shipping_delivery_time'])) {
		wc_add_notice(__('Час доставки є обов\'язковим полем.', 'natura'), 'error');
	}
	
	if (empty($_POST['shipping_packaging'])) {
		wc_add_notice(__('Вид упакування є обов\'язковим полем.', 'natura'), 'error');
	}
}

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

/**
 * Дробное количество для товаров в кг (0.1 = 100г)
 */
function natura_normalize_unit_label( $unit ): string {
	$unit = trim( (string) $unit );
	$unit = function_exists( 'mb_strtolower' ) ? mb_strtolower( $unit ) : strtolower( $unit );
	$unit = preg_replace( '/\s+/', '', $unit );
	return $unit;
}

function natura_is_kg_unit( $unit ): bool {
	$unit = natura_normalize_unit_label( $unit );
	return in_array( $unit, array( 'кг', 'kg' ), true );
}

/**
 * WooCommerce по умолчанию приводит количество к целому — разрешаем дробные значения
 * (нужно для 0.9 кг, 1.1 кг и т.д.)
 */
function natura_allow_decimal_stock_amount( $amount ) {
	if ( is_string( $amount ) ) {
		$amount = str_replace( ',', '.', $amount );
	}
	return (float) $amount;
}
add_filter( 'woocommerce_stock_amount', 'natura_allow_decimal_stock_amount', 10, 1 );

/**
 * Если где-то используется стандартный quantity input WooCommerce — тоже выставляем шаг 0.1 для "кг"
 */
function natura_quantity_input_args_for_kg( $args, $product ) {
	if ( ! $product || ! is_a( $product, 'WC_Product' ) ) {
		return $args;
	}

	$product_id = method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;
	$unit       = $product_id ? get_post_meta( $product_id, '_product_unit', true ) : '';
	if ( empty( $unit ) ) {
		$unit = get_option( 'woocommerce_weight_unit', 'kg' );
	}

	if ( natura_is_kg_unit( $unit ) ) {
		$args['step']      = 0.1;
		$args['min_value'] = 0.1;
		if ( empty( $args['input_value'] ) || (float) $args['input_value'] < 0.1 ) {
			$args['input_value'] = 1;
		}
	}

	return $args;
}
add_filter( 'woocommerce_quantity_input_args', 'natura_quantity_input_args_for_kg', 10, 2 );

/**
 * Разрешаем минимальное количество 0.1 для товаров в кг при добавлении в корзину
 */
function natura_validate_add_to_cart_quantity( $quantity, $product_id ) {
	$product = wc_get_product( $product_id );
	if ( ! $product ) {
		return $quantity;
	}

	$unit = get_post_meta( $product_id, '_product_unit', true );
	if ( empty( $unit ) ) {
		$unit = get_option( 'woocommerce_weight_unit', 'kg' );
	}

	if ( natura_is_kg_unit( $unit ) ) {
		// Для товаров в кг разрешаем минимальное количество 0.1
		$min_qty = 0.1;
		if ( $quantity < $min_qty ) {
			$quantity = $min_qty;
		}
		// Округляем до 1 знака после запятой
		$quantity = round( (float) $quantity, 1 );
	} else {
		// Для остальных товаров минимальное количество 1
		$quantity = max( 1, absint( $quantity ) );
	}

	return $quantity;
}
add_filter( 'woocommerce_add_to_cart_quantity', 'natura_validate_add_to_cart_quantity', 10, 2 );

/**
 * AJAX: Обновление количества товара в корзине (по cart_item_key) + возврат fragments
 */
function natura_update_cart_item_quantity_ajax() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'natura_cart_nonce' ) ) {
		wp_send_json_error(
			array( 'message' => 'Invalid nonce' ),
			403
		);
	}

	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error(
			array( 'message' => 'WooCommerce not available' ),
			500
		);
	}

	// Убеждаемся, что корзина загружена (особенно для admin-ajax)
	if ( function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	if ( ! WC()->cart ) {
		wp_send_json_error(
			array( 'message' => 'Cart not available' ),
			500
		);
	}

	$cart_item_key = isset( $_POST['cart_item_key'] ) ? sanitize_text_field( wp_unslash( $_POST['cart_item_key'] ) ) : '';
	$quantity_raw  = isset( $_POST['quantity'] ) ? wc_clean( wp_unslash( $_POST['quantity'] ) ) : '1';
	$quantity_raw  = str_replace( ',', '.', (string) $quantity_raw );
	$quantity      = is_numeric( $quantity_raw ) ? (float) $quantity_raw : 1.0;

	if ( empty( $cart_item_key ) ) {
		wp_send_json_error(
			array( 'message' => 'Missing cart_item_key' ),
			400
		);
	}

	$cart = WC()->cart->get_cart();
	if ( ! isset( $cart[ $cart_item_key ] ) ) {
		wp_send_json_error(
			array( 'message' => 'Cart item not found' ),
			404
		);
	}

	$product_id = isset( $cart[ $cart_item_key ]['product_id'] ) ? (int) $cart[ $cart_item_key ]['product_id'] : 0;
	$unit       = $product_id ? get_post_meta( $product_id, '_product_unit', true ) : '';
	if ( empty( $unit ) ) {
		$unit = get_option( 'woocommerce_weight_unit', 'kg' );
	}

	$is_kg_unit = natura_is_kg_unit( $unit );
	$min_qty    = $is_kg_unit ? 0.1 : 1;

	if ( $is_kg_unit ) {
		$quantity = round( $quantity, 1 );
	} else {
		$quantity = (float) absint( $quantity );
	}

	if ( $quantity < $min_qty ) {
		$quantity = $min_qty;
	}

	WC()->cart->set_quantity( $cart_item_key, $quantity, true );
	WC()->cart->calculate_totals();

	// Возвращаем стандартные WooCommerce fragments
	if ( class_exists( 'WC_AJAX' ) ) {
		WC_AJAX::get_refreshed_fragments();
	}

	wp_send_json_error(
		array( 'message' => 'Fragments not available' ),
		500
	);
}

add_action( 'wp_ajax_natura_update_cart_item_quantity', 'natura_update_cart_item_quantity_ajax' );
add_action( 'wp_ajax_nopriv_natura_update_cart_item_quantity', 'natura_update_cart_item_quantity_ajax' );

/**
 * AJAX: Очистка корзины
 */
function natura_clear_cart_ajax() {
	if ( ! class_exists( 'WooCommerce' ) ) {
		wp_send_json_error(
			array( 'message' => 'WooCommerce not available' ),
			500
		);
	}

	// Убеждаемся, что корзина загружена
	if ( function_exists( 'wc_load_cart' ) ) {
		wc_load_cart();
	}

	if ( ! WC()->cart ) {
		wp_send_json_error(
			array( 'message' => 'Cart not available' ),
			500
		);
	}

	// Очищаем корзину
	WC()->cart->empty_cart( true );
	WC()->cart->calculate_totals();

	// Возвращаем стандартные WooCommerce fragments
	if ( class_exists( 'WC_AJAX' ) ) {
		WC_AJAX::get_refreshed_fragments();
		exit;
	}

	// Fallback: возвращаем успешный ответ
	wp_send_json_success(
		array( 'message' => 'Cart cleared' )
	);
}
add_action( 'wp_ajax_natura_clear_cart', 'natura_clear_cart_ajax' );
add_action( 'wp_ajax_nopriv_natura_clear_cart', 'natura_clear_cart_ajax' );

/**
 * После успешного оформления заказа (страница order-received) — редирект в кабинет на "Мої замовлення"
 */
add_action( 'template_redirect', 'natura_redirect_order_received_to_account_orders', 20 );
function natura_redirect_order_received_to_account_orders() {
	if ( is_admin() || wp_doing_ajax() ) {
		return;
	}

	if ( ! function_exists( 'is_order_received_page' ) || ! is_order_received_page() ) {
		return;
	}

	// Редиректим только залогиненных — у гостя нет "кабинета"
	if ( ! is_user_logged_in() ) {
		return;
	}

	$orders_url = function_exists( 'wc_get_account_endpoint_url' ) ? wc_get_account_endpoint_url( 'orders' ) : '';
	if ( empty( $orders_url ) && function_exists( 'natura_get_account_url' ) ) {
		$orders_url = natura_get_account_url();
	}
	if ( empty( $orders_url ) ) {
		$orders_url = home_url( '/' );
	}

	// Добавляем параметр для показа попапа
	$orders_url = add_query_arg( 'order_placed', '1', $orders_url );

	wp_safe_redirect( $orders_url );
	exit;
}

/**
 * Статусы заказов:
 * - "Обробляється" (когда только поступил)
 * - "В дорозі" (когда едет)
 * - "Виконано" (когда получено)
 */
add_action( 'init', 'natura_register_order_status_in_transit' );
function natura_register_order_status_in_transit() {
	register_post_status(
		'wc-in-transit',
		array(
			'label'                     => _x( 'В дорозі', 'Order status', 'natura' ),
			'public'                    => true,
			'exclude_from_search'       => false,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			'label_count'               => _n_noop( 'В дорозі <span class="count">(%s)</span>', 'В дорозі <span class="count">(%s)</span>', 'natura' ),
		)
	);
}

add_filter( 'wc_order_statuses', 'natura_custom_order_statuses_labels' );
function natura_custom_order_statuses_labels( $order_statuses ) {
	$new_statuses = array();

	foreach ( $order_statuses as $status_key => $status_label ) {
		// Rename core statuses
		if ( 'wc-processing' === $status_key ) {
			$new_statuses[ $status_key ] = __( 'Обробляється', 'natura' );
			// Insert our custom status right after "processing"
			$new_statuses['wc-in-transit'] = __( 'В дорозі', 'natura' );
			continue;
		}

		if ( 'wc-completed' === $status_key ) {
			$new_statuses[ $status_key ] = __( 'Виконано', 'natura' );
			continue;
		}

		$new_statuses[ $status_key ] = $status_label;
	}

	// Fallback: if processing wasn't in the list for any reason, still ensure our custom status exists.
	if ( ! isset( $new_statuses['wc-in-transit'] ) ) {
		$new_statuses['wc-in-transit'] = __( 'В дорозі', 'natura' );
	}

	return $new_statuses;
}

/**
 * Новые заказы: чтобы "когда только надійшло" было "Обробляється".
 * Некоторые способы оплаты создают заказ в статусе pending/on-hold — приводим к processing.
 */
add_filter( 'woocommerce_new_order_status', 'natura_force_new_order_status_processing', 10, 2 );
function natura_force_new_order_status_processing( $status, $order_id ) {
	$normalized = str_replace( 'wc-', '', (string) $status );

	if ( in_array( $normalized, array( 'pending', 'on-hold' ), true ) ) {
		return 'processing';
	}

	return $normalized ?: $status;
}

/**
 * Формує JSON замовлення для відправки в зовнішні системи
 * Включає ID товара та SKU (артикул) для кожного товару
 */
function natura_format_order_json( $order_id ) {
	$order = wc_get_order( $order_id );
	if ( ! $order ) {
		return false;
	}

	// Основні дані замовлення
	$order_data = array(
		'order_number'   => $order->get_order_number(),
		'order_date'     => $order->get_date_created()->date( 'c' ), // ISO 8601 формат
		'order_total'    => number_format( (float) $order->get_total(), 2, '.', '' ),
		'payment_method' => $order->get_payment_method_title(),
		'customer'       => array(
			'full_name' => trim( $order->get_billing_first_name() . ' ' . $order->get_billing_last_name() ),
			'phone'     => $order->get_billing_phone(),
			'email'     => $order->get_billing_email(),
			'company'   => $order->get_billing_company() ?: '',
		),
		'shipping'        => array(
			'city'         => $order->get_shipping_city() ?: '',
			'address'      => $order->get_shipping_address_1() ?: '',
			'apartment'    => $order->get_shipping_address_2() ?: '',
			'delivery_date' => $order->get_meta( '_shipping_delivery_date' ) ?: '',
			'delivery_time' => $order->get_meta( '_shipping_delivery_time' ) ?: '',
			'packaging'    => $order->get_meta( '_shipping_packaging' ) ?: '',
		),
		'items'           => array(),
		'comment'         => $order->get_customer_note() ?: '',
	);

	// Товари замовлення
	foreach ( $order->get_items() as $item_id => $item ) {
		$product = $item->get_product();
		if ( ! $product ) {
			continue;
		}

		$sku = $product->get_sku();
		$product_id = $product->get_id();
		$quantity = $item->get_quantity();
		$item_price = (float) $item->get_subtotal() / $quantity; // Ціна за одиницю
		$item_total = (float) $item->get_subtotal();

		$order_data['items'][] = array(
			'sku'      => $sku ?: '',
			'name'     => $item->get_name(),
			'quantity' => $quantity,
			'price'    => number_format( $item_price, 2, '.', '' ),
			'total'    => number_format( $item_total, 2, '.', '' ),
			'id'       => (string) $product_id, // ID товара як рядок
		);
	}

	return $order_data;
}

/**
 * Зберігає JSON замовлення в метаданих замовлення при його створенні
 */
function natura_save_order_json( $order_id ) {
	$order_data = natura_format_order_json( $order_id );
	if ( $order_data ) {
		$order = wc_get_order( $order_id );
		if ( $order ) {
			$order->update_meta_data( '_order_json_data', wp_json_encode( $order_data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT ) );
			$order->save();
		}
	}
}
add_action( 'woocommerce_checkout_order_processed', 'natura_save_order_json', 20, 1 );
add_action( 'woocommerce_new_order', 'natura_save_order_json', 20, 1 );

/**
 * Сортировка товаров на главной странице каталога: сначала овощи и фрукты
 */
add_action( 'woocommerce_product_query', 'natura_sort_products_by_priority_categories' );
function natura_sort_products_by_priority_categories( $query ) {
	// Применяем только на главной странице каталога (shop), не на страницах категорий
	if ( is_product_category() || is_product_tag() || ! is_shop() ) {
		return;
	}

	// Получаем ID категорий "Овочі" и "Фрукти"
	$priority_categories = array();
	$category_names = array( 'овочі', 'овощи', 'фрукти', 'фрукты' );
	
	foreach ( $category_names as $name ) {
		$term = get_term_by( 'name', $name, 'product_cat' );
		if ( $term && ! is_wp_error( $term ) ) {
			$priority_categories[] = $term->term_id;
		}
		// Также проверяем по slug
		$term_slug = get_term_by( 'slug', sanitize_title( $name ), 'product_cat' );
		if ( $term_slug && ! is_wp_error( $term_slug ) && ! in_array( $term_slug->term_id, $priority_categories, true ) ) {
			$priority_categories[] = $term_slug->term_id;
		}
	}

	if ( empty( $priority_categories ) ) {
		return;
	}

	// Получаем ID товаров из приоритетных категорий (овочі + фрукти)
	$priority_product_ids = get_posts( array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'fields'         => 'ids',
		'tax_query'      => array(
			array(
				'taxonomy' => 'product_cat',
				'field'    => 'term_id',
				'terms'    => $priority_categories,
				'operator' => 'IN',
			),
		),
	) );
	
	if ( empty( $priority_product_ids ) ) {
		return;
	}

	// Перемешиваем ID товаров из овощей и фруктов для случайного порядка
	shuffle( $priority_product_ids );

	// Добавляем фильтр для изменения порядка через posts_clauses
	add_filter( 'posts_clauses', function( $clauses, $wp_query ) use ( $priority_product_ids ) {
		global $wpdb;
		
		// Добавляем JOIN с postmeta для stock_status
		if ( strpos( $clauses['join'], 'stock_meta' ) === false ) {
			$clauses['join'] .= " LEFT JOIN {$wpdb->postmeta} AS stock_meta ON ({$wpdb->posts}.ID = stock_meta.post_id AND stock_meta.meta_key = '_stock_status')";
		}
		
		$orderby_parts = array();
		
		// Сначала сортировка по наличию: товары в наличии (instock) первыми, не в наличии (outofstock) в конце
		$orderby_parts[] = "CASE WHEN stock_meta.meta_value = 'outofstock' THEN 1 ELSE 0 END ASC";
		
		// Потом товары из приоритетных категорий (овочі + фрукти), но только среди товаров в наличии
		if ( ! empty( $priority_product_ids ) ) {
			$priority_ids_str = implode( ',', array_map( 'intval', $priority_product_ids ) );
			$orderby_parts[] = "FIELD({$wpdb->posts}.ID, {$priority_ids_str}) DESC";
		}
		
		// Дополнительная сортировка по menu_order
		$orderby_parts[] = "{$wpdb->posts}.menu_order ASC";
		
		$clauses['orderby'] = implode( ', ', $orderby_parts );
		
		return $clauses;
	}, 10, 2 );
}

/**
 * AJAX handler для "Повідомити про наявність"
 */
function natura_ajax_stock_notification() {
	if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'natura_stock_notification' ) ) {
		wp_send_json_error( array( 'message' => __( 'Помилка безпеки', 'natura' ) ) );
	}
	
	$email = sanitize_email( $_POST['email'] ?? '' );
	$product_id = intval( $_POST['product_id'] ?? 0 );
	
	if ( empty( $email ) || ! is_email( $email ) ) {
		wp_send_json_error( array( 'message' => __( 'Будь ласка, введіть коректний email', 'natura' ) ) );
	}
	
	if ( ! $product_id || ! wc_get_product( $product_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Товар не знайдено', 'natura' ) ) );
	}
	
	// Перевіряємо, чи вже є підписка
	$existing = get_post_meta( $product_id, '_stock_notification_emails', true );
	if ( ! is_array( $existing ) ) {
		$existing = array();
	}
	
	if ( in_array( $email, $existing, true ) ) {
		wp_send_json_error( array( 'message' => __( 'Ви вже підписані на сповіщення', 'natura' ) ) );
	}
	
	// Додаємо email
	$existing[] = $email;
	update_post_meta( $product_id, '_stock_notification_emails', $existing );
	
	wp_send_json_success( array( 'message' => __( 'Дякуємо! Ми повідомимо вас, коли товар з\'явиться в наявності', 'natura' ) ) );
}
add_action( 'wp_ajax_natura_stock_notification', 'natura_ajax_stock_notification' );
add_action( 'wp_ajax_nopriv_natura_stock_notification', 'natura_ajax_stock_notification' );

/**
 * Відправляє email сповіщення при появі товару в наявності
 */
function natura_send_stock_notifications( $product_id ) {
	$product = wc_get_product( $product_id );
	
	if ( ! $product || ! $product->is_in_stock() ) {
		return;
	}
	
	$emails = get_post_meta( $product_id, '_stock_notification_emails', true );
	
	if ( ! is_array( $emails ) || empty( $emails ) ) {
		return;
	}
	
	$product_name = $product->get_name();
	$product_url = $product->get_permalink();
	
	$subject = sprintf( __( 'Товар "%s" з\'явився в наявності!', 'natura' ), $product_name );
	
	$message = sprintf(
		__( 'Вітаємо!%1$sТовар "%2$s", на який ви підписалися, з\'явився в наявності.%1$sПерейти до товару: %3$s', 'natura' ),
		"\n\n",
		$product_name,
		$product_url
	);
	
	$headers = array( 'Content-Type: text/html; charset=UTF-8' );
	
	foreach ( $emails as $email ) {
		wp_mail( $email, $subject, nl2br( esc_html( $message ) ), $headers );
	}
	
	// Видаляємо підписки після відправки
	delete_post_meta( $product_id, '_stock_notification_emails' );
}
add_action( 'woocommerce_product_set_stock_status', 'natura_check_stock_notifications', 10, 2 );
add_action( 'woocommerce_variation_set_stock_status', 'natura_check_stock_notifications', 10, 2 );

function natura_check_stock_notifications( $product_id, $stock_status ) {
	if ( $stock_status === 'instock' ) {
		natura_send_stock_notifications( $product_id );
	}
}






