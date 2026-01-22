<?php
/**
 * Home page content management (Trusted carousel).
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}

const NATURA_TRUSTED_CAROUSEL_OPTION = 'natura_trusted_carousel_items';

/**
 * Add admin menu page for home content management.
 */
function natura_home_content_admin_menu() {
	add_menu_page(
		__('Контент на головній', 'natura'),
		__('Контент на головній', 'natura'),
		'manage_options',
		'natura-home-content',
		'natura_home_content_page',
		'dashicons-admin-page',
		30
	);
}
add_action('admin_menu', 'natura_home_content_admin_menu');

/**
 * Render admin page for home content management.
 */
function natura_home_content_page() {
	if (! current_user_can('manage_options')) {
		return;
	}

	// Handle form submission
	if (isset($_POST['natura_trusted_save']) && check_admin_referer('natura_trusted_save', 'natura_trusted_nonce')) {
		$top_items = isset($_POST['trusted_top']) ? json_decode(stripslashes($_POST['trusted_top']), true) : array();
		$bottom_items = isset($_POST['trusted_bottom']) ? json_decode(stripslashes($_POST['trusted_bottom']), true) : array();

		// Validate and sanitize data
		$top_items = natura_sanitize_carousel_items($top_items);
		$bottom_items = natura_sanitize_carousel_items($bottom_items);

		update_option(NATURA_TRUSTED_CAROUSEL_OPTION, array(
			'top' => $top_items,
			'bottom' => $bottom_items,
		));

		echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Налаштування збережено!', 'natura') . '</p></div>';
	}

	// Handle sync from template
	if (isset($_POST['natura_trusted_sync']) && check_admin_referer('natura_trusted_sync', 'natura_trusted_sync_nonce')) {
		$synced_data = natura_sync_trusted_from_template();
		if ($synced_data) {
			update_option(NATURA_TRUSTED_CAROUSEL_OPTION, $synced_data);
			echo '<div class="notice notice-success is-dismissible"><p>' . esc_html__('Дані синхронізовано з шаблону!', 'natura') . '</p></div>';
		}
	}

	// Get current data
	$carousel_data = get_option(NATURA_TRUSTED_CAROUSEL_OPTION, array(
		'top' => array(),
		'bottom' => array(),
	));

	$top_items = isset($carousel_data['top']) ? $carousel_data['top'] : array();
	$bottom_items = isset($carousel_data['bottom']) ? $carousel_data['bottom'] : array();

	?>
	<div class="wrap">
		<h1><?php esc_html_e('Контент на головній', 'natura'); ?></h1>

		<div class="natura-home-content-tabs">
			<nav class="nav-tab-wrapper">
				<a href="#trusted-section" class="nav-tab nav-tab-active"><?php esc_html_e('Блок "Нам довіряють"', 'natura'); ?></a>
			</nav>
		</div>

		<div id="trusted-section" class="natura-tab-content">
			<h2><?php esc_html_e('Управління каруселлю "Нам довіряють"', 'natura'); ?></h2>
			
			<form method="post" action="" id="natura-trusted-form">
				<?php wp_nonce_field('natura_trusted_save', 'natura_trusted_nonce'); ?>

				<div class="natura-sync-section">
					<h3><?php esc_html_e('Синхронізація', 'natura'); ?></h3>
					<p class="description">
						<?php esc_html_e('Натисніть кнопку нижче, щоб синхронізувати дані з поточного шаблону (якщо в коді є жорстко закодовані елементи).', 'natura'); ?>
					</p>
					<form method="post" action="" style="display: inline-block;">
						<?php wp_nonce_field('natura_trusted_sync', 'natura_trusted_sync_nonce'); ?>
						<button type="submit" name="natura_trusted_sync" class="button button-secondary">
							<?php esc_html_e('Синхронізувати з шаблоном', 'natura'); ?>
						</button>
					</form>
				</div>

				<div class="natura-carousel-sections">
					<!-- Top Carousel (Right) -->
					<div class="natura-carousel-section">
						<h3><?php esc_html_e('Верхня лента (рухається вправо)', 'natura'); ?></h3>
						<div class="natura-carousel-items" data-carousel="top">
							<?php
							if (! empty($top_items)) {
								foreach ($top_items as $index => $item) {
									natura_render_carousel_item($item, $index, 'top');
								}
							}
							?>
						</div>
						<button type="button" class="button button-secondary natura-add-item" data-carousel="top">
							<?php esc_html_e('+ Додати елемент', 'natura'); ?>
						</button>
					</div>

					<!-- Bottom Carousel (Left) -->
					<div class="natura-carousel-section">
						<h3><?php esc_html_e('Нижня лента (рухається вліво)', 'natura'); ?></h3>
						<div class="natura-carousel-items" data-carousel="bottom">
							<?php
							if (! empty($bottom_items)) {
								foreach ($bottom_items as $index => $item) {
									natura_render_carousel_item($item, $index, 'bottom');
								}
							}
							?>
						</div>
						<button type="button" class="button button-secondary natura-add-item" data-carousel="bottom">
							<?php esc_html_e('+ Додати елемент', 'natura'); ?>
						</button>
					</div>
				</div>

				<input type="hidden" name="trusted_top" id="trusted-top-data" value="<?php echo esc_attr(json_encode($top_items)); ?>">
				<input type="hidden" name="trusted_bottom" id="trusted-bottom-data" value="<?php echo esc_attr(json_encode($bottom_items)); ?>">

				<p class="submit">
					<button type="submit" name="natura_trusted_save" class="button button-primary button-large">
						<?php esc_html_e('Зберегти зміни', 'natura'); ?>
					</button>
				</p>
			</form>
		</div>
	</div>

	<!-- Template for new item -->
	<script type="text/template" id="natura-carousel-item-template">
		<div class="natura-carousel-item">
			<div class="natura-carousel-item-header">
				<span class="natura-item-number"></span>
				<button type="button" class="button-link natura-remove-item" aria-label="<?php esc_attr_e('Видалити', 'natura'); ?>">
					<?php esc_html_e('Видалити', 'natura'); ?>
				</button>
			</div>
			<div class="natura-carousel-item-content">
				<div class="natura-field">
					<label><?php esc_html_e('Зображення (звичайне)', 'natura'); ?></label>
					<div class="natura-image-field">
						<input type="hidden" class="natura-image-id" value="">
						<div class="natura-image-preview"></div>
						<button type="button" class="button natura-image-upload"><?php esc_html_e('Обрати зображення', 'natura'); ?></button>
						<button type="button" class="button link-button natura-image-remove is-hidden"><?php esc_html_e('Видалити', 'natura'); ?></button>
					</div>
				</div>
				<div class="natura-field">
					<label><?php esc_html_e('Зображення (при наведенні)', 'natura'); ?></label>
					<div class="natura-image-field">
						<input type="hidden" class="natura-image-hover-id" value="">
						<div class="natura-image-hover-preview"></div>
						<button type="button" class="button natura-image-hover-upload"><?php esc_html_e('Обрати зображення', 'natura'); ?></button>
						<button type="button" class="button link-button natura-image-hover-remove is-hidden"><?php esc_html_e('Видалити', 'natura'); ?></button>
					</div>
				</div>
			</div>
		</div>
	</script>

	<?php
	// Enqueue admin scripts and styles
	natura_home_content_admin_assets();
}

