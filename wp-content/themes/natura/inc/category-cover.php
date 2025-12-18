<?php
/**
 * Custom image field for WooCommerce product categories (home carousel cover).
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}

const NATURA_CATEGORY_COVER_META = 'natura_category_cover';

/**
 * Render field for add form.
 */
function natura_category_cover_add_field() {
	?>
	<div class="form-field">
		<label for="natura-category-cover"><?php esc_html_e('Зображення для головної', 'natura'); ?></label>
		<div class="natura-category-cover-field">
			<input type="hidden" id="natura-category-cover" name="<?php echo esc_attr(NATURA_CATEGORY_COVER_META); ?>" value="">
			<div class="natura-category-cover-preview"></div>
			<button type="button" class="button natura-category-cover-upload"><?php esc_html_e('Обрати зображення', 'natura'); ?></button>
			<button type="button" class="button link-button natura-category-cover-remove is-hidden"><?php esc_html_e('Видалити', 'natura'); ?></button>
		</div>
		<p class="description"><?php esc_html_e('Це зображення використовується у другому блоці головної сторінки.', 'natura'); ?></p>
	</div>
	<?php
}
add_action('product_cat_add_form_fields', 'natura_category_cover_add_field');

/**
 * Render field for edit form.
 *
 * @param WP_Term $term Term object.
 */
function natura_category_cover_edit_field($term) {
	$attachment_id = (int) get_term_meta($term->term_id, NATURA_CATEGORY_COVER_META, true);
	$image_url     = $attachment_id ? wp_get_attachment_image_url($attachment_id, 'medium') : '';
	$description   = term_description($term);
	?>
	<tr class="form-field">
		<th scope="row">
			<label for="natura-category-cover"><?php esc_html_e('Зображення для головної', 'natura'); ?></label>
		</th>
		<td>
			<div class="natura-category-cover-field">
				<input type="hidden" id="natura-category-cover" name="<?php echo esc_attr(NATURA_CATEGORY_COVER_META); ?>" value="<?php echo esc_attr($attachment_id); ?>">
				<div class="natura-category-cover-preview">
					<?php if ($image_url) : ?>
						<img src="<?php echo esc_url($image_url); ?>" alt="" />
					<?php endif; ?>
				</div>
				<button type="button" class="button natura-category-cover-upload">
					<?php echo $image_url ? esc_html__('Змінити зображення', 'natura') : esc_html__('Обрати зображення', 'natura'); ?>
				</button>
				<button type="button" class="button link-button natura-category-cover-remove<?php echo $image_url ? '' : ' is-hidden'; ?>">
					<?php esc_html_e('Видалити', 'natura'); ?>
				</button>
			</div>
			<p class="description"><?php esc_html_e('Це зображення використовується у другому блоці головної сторінки.', 'natura'); ?></p>
		</td>
	</tr>
	<tr class="form-field">
		<th scope="row"><label for="tag-description"><?php esc_html_e('Опис', 'natura'); ?></label></th>
		<td>
			<?php
			wp_editor(
				wp_kses_post($description),
				'tag-description',
				[
					'textarea_name' => 'description',
					'textarea_rows' => 5,
					'editor_height' => 200,
					'media_buttons' => false,
					'quicktags'     => true,
					'tinymce'       => [
						'toolbar1' => 'bold,italic,underline,separator,bullist,numlist,link,unlink,undo,redo',
						'toolbar2' => '',
					],
				]
			);
			?>
			<p class="description"><?php esc_html_e('Можна додати до трьох коротких речень, вони будуть показані у картці.', 'natura'); ?></p>
		</td>
	</tr>
	<?php
}
add_action('product_cat_edit_form_fields', 'natura_category_cover_edit_field');

/**
 * Save metadata on create/edit.
 *
 * @param int $term_id Term ID.
 */
function natura_category_cover_save($term_id) {
	if (isset($_POST[NATURA_CATEGORY_COVER_META])) {
		$image_id = (int) $_POST[NATURA_CATEGORY_COVER_META];
		if ($image_id > 0) {
			update_term_meta($term_id, NATURA_CATEGORY_COVER_META, $image_id);
		} else {
			delete_term_meta($term_id, NATURA_CATEGORY_COVER_META);
		}
	}
}
add_action('created_product_cat', 'natura_category_cover_save');
add_action('edited_product_cat', 'natura_category_cover_save');

/**
 * Enqueue admin scripts.
 *
 * @param string $hook Current admin page.
 */
function natura_category_cover_admin_assets($hook) {
	if (! in_array($hook, ['edit-tags.php', 'term.php'], true)) {
		return;
	}

	$taxonomy = isset($_GET['taxonomy']) ? sanitize_key($_GET['taxonomy']) : '';

	if ('product_cat' !== $taxonomy) {
		return;
	}

	wp_enqueue_media();
	wp_enqueue_script(
		'natura-category-cover',
		get_template_directory_uri() . '/assets/js/admin-category-cover.js',
		['jquery'],
		NATURA_THEME_VERSION,
		true
	);
	wp_localize_script(
		'natura-category-cover',
		'naturaCategoryCover',
		[
			'title'  => esc_html__('Оберіть зображення', 'natura'),
			'button' => esc_html__('Використати', 'natura'),
		]
	);
	wp_enqueue_style(
		'natura-category-cover',
		get_template_directory_uri() . '/assets/css/admin-category-cover.css',
		[],
		NATURA_THEME_VERSION
	);
}
add_action('admin_enqueue_scripts', 'natura_category_cover_admin_assets');

