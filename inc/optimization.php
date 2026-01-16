<?php
/**
 * Natura Optimization Module
 * 
 * Модуль оптимізації з панеллю управління в адмінці.
 * Кожну оптимізацію можна вмикати/вимикати окремо.
 * 
 * Для відкату: видаліть цей файл та рядок require з functions.php
 * Або просто вимкніть всі опції в адмінці.
 * 
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Отримати налаштування оптимізації
 */
function natura_get_optimization_settings() {
	$defaults = array(
		'lazy_loading'      => true,  // Lazy loading зображень
		'minify_html'       => false, // Мініфікація HTML (вимкнено за замовчуванням - може ламати)
		'conditional_js'    => true,  // Умовне завантаження JS
		'defer_css'         => false, // Відкладене завантаження CSS (вимкнено - може ламати)
		'preload_fonts'     => true,  // Preload шрифтів
		'remove_query_strings' => false, // Видалити ?ver= з URL (вимкнено - може ламати кеш)
	);
	
	$settings = get_option( 'natura_optimization', array() );
	return wp_parse_args( $settings, $defaults );
}

/**
 * Перевірка чи увімкнена оптимізація
 */
function natura_is_optimization_enabled( $key ) {
	$settings = natura_get_optimization_settings();
	return ! empty( $settings[ $key ] );
}

// =====================================================
// АДМІН-ПАНЕЛЬ
// =====================================================

/**
 * Додати сторінку налаштувань
 */
function natura_optimization_admin_menu() {
	add_menu_page(
		__( 'Оптимізація', 'natura' ),
		__( 'Оптимізація', 'natura' ),
		'manage_options',
		'natura-optimization',
		'natura_optimization_page',
		'dashicons-performance',
		28
	);
}
add_action( 'admin_menu', 'natura_optimization_admin_menu' );

/**
 * Сторінка налаштувань
 */
