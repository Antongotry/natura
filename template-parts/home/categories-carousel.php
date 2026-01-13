<?php
/**
 * Home page categories carousel section.
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}

$taxonomy = 'product_cat';

$args = apply_filters(
	'natura_categories_carousel_args',
	[
		'taxonomy'   => $taxonomy,
		'hide_empty' => false,
		'parent'     => 0,
		'exclude'    => [],
		'number'     => 0,
	]
);

$terms = get_terms($args);

$excluded_slugs = apply_filters(
	'natura_categories_carousel_excluded_slugs',
	[
		'uncategorized',
		'bez-kategoriyi',
		'bez-kategorii',
	]
);

$excluded_names = apply_filters(
	'natura_categories_carousel_excluded_names',
	[
		'без категорії',
		'без категории',
	]
);

if (! empty($terms) && ! is_wp_error($terms)) {
	// Показываем только категории в нужной последовательности
	$allowed_slugs = ['ovochi', 'frukty', 'yagody', 'zelen-travy-salaty', 'gryby', 'pryanoshhi-ta-prypravy', 'bakaliya', 'molochni-produkty', 'yajczya', 'gorihy-ta-nasinnya', 'kava-ta-chaj', 'sneky'];
	
	$filtered_terms = array_filter(
		$terms,
		static function ($term) use ($excluded_slugs, $excluded_names, $allowed_slugs) {
			// Проверяем, что категория в списке разрешенных
			if (! in_array($term->slug, $allowed_slugs, true)) {
				return false;
			}
			
			$slug_match = in_array($term->slug, $excluded_slugs, true);
			$name_match = function_exists('mb_strtolower')
				? in_array(mb_strtolower($term->name), $excluded_names, true)
				: in_array(strtolower($term->name), $excluded_names, true);

			return ! $slug_match && ! $name_match;
		}
	);
	
	// Сортируем категории по порядку в массиве $allowed_slugs
	$terms = [];
	foreach ($allowed_slugs as $slug) {
		foreach ($filtered_terms as $term) {
			if ($term->slug === $slug) {
				$terms[] = $term;
				break;
			}
		}
	}
	
	$terms = array_values($terms);
}

if (empty($terms) || is_wp_error($terms)) {
	return;
}

$carousel_id = 'categories-carousel-' . wp_unique_id();
?>

<section class="home-categories-carousel" id="catalog" data-module="categories-carousel">
	<div class="home-categories-carousel__header container">
		<h2 class="home-categories-carousel__title">
			<span aria-hidden="true"><?php esc_html_e('Все необхідне', 'natura'); ?><br><?php esc_html_e('в одному місці', 'natura'); ?></span>
			<span class="sr-only"><?php esc_html_e('Все необхідне', 'natura'); ?> <?php esc_html_e('в одному місці', 'natura'); ?></span>
		</h2>

		<?php if (count($terms) > 1) : ?>
			<div class="home-categories-carousel__nav" role="toolbar" aria-label="<?php esc_attr_e('Керування слайдером категорій', 'natura'); ?>">
				<button
					type="button"
					class="home-categories-carousel__arrow home-categories-carousel__arrow--prev is-disabled"
					aria-label="<?php esc_attr_e('Попередні категорії', 'natura'); ?>"
					data-carousel-prev
					disabled
				>
					<img
						src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/left-hover.svg"
						data-icon-default="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/left-hover.svg"
						data-icon-hover="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/white-left.svg"
						alt=""
						width="20"
						height="20"
						loading="lazy"
					>
				</button>

				<button
					type="button"
					class="home-categories-carousel__arrow home-categories-carousel__arrow--next"
					aria-label="<?php esc_attr_e('Наступні категорії', 'natura'); ?>"
					data-carousel-next
				>
					<img
						src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/right-hover.svg"
						data-icon-default="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/right-hover.svg"
						data-icon-hover="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/white-right.svg"
						alt=""
						width="20"
						height="20"
						loading="lazy"
					>
				</button>
			</div>
		<?php endif; ?>
	</div>

	<div class="home-categories-carousel__body container">
		<div
			id="<?php echo esc_attr($carousel_id); ?>"
			class="swiper home-categories-carousel__swiper"
			data-swiper
		>
			<div class="swiper-wrapper home-categories-carousel__wrapper">
				<?php foreach ($terms as $index => $term) : ?>
					<?php
					$cover_id         = (int) get_term_meta($term->term_id, NATURA_CATEGORY_COVER_META, true);
					$thumbnail_id     = $cover_id ?: get_term_meta($term->term_id, 'thumbnail_id', true);
					$image_attributes = $thumbnail_id ? wp_get_attachment_image_src($thumbnail_id, 'large') : false;
					$image_alt        = $thumbnail_id ? get_post_meta($thumbnail_id, '_wp_attachment_image_alt', true) : '';
					$term_link        = get_term_link($term);
					$term_description = term_description($term, $taxonomy);
					$placeholder      = function_exists('mb_substr') ? mb_strtoupper(mb_substr($term->name, 0, 1)) : strtoupper(substr($term->name, 0, 1));
					$cta_label        = sprintf(
						/* translators: %s: category name */
						esc_html__('Перейти до категорії %s', 'natura'),
						esc_html($term->name)
					);
					?>

					<article class="swiper-slide home-categories-carousel__slide" data-category-id="<?php echo esc_attr($term->term_id); ?>">
						<a class="home-categories-carousel__link" href="<?php echo esc_url($term_link); ?>">
							<div class="home-categories-carousel__media<?php echo $image_attributes ? '' : ' home-categories-carousel__media--empty'; ?>">
								<?php if ($image_attributes) : ?>
									<img
										class="home-categories-carousel__image"
										src="<?php echo esc_url($image_attributes[0]); ?>"
										width="<?php echo esc_attr($image_attributes[1]); ?>"
										height="<?php echo esc_attr($image_attributes[2]); ?>"
										alt="<?php echo esc_attr($image_alt ? $image_alt : $term->name); ?>"
										loading="lazy"
									>
								<?php else : ?>
									<span class="home-categories-carousel__placeholder"><?php echo esc_html($placeholder); ?></span>
								<?php endif; ?>
							</div>

							<div class="home-categories-carousel__content">
								<h3 class="home-categories-carousel__name"><?php echo esc_html($term->name); ?></h3>

								<div class="home-categories-carousel__footer">
									<?php if (! empty($term_description)) : ?>
										<div class="home-categories-carousel__description">
											<?php echo wp_kses_post($term_description); ?>
										</div>
									<?php endif; ?>

									<span class="home-categories-carousel__cta" aria-hidden="true"></span>
									<span class="screen-reader-text"><?php echo esc_html($cta_label); ?></span>
								</div>
							</div>
						</a>
					</article>
				<?php endforeach; ?>
			</div>
			<?php if (count($terms) > 1) : ?>
				<div class="swiper-pagination home-categories-carousel__pagination"></div>
			<?php endif; ?>
		</div>
	</div>
</section>

