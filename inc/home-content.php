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
		if ($synced_data && (! empty($synced_data['top']) || ! empty($synced_data['bottom']))) {
			update_option(NATURA_TRUSTED_CAROUSEL_OPTION, $synced_data);
			$top_count = isset($synced_data['top']) ? count($synced_data['top']) : 0;
			$bottom_count = isset($synced_data['bottom']) ? count($synced_data['bottom']) : 0;
			echo '<div class="notice notice-success is-dismissible"><p>' . 
				sprintf(
					esc_html__('Дані синхронізовано! Знайдено: %d елементів у верхній ленті, %d елементів у нижній ленті. Сторінка буде оновлена...', 'natura'),
					$top_count,
					$bottom_count
				) . '</p></div>';
			
			// Refresh page data
			$carousel_data = $synced_data;
			$top_items = isset($carousel_data['top']) ? $carousel_data['top'] : array();
			$bottom_items = isset($carousel_data['bottom']) ? $carousel_data['bottom'] : array();
			
			// Add JavaScript to reload page after a short delay
			echo '<script>
				setTimeout(function() {
					window.location.reload();
				}, 1500);
			</script>';
		} else {
			// Try to get current saved data as fallback
			$current_data = get_option(NATURA_TRUSTED_CAROUSEL_OPTION, false);
			if ($current_data && (! empty($current_data['top']) || ! empty($current_data['bottom']))) {
				echo '<div class="notice notice-info is-dismissible"><p>' . 
					esc_html__('Використано збережені дані. Якщо потрібно синхронізувати з медіа-бібліотекою, переконайтеся, що файли ca-1.svg, ca-2.svg, cb-1.svg тощо завантажені.', 'natura') . 
					'</p></div>';
			} else {
				echo '<div class="notice notice-warning is-dismissible"><p>' . 
					esc_html__('Не вдалося знайти зображення в медіа-бібліотеці. Переконайтеся, що файли ca-1.svg, ca-2.svg, cb-1.svg, cb-2.svg тощо завантажені в медіа-бібліотеку WordPress. Або додайте елементи вручну через форму вище.', 'natura') . 
					'</p></div>';
			}
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
 * This function tries to find images in media library by known filenames.
 *
 * @return array|false Synced data or false on failure.
 */
function natura_sync_trusted_from_template() {
	// Default image filenames that were used in the original template
	$top_filenames = array(
		array('icon' => 'ca-1.svg', 'hover' => 'ca-1-hover.svg'),
		array('icon' => 'ca-2.svg', 'hover' => 'ca-2-hover.svg'),
		array('icon' => 'ca-3.svg', 'hover' => 'ca-3-hover.svg'),
		array('icon' => 'ca-4.svg', 'hover' => 'ca-4-hover.svg'),
		array('icon' => 'ca-5.svg', 'hover' => 'ca-5-hover.svg'),
	);

	$bottom_filenames = array(
		array('icon' => 'cb-1.svg', 'hover' => 'cb-1-hover.svg'),
		array('icon' => 'cb-2.svg', 'hover' => 'cb-2-hover.svg'),
		array('icon' => 'cb-3.svg', 'hover' => 'cb-3-hover.svg'),
		array('icon' => 'cb-4.svg', 'hover' => 'cb-4-hover.svg'),
		array('icon' => 'cb-5.svg', 'hover' => 'cb-5-hover.svg'),
	);

	$top_items = array();
	$bottom_items = array();

	// Process top carousel
	foreach ($top_filenames as $file_pair) {
		$icon_id = natura_find_attachment_by_filename($file_pair['icon']);
		$icon_hover_id = natura_find_attachment_by_filename($file_pair['hover']);

		// If exact match not found, try to find by pattern (ca-1, ca_1, ca1, etc.)
		if (! $icon_id) {
			$base_name = pathinfo($file_pair['icon'], PATHINFO_FILENAME); // e.g., 'ca-1' from 'ca-1.svg'
			$icon_id = natura_find_attachment_by_pattern($base_name);
		}
		if (! $icon_hover_id) {
			$base_name = pathinfo($file_pair['hover'], PATHINFO_FILENAME); // e.g., 'ca-1-hover' from 'ca-1-hover.svg'
			$icon_hover_id = natura_find_attachment_by_pattern($base_name);
		}

		if ($icon_id || $icon_hover_id) {
			$top_items[] = array(
				'icon_id' => $icon_id,
				'icon_hover_id' => $icon_hover_id,
			);
		}
	}

	// Process bottom carousel
	foreach ($bottom_filenames as $file_pair) {
		$icon_id = natura_find_attachment_by_filename($file_pair['icon']);
		$icon_hover_id = natura_find_attachment_by_filename($file_pair['hover']);

		// If exact match not found, try to find by pattern (cb-1, cb_1, cb1, etc.)
		if (! $icon_id) {
			$base_name = pathinfo($file_pair['icon'], PATHINFO_FILENAME); // e.g., 'cb-1' from 'cb-1.svg'
			$icon_id = natura_find_attachment_by_pattern($base_name);
		}
		if (! $icon_hover_id) {
			$base_name = pathinfo($file_pair['hover'], PATHINFO_FILENAME); // e.g., 'cb-1-hover' from 'cb-1-hover.svg'
			$icon_hover_id = natura_find_attachment_by_pattern($base_name);
		}

		if ($icon_id || $icon_hover_id) {
			$bottom_items[] = array(
				'icon_id' => $icon_id,
				'icon_hover_id' => $icon_hover_id,
			);
		}
	}

	// If we found items, return them
	if (! empty($top_items) || ! empty($bottom_items)) {
		return array(
			'top' => $top_items,
			'bottom' => $bottom_items,
		);
	}

	// If no items found, try to get from current saved data
	$current_data = get_option(NATURA_TRUSTED_CAROUSEL_OPTION, false);
	if ($current_data && (isset($current_data['top']) || isset($current_data['bottom']))) {
		return $current_data;
	}

	return false;
}

/**
 * Find attachment ID by pattern (for flexible search).
 * Searches for files containing the pattern in filename or title.
 *
 * @param string $pattern Pattern to search for (e.g., 'ca-1', 'ca1').
 * @return int Attachment ID or 0.
 */
function natura_find_attachment_by_pattern($pattern) {
	if (empty($pattern)) {
		return 0;
	}

	global $wpdb;

	// Search in _wp_attached_file meta using LIKE (with and without separators)
	$attachment_id = $wpdb->get_var($wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} 
		WHERE meta_key = '_wp_attached_file' 
		AND (meta_value LIKE %s OR meta_value LIKE %s OR meta_value LIKE %s)
		LIMIT 1",
		'%' . $wpdb->esc_like($pattern) . '%',
		'%' . $wpdb->esc_like(str_replace('-', '_', $pattern)) . '%',
		'%' . $wpdb->esc_like(str_replace(array('-', '_'), '', $pattern)) . '%'
	));

	if ($attachment_id) {
		return (int) $attachment_id;
	}

	// Also try in post title
	$attachment_id = $wpdb->get_var($wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} 
		WHERE post_type = 'attachment' 
		AND (post_title LIKE %s OR post_title LIKE %s OR post_title LIKE %s)
		LIMIT 1",
		'%' . $wpdb->esc_like($pattern) . '%',
		'%' . $wpdb->esc_like(str_replace('-', '_', $pattern)) . '%',
		'%' . $wpdb->esc_like(str_replace(array('-', '_'), '', $pattern)) . '%'
	));

	return $attachment_id ? (int) $attachment_id : 0;
}