function natura_optimization_page() {
	// Зберегти налаштування
	if ( isset( $_POST['natura_save_optimization'] ) && check_admin_referer( 'natura_optimization_nonce' ) ) {
		$settings = array(
			'lazy_loading'         => ! empty( $_POST['lazy_loading'] ),
			'minify_html'          => ! empty( $_POST['minify_html'] ),
			'conditional_js'       => ! empty( $_POST['conditional_js'] ),
			'defer_css'            => ! empty( $_POST['defer_css'] ),
			'preload_fonts'        => ! empty( $_POST['preload_fonts'] ),
			'remove_query_strings' => ! empty( $_POST['remove_query_strings'] ),
		);
		update_option( 'natura_optimization', $settings );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Налаштування збережено!', 'natura' ) . '</p></div>';
	}
	
	$settings = natura_get_optimization_settings();
	?>
	<div class="wrap">
		<h1>🚀 <?php esc_html_e( 'Оптимізація сайту', 'natura' ); ?></h1>
		
		<div style="background: #fff; padding: 20px; border: 1px solid #ccc; border-radius: 8px; max-width: 800px; margin-top: 20px;">
			<p style="background: #e7f3ff; padding: 15px; border-radius: 4px; border-left: 4px solid #2196F3;">
				💡 <strong>Порада:</strong> Увімкніть опції по одній та перевіряйте сайт після кожної зміни. Якщо щось зламалося — просто вимкніть цю опцію.
			</p>
			
			<form method="post">
				<?php wp_nonce_field( 'natura_optimization_nonce' ); ?>
				
				<table class="form-table">
					<!-- Lazy Loading -->
					<tr>
						<th scope="row">
							<label for="lazy_loading">
								<?php esc_html_e( 'Lazy Loading зображень', 'natura' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="lazy_loading" id="lazy_loading" value="1" <?php checked( $settings['lazy_loading'] ); ?> />
								<?php esc_html_e( 'Увімкнути', 'natura' ); ?>
							</label>
							<p class="description" style="color: #4CAF50;">
								✅ <?php esc_html_e( 'Безпечно. Відкладене завантаження зображень. Прискорює першу відрисовку.', 'natura' ); ?>
							</p>
						</td>
					</tr>
					
					<!-- Conditional JS -->
					<tr>
						<th scope="row">
							<label for="conditional_js">
								<?php esc_html_e( 'Умовне завантаження JS', 'natura' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="conditional_js" id="conditional_js" value="1" <?php checked( $settings['conditional_js'] ); ?> />
								<?php esc_html_e( 'Увімкнути', 'natura' ); ?>
							</label>
							<p class="description" style="color: #4CAF50;">
								✅ <?php esc_html_e( 'Безпечно. Swiper/GSAP/Lenis тільки на потрібних сторінках. Економить ~150KB.', 'natura' ); ?>
							</p>
						</td>
					</tr>
					
					<!-- Preload Fonts -->
					<tr>
						<th scope="row">
							<label for="preload_fonts">
								<?php esc_html_e( 'Preload шрифтів', 'natura' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="preload_fonts" id="preload_fonts" value="1" <?php checked( $settings['preload_fonts'] ); ?> />
								<?php esc_html_e( 'Увімкнути', 'natura' ); ?>
							</label>
							<p class="description" style="color: #4CAF50;">
								✅ <?php esc_html_e( 'Безпечно. Швидше завантаження шрифтів.', 'natura' ); ?>
							</p>
						</td>
					</tr>
					
					<!-- Minify HTML -->
					<tr>
						<th scope="row">
							<label for="minify_html">
								<?php esc_html_e( 'Мініфікація HTML', 'natura' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="minify_html" id="minify_html" value="1" <?php checked( $settings['minify_html'] ); ?> />
								<?php esc_html_e( 'Увімкнути', 'natura' ); ?>
							</label>
							<p class="description" style="color: #FF9800;">
								⚠️ <?php esc_html_e( 'Обережно. Видаляє пробіли з HTML. Може ламати inline JS/CSS. Тестуйте!', 'natura' ); ?>
							</p>
						</td>
					</tr>
					
					<!-- Defer CSS -->
					<tr>
						<th scope="row">
							<label for="defer_css">
								<?php esc_html_e( 'Відкладене CSS', 'natura' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="defer_css" id="defer_css" value="1" <?php checked( $settings['defer_css'] ); ?> />
								<?php esc_html_e( 'Увімкнути', 'natura' ); ?>
							</label>
							<p class="description" style="color: #FF9800;">
								⚠️ <?php esc_html_e( 'Обережно. Відкладає завантаження CSS. Може бути FOUC (миготіння). Тестуйте!', 'natura' ); ?>
							</p>
						</td>
					</tr>
					
					<!-- Remove Query Strings -->
					<tr>
						<th scope="row">
							<label for="remove_query_strings">
								<?php esc_html_e( 'Видалити ?ver=', 'natura' ); ?>
							</label>
						</th>
						<td>
							<label>
								<input type="checkbox" name="remove_query_strings" id="remove_query_strings" value="1" <?php checked( $settings['remove_query_strings'] ); ?> />
								<?php esc_html_e( 'Увімкнути', 'natura' ); ?>
							</label>
							<p class="description" style="color: #FF9800;">
								⚠️ <?php esc_html_e( 'Обережно. Покращує кешування CDN, але може ламати cache-busting.', 'natura' ); ?>
							</p>
						</td>
					</tr>
				</table>
				
				<p class="submit">
					<button type="submit" name="natura_save_optimization" class="button button-primary button-large">
						💾 <?php esc_html_e( 'Зберегти налаштування', 'natura' ); ?>
					</button>
				</p>
			</form>
			
			<hr style="margin: 30px 0;">
			
			<h3>🔄 <?php esc_html_e( 'Як відкотити', 'natura' ); ?></h3>
			<ol>
				<li><?php esc_html_e( 'Вимкніть всі чекбокси вище та збережіть', 'natura' ); ?></li>
				<li><?php esc_html_e( 'Або видаліть файл inc/optimization.php', 'natura' ); ?></li>
				<li><?php esc_html_e( 'Або виконайте: git revert HEAD', 'natura' ); ?></li>
			</ol>
		</div>
	</div>
	<?php
}

// =====================================================
// ОПТИМІЗАЦІЇ
// =====================================================

/**
 * 1. LAZY LOADING зображень
 */
function natura_add_lazy_loading_to_images( $attr, $attachment, $size ) {
	if ( ! natura_is_optimization_enabled( 'lazy_loading' ) ) {
		return $attr;
	}
	
	// Не додавати lazy loading для логотипів та критичних зображень
	if ( is_admin() ) {
		return $attr;
	}
	
	// Додати loading="lazy" якщо ще немає
	if ( ! isset( $attr['loading'] ) ) {
		$attr['loading'] = 'lazy';
	}
	
	return $attr;
}
add_filter( 'wp_get_attachment_image_attributes', 'natura_add_lazy_loading_to_images', 10, 3 );

/**
 * Lazy loading для зображень в контенті
 */
function natura_lazy_loading_content_images( $content ) {
	if ( ! natura_is_optimization_enabled( 'lazy_loading' ) ) {
		return $content;
	}
	
	if ( is_admin() || empty( $content ) ) {
		return $content;
	}
	
	// Додати loading="lazy" до img без цього атрибута
	$content = preg_replace(
		'/<img((?!loading=)[^>]*)>/i',
		'<img$1 loading="lazy">',
		$content
	);
	
	return $content;
}
add_filter( 'the_content', 'natura_lazy_loading_content_images', 999 );
add_filter( 'post_thumbnail_html', 'natura_lazy_loading_content_images', 999 );

/**
 * 2. PRELOAD ШРИФТІВ
 */
function natura_preload_fonts() {
	if ( ! natura_is_optimization_enabled( 'preload_fonts' ) ) {
		return;
	}
	
	// Preconnect до Google Fonts
	echo '<link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>' . "\n";
	echo '<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>' . "\n";
	
	// Preconnect до CDN бібліотек
	echo '<link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>' . "\n";
}
add_action( 'wp_head', 'natura_preload_fonts', 1 );

/**
 * 3. МІНІФІКАЦІЯ HTML (обережно!)
 */
function natura_start_html_minify() {
	if ( ! natura_is_optimization_enabled( 'minify_html' ) ) {
		return;
	}
	
	// Не мініфікувати в адмінці, для AJAX, REST API
	if ( is_admin() || wp_doing_ajax() || ( defined( 'REST_REQUEST' ) && REST_REQUEST ) ) {
		return;
	}
	
	ob_start( 'natura_minify_html_callback' );
}
add_action( 'template_redirect', 'natura_start_html_minify', 1 );

function natura_minify_html_callback( $html ) {
	if ( empty( $html ) ) {
		return $html;
	}
	
	// Зберігаємо pre, script, style, textarea
	$protected = array();
	$html = preg_replace_callback(
		'/<(pre|script|style|textarea)[^>]*>.*?<\/\1>/is',
		function( $matches ) use ( &$protected ) {
			$placeholder = '<!--PROTECTED' . count( $protected ) . '-->';
			$protected[] = $matches[0];
			return $placeholder;
		},
		$html
	);
	
	// Видаляємо HTML коментарі (крім IE умовних)
	$html = preg_replace( '/<!--(?!\[if).*?-->/s', '', $html );
	
	// Видаляємо зайві пробіли
	$html = preg_replace( '/\s+/', ' ', $html );
	$html = preg_replace( '/>\s+</', '><', $html );
	
	// Повертаємо protected елементи
	foreach ( $protected as $i => $content ) {
		$html = str_replace( '<!--PROTECTED' . $i . '-->', $content, $html );
	}
	
	return trim( $html );
}

/**
 * 4. DEFER CSS (відкладене завантаження)
 */
function natura_defer_css( $tag, $handle, $href ) {
	if ( ! natura_is_optimization_enabled( 'defer_css' ) ) {
		return $tag;
	}
	
	// Не defer для критичних стилів
	$critical_handles = array( 'natura-style', 'natura-main' );
	if ( in_array( $handle, $critical_handles, true ) ) {
		return $tag;
	}
	
	// Не defer для адмінки
	if ( is_admin() ) {
		return $tag;
	}
	
	// Defer через media="print" + onload
	if ( strpos( $tag, 'media=' ) === false ) {
		$tag = str_replace(
			"rel='stylesheet'",
			"rel='stylesheet' media='print' onload=\"this.media='all'\"",
			$tag
		);
	}
	
	return $tag;
}
add_filter( 'style_loader_tag', 'natura_defer_css', 10, 3 );

/**
 * 5. ВИДАЛИТИ QUERY STRINGS (?ver=)
 */
function natura_remove_query_strings( $src ) {
	if ( ! natura_is_optimization_enabled( 'remove_query_strings' ) ) {
		return $src;
	}
	
	if ( strpos( $src, '?ver=' ) !== false || strpos( $src, '&ver=' ) !== false ) {
		$src = remove_query_arg( 'ver', $src );
	}
	
	return $src;
}
add_filter( 'style_loader_src', 'natura_remove_query_strings', 10, 1 );
add_filter( 'script_loader_src', 'natura_remove_query_strings', 10, 1 );

/**
 * 6. УМОВНЕ ЗАВАНТАЖЕННЯ JS (перевірка для enqueue.php)
 */
function natura_should_load_swiper() {
	if ( ! natura_is_optimization_enabled( 'conditional_js' ) ) {
		return true; // Завантажувати завжди якщо оптимізація вимкнена
	}
	
	// Swiper потрібен на: головна, каталог, сторінка товару, категорії
	return is_front_page() || is_shop() || is_product() || is_product_category() || is_product_tag();
}

function natura_should_load_gsap() {
	if ( ! natura_is_optimization_enabled( 'conditional_js' ) ) {
		return true;
	}
	
	// GSAP потрібен для анімацій на багатьох сторінках
	// Вимикаємо тільки для checkout/cart
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return false;
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return false;
	}
	
	return true; // Завантажувати на всіх інших сторінках
}

function natura_should_load_lenis() {
	if ( ! natura_is_optimization_enabled( 'conditional_js' ) ) {
		return true;
	}
	
	// Lenis потрібен на всіх сторінках для плавного скролу
	// Вимикаємо тільки для checkout/cart щоб не заважати формам
	if ( function_exists( 'is_checkout' ) && is_checkout() ) {
		return false;
	}
	if ( function_exists( 'is_cart' ) && is_cart() ) {
		return false;
	}
	
	return true; // Завантажувати на всіх інших сторінках
}