/**
 * Render carousel item in admin.
 *
 * @param array  $item Item data.
 * @param int    $index Item index.
 * @param string $carousel Carousel type (top/bottom).
 */
function natura_render_carousel_item($item, $index, $carousel) {
	$icon_id = isset($item['icon_id']) ? (int) $item['icon_id'] : 0;
	$icon_hover_id = isset($item['icon_hover_id']) ? (int) $item['icon_hover_id'] : 0;
	$icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'medium') : '';
	$icon_hover_url = $icon_hover_id ? wp_get_attachment_image_url($icon_hover_id, 'medium') : '';
	?>
	<div class="natura-carousel-item" data-index="<?php echo esc_attr($index); ?>" data-carousel="<?php echo esc_attr($carousel); ?>">
		<div class="natura-carousel-item-header">
			<span class="natura-item-number"><?php echo esc_html($index + 1); ?></span>
			<button type="button" class="button-link natura-remove-item" aria-label="<?php esc_attr_e('Видалити', 'natura'); ?>">
				<?php esc_html_e('Видалити', 'natura'); ?>
			</button>
		</div>
		<div class="natura-carousel-item-content">
			<div class="natura-field">
				<label><?php esc_html_e('Зображення (звичайне)', 'natura'); ?></label>
				<div class="natura-image-field">
					<input type="hidden" class="natura-image-id" value="<?php echo esc_attr($icon_id); ?>">
					<div class="natura-image-preview">
						<?php if ($icon_url) : ?>
							<img src="<?php echo esc_url($icon_url); ?>" alt="">
						<?php endif; ?>
					</div>
					<button type="button" class="button natura-image-upload"><?php esc_html_e('Обрати зображення', 'natura'); ?></button>
					<button type="button" class="button link-button natura-image-remove <?php echo $icon_id ? '' : 'is-hidden'; ?>">
						<?php esc_html_e('Видалити', 'natura'); ?>
					</button>
				</div>
			</div>
			<div class="natura-field">
				<label><?php esc_html_e('Зображення (при наведенні)', 'natura'); ?></label>
				<div class="natura-image-field">
					<input type="hidden" class="natura-image-hover-id" value="<?php echo esc_attr($icon_hover_id); ?>">
					<div class="natura-image-hover-preview">
						<?php if ($icon_hover_url) : ?>
							<img src="<?php echo esc_url($icon_hover_url); ?>" alt="">
						<?php endif; ?>
					</div>
					<button type="button" class="button natura-image-hover-upload"><?php esc_html_e('Обрати зображення', 'natura'); ?></button>
					<button type="button" class="button link-button natura-image-hover-remove <?php echo $icon_hover_id ? '' : 'is-hidden'; ?>">
						<?php esc_html_e('Видалити', 'natura'); ?>
					</button>
				</div>
			</div>
		</div>
	</div>
	<?php
}