/**
 * Find attachment ID by filename.
 *
 * @param string $filename Filename to search for.
 * @return int Attachment ID or 0.
 */
function natura_find_attachment_by_filename($filename) {
	if (empty($filename)) {
		return 0;
	}

	global $wpdb;

	// Clean filename (remove path if present)
	$filename = basename($filename);
	
	// Try exact match in _wp_attached_file meta
	$attachment_id = $wpdb->get_var($wpdb->prepare(
		"SELECT post_id FROM {$wpdb->postmeta} 
		WHERE meta_key = '_wp_attached_file' 
		AND (meta_value = %s OR meta_value LIKE %s)
		LIMIT 1",
		$filename,
		'%/' . $wpdb->esc_like($filename)
	));

	if ($attachment_id) {
		return (int) $attachment_id;
	}

	// Try to find by post title
	$attachment_id = $wpdb->get_var($wpdb->prepare(
		"SELECT ID FROM {$wpdb->posts} 
		WHERE post_type = 'attachment' 
		AND (post_title = %s OR post_title LIKE %s)
		LIMIT 1",
		$filename,
		'%' . $wpdb->esc_like($filename) . '%'
	));

	if ($attachment_id) {
		return (int) $attachment_id;
	}

	// Try to find by guid (URL) - remove extension for more flexible search
	$filename_no_ext = pathinfo($filename, PATHINFO_FILENAME);
	if ($filename_no_ext) {
		$attachment_id = $wpdb->get_var($wpdb->prepare(
			"SELECT ID FROM {$wpdb->posts} 
			WHERE post_type = 'attachment' 
			AND (guid LIKE %s OR guid LIKE %s)
			LIMIT 1",
			'%' . $wpdb->esc_like($filename) . '%',
			'%' . $wpdb->esc_like($filename_no_ext) . '%'
		));

		if ($attachment_id) {
			return (int) $attachment_id;
		}
	}

	return 0;
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
		return natura_find_attachment_by_filename($filename);
	}

	return 0;
}

/**
 * Enqueue admin assets for home content page.
 */
function natura_home_content_admin_assets() {
	// Enqueue media uploader scripts
	wp_enqueue_media();
	
	// Enqueue jQuery UI Sortable
	wp_enqueue_script('jquery-ui-sortable');
	
	// Enqueue our custom script with proper dependencies
	wp_enqueue_script(
		'natura-home-content-admin',
		get_template_directory_uri() . '/assets/js/admin-home-content.js',
		array('jquery', 'jquery-ui-sortable', 'media-upload', 'media-views'),
		NATURA_THEME_VERSION,
		true
	);
	
	// Enqueue styles
	wp_enqueue_style(
		'natura-home-content-admin',
		get_template_directory_uri() . '/assets/css/admin-home-content.css',
		array(),
		NATURA_THEME_VERSION
	);

	// Localize script
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