/**
 * Sanitize carousel items.
 *
 * @param array $items Items to sanitize.
 * @return array Sanitized items.
 */
function natura_sanitize_carousel_items($items) {
	if (! is_array($items)) {
		return array();
	}

	$sanitized = array();
	foreach ($items as $item) {
		if (! is_array($item)) {
			continue;
		}

		$sanitized[] = array(
			'icon_id' => isset($item['icon_id']) ? absint($item['icon_id']) : 0,
			'icon_hover_id' => isset($item['icon_hover_id']) ? absint($item['icon_hover_id']) : 0,
		);
	}

	return $sanitized;
}

/**
 * Sync trusted carousel data from template file.
 * This function parses the template file to extract current items.
 *
 * @return array|false Synced data or false on failure.
 */
function natura_sync_trusted_from_template() {
	$template_file = get_template_directory() . '/template-parts/home/trusted.php';
	
	if (! file_exists($template_file)) {
		return false;
	}

	$content = file_get_contents($template_file);
	
	// Extract top carousel section (trusted__carousel--right)
	preg_match(
		'/<div class="trusted__carousel trusted__carousel--right">(.*?)<\/div>\s*<\/div>/s',
		$content,
		$top_section
	);

	// Extract bottom carousel section (trusted__carousel--left)
	preg_match(
		'/<div class="trusted__carousel trusted__carousel--left">(.*?)<\/div>\s*<\/div>/s',
		$content,
		$bottom_section
	);

	$top_items = array();
	$bottom_items = array();

	// Process top carousel
	if (! empty($top_section[1])) {
		// Extract all carousel items from top section
		preg_match_all(
			'/<div class="trusted__carousel-item">.*?<img[^>]*class="trusted__icon"[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<img[^>]*class="trusted__icon-hover"[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<\/div>/s',
			$top_section[1],
			$top_matches,
			PREG_SET_ORDER
		);

		$top_seen = array();
		foreach ($top_matches as $match) {
			if (count($top_seen) >= 10) { // Limit to avoid duplicates
				break;
			}
			
			$icon_url = isset($match[1]) ? $match[1] : '';
			$icon_hover_url = isset($match[2]) ? $match[2] : '';
			
			if (empty($icon_url) && empty($icon_hover_url)) {
				continue;
			}
			
			// Check if we've seen this combination (to avoid duplicates)
			$key = md5($icon_url . $icon_hover_url);
			if (in_array($key, $top_seen, true)) {
				continue;
			}
			$top_seen[] = $key;

			// Convert URL to attachment ID
			$icon_id = natura_url_to_attachment_id($icon_url);
			$icon_hover_id = natura_url_to_attachment_id($icon_hover_url);

			if ($icon_id || $icon_hover_id) {
				$top_items[] = array(
					'icon_id' => $icon_id,
					'icon_hover_id' => $icon_hover_id,
				);
			}
		}
	}

	// Process bottom carousel
	if (! empty($bottom_section[1])) {
		// Extract all carousel items from bottom section
		preg_match_all(
			'/<div class="trusted__carousel-item">.*?<img[^>]*class="trusted__icon"[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<img[^>]*class="trusted__icon-hover"[^>]*src=["\']([^"\']+)["\'][^>]*>.*?<\/div>/s',
			$bottom_section[1],
			$bottom_matches,
			PREG_SET_ORDER
		);

		$bottom_seen = array();
		foreach ($bottom_matches as $match) {
			if (count($bottom_seen) >= 10) { // Limit to avoid duplicates
				break;
			}
			
			$icon_url = isset($match[1]) ? $match[1] : '';
			$icon_hover_url = isset($match[2]) ? $match[2] : '';
			
			if (empty($icon_url) && empty($icon_hover_url)) {
				continue;
			}
			
			// Check if we've seen this combination (to avoid duplicates)
			$key = md5($icon_url . $icon_hover_url);
			if (in_array($key, $bottom_seen, true)) {
				continue;
			}
			$bottom_seen[] = $key;

			// Convert URL to attachment ID
			$icon_id = natura_url_to_attachment_id($icon_url);
			$icon_hover_id = natura_url_to_attachment_id($icon_hover_url);

			if ($icon_id || $icon_hover_id) {
				$bottom_items[] = array(
					'icon_id' => $icon_id,
					'icon_hover_id' => $icon_hover_id,
				);
			}
		}
	}

	return array(
		'top' => $top_items,
		'bottom' => $bottom_items,
	);
}

/**
 * Convert attachment URL to attachment ID.
 *
 * @param string $url Attachment URL.
 * @return int Attachment ID or 0.
 */
function natura_url_to_attachment_id($url) {
	if (empty($url)) {
		return 0;
	}

	// Try to extract attachment ID from URL
	$attachment_id = attachment_url_to_postid($url);
	
	if ($attachment_id) {
		return $attachment_id;
	}

	// If direct lookup fails, try to find by filename
	$filename = basename(parse_url($url, PHP_URL_PATH));
	if ($filename) {
		global $wpdb;
		$attachment_id = $wpdb->get_var($wpdb->prepare(
			"SELECT post_id FROM {$wpdb->postmeta} WHERE meta_key = '_wp_attached_file' AND meta_value LIKE %s LIMIT 1",
			'%' . $wpdb->esc_like($filename)
		));
		
		if ($attachment_id) {
			return (int) $attachment_id;
		}
	}

	return 0;
}

/**
 * Enqueue admin assets for home content page.
 */
function natura_home_content_admin_assets() {
	wp_enqueue_media();
	wp_enqueue_script('jquery-ui-sortable');
	wp_enqueue_script(
		'natura-home-content-admin',
		get_template_directory_uri() . '/assets/js/admin-home-content.js',
		array('jquery', 'jquery-ui-sortable'),
		NATURA_THEME_VERSION,
		true
	);
	wp_enqueue_style(
		'natura-home-content-admin',
		get_template_directory_uri() . '/assets/css/admin-home-content.css',
		array(),
		NATURA_THEME_VERSION
	);

	wp_localize_script('natura-home-content-admin', 'naturaHomeContent', array(
		'ajaxUrl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('natura_home_content_nonce'),
		'i18n' => array(
			'removeConfirm' => __('Ви впевнені, що хочете видалити цей елемент?', 'natura'),
		),
	));
}

/**
 * Get trusted carousel items for frontend.
 *
 * @return array Carousel items.
 */
function natura_get_trusted_carousel_items() {
	$data = get_option(NATURA_TRUSTED_CAROUSEL_OPTION, array(
		'top' => array(),
		'bottom' => array(),
	));

	return array(
		'top' => isset($data['top']) ? $data['top'] : array(),
		'bottom' => isset($data['bottom']) ? $data['bottom'] : array(),
	);
}
