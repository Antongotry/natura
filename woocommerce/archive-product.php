<?php
/**
 * The Template for displaying product archives, including the main shop page
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<?php
// =========================
// Категории в каталоге (дерево)
// =========================
$excluded_slugs = array( 'uncategorized', 'bez-kategoriyi', 'bez-kategorii' );
$excluded_names = array( 'без категорії', 'без категории' );

$current_term    = get_queried_object();
$current_term_id = ( $current_term instanceof WP_Term && $current_term->taxonomy === 'product_cat' ) ? (int) $current_term->term_id : 0;
$ancestor_ids    = $current_term_id ? array_map( 'intval', get_ancestors( $current_term_id, 'product_cat' ) ) : array();

// URL "Всі товари" (надежный fallback, даже если shop page не настроена)
$shop_url = function_exists( 'wc_get_page_permalink' ) ? wc_get_page_permalink( 'shop' ) : '';
if ( empty( $shop_url ) && function_exists( 'wc_get_page_id' ) ) {
	$shop_page_id = wc_get_page_id( 'shop' );
	if ( $shop_page_id && $shop_page_id > 0 ) {
		$shop_url = get_permalink( $shop_page_id );
	}
}
if ( empty( $shop_url ) && function_exists( 'get_post_type_archive_link' ) ) {
	$shop_url = get_post_type_archive_link( 'product' );
}
if ( empty( $shop_url ) ) {
	$shop_url = home_url( '/?post_type=product' );
}

if ( ! function_exists( 'natura_is_excluded_product_cat' ) ) {
	function natura_is_excluded_product_cat( $term, array $excluded_slugs, array $excluded_names ): bool {
		if ( ! ( $term instanceof WP_Term ) ) {
			return true;
		}

		if ( in_array( $term->slug, $excluded_slugs, true ) ) {
			return true;
		}

		$name = function_exists( 'mb_strtolower' ) ? mb_strtolower( $term->name ) : strtolower( $term->name );

		return in_array( $name, $excluded_names, true );
	}
}

if ( ! function_exists( 'natura_render_product_cat_items' ) ) {
	/**
	 * Рендерит дерево категорий product_cat в виде <li>…</li>.
	 * Возвращает true, если что-то вывело.
	 */
	function natura_render_product_cat_items(
		int $parent_id,
		int $current_term_id,
		array $ancestor_ids,
		array $excluded_slugs,
		array $excluded_names,
		array $opts,
		int $depth = 0
	): bool {
		$terms = get_terms(
			array(
				'taxonomy'   => 'product_cat',
				'hide_empty' => true,
				'pad_counts' => true,
				'hierarchical' => true,
				'parent'     => $parent_id,
				'orderby'    => 'menu_order',
				'order'      => 'ASC',
			)
		);

		if ( is_wp_error( $terms ) || empty( $terms ) ) {
			return false;
		}

		// Сортировка корневых категорий по порядку как на главной странице
		if ( $parent_id === 0 ) {
			$allowed_slugs = ['ovochi', 'frukty', 'yagody', 'zelen-travy-salaty', 'gryby', 'pryanoshhi-ta-prypravy', 'bakaliya', 'molochni-produkty', 'yajczya', 'gorihy-ta-nasinnya', 'kava-ta-chaj', 'sneky'];
			
			// Фильтруем только разрешенные категории
			$filtered_terms = array_filter(
				$terms,
				static function ($term) use ($allowed_slugs) {
					return in_array($term->slug, $allowed_slugs, true);
				}
			);
			
			// Сортируем категории по порядку в массиве $allowed_slugs
			$sorted_terms = [];
			foreach ($allowed_slugs as $slug) {
				foreach ($filtered_terms as $term) {
					if ($term->slug === $slug) {
						$sorted_terms[] = $term;
						break;
					}
				}
			}
			
			$terms = $sorted_terms;
		} else {
			// Для дочерних категорий сортируем по menu_order
			usort( $terms, function( $a, $b ) {
				$a_order = isset( $a->term_order ) ? (int) $a->term_order : 0;
				$b_order = isset( $b->term_order ) ? (int) $b->term_order : 0;
				return $a_order - $b_order;
			} );
		}

		$printed = false;

		foreach ( $terms as $term ) {
			if ( natura_is_excluded_product_cat( $term, $excluded_slugs, $excluded_names ) ) {
				continue;
			}

			$term_id     = (int) $term->term_id;
			$is_current  = $current_term_id && $term_id === $current_term_id;
			$is_ancestor = $current_term_id && in_array( $term_id, $ancestor_ids, true );
			$is_expanded = $is_current || $is_ancestor;

			$base_item_class = $depth === 0 ? ( $opts['item_root'] ?? '' ) : ( $opts['item_child'] ?? '' );
			$active_class    = $depth === 0 ? ( $opts['item_root_active'] ?? '' ) : ( $opts['item_child_active'] ?? '' );
			$expanded_class  = $depth === 0 ? ( $opts['item_root_expanded'] ?? '' ) : ( $opts['item_child_expanded'] ?? '' );

			$item_classes = trim(
				$base_item_class
				. ( $is_current && $active_class ? ' ' . $active_class : '' )
				. ( $is_expanded && $expanded_class ? ' ' . $expanded_class : '' )
			);

			$link_class = $depth === 0 ? ( $opts['link_root'] ?? '' ) : ( $opts['link_child'] ?? '' );

			$term_link = get_term_link( $term );
			if ( is_wp_error( $term_link ) ) {
				continue;
			}

			$printed = true;

			echo '<li class="' . esc_attr( $item_classes ) . '">';
			echo '<a href="' . esc_url( $term_link ) . '"' . ( $link_class ? ' class="' . esc_attr( $link_class ) . '"' : '' ) . '>';
			echo esc_html( $term->name );
			echo '</a>';

			ob_start();
			$child_printed = natura_render_product_cat_items(
				$term_id,
				$current_term_id,
				$ancestor_ids,
				$excluded_slugs,
				$excluded_names,
				$opts,
				$depth + 1
			);
			$children_html = ob_get_clean();

			if ( $child_printed && ! empty( $children_html ) ) {
				echo '<ul class="' . esc_attr( $opts['sub_list'] ?? '' ) . '">';
				echo $children_html;
				echo '</ul>';
			}

			echo '</li>';
		}

		return $printed;
	}
}

$mobile_opts = array(
	'item_root'           => 'shop-filter-list__item',
	'item_child'          => 'shop-filter-list__item shop-filter-list__item--child',
	'item_root_active'    => 'shop-filter-list__item--active',
	'item_child_active'   => 'shop-filter-list__item--active',
	'item_root_expanded'  => 'shop-filter-list__item--expanded',
	'item_child_expanded' => 'shop-filter-list__item--expanded',
	'link_root'           => 'shop-filter-list__link',
	'link_child'          => 'shop-filter-list__link shop-filter-list__link--child',
	'sub_list'            => 'shop-filter-list__sublist',
);

$sidebar_opts = array(
	'item_root'           => 'shop-archive-filters__item',
	'item_child'          => 'shop-archive-filters__subitem',
	'item_root_active'    => 'shop-archive-filters__item--active',
	'item_child_active'   => 'shop-archive-filters__subitem--active',
	'item_root_expanded'  => 'shop-archive-filters__item--expanded',
	'item_child_expanded' => 'shop-archive-filters__subitem--expanded',
	'link_root'           => '',
	'link_child'          => 'shop-archive-filters__sublink',
	'sub_list'            => 'shop-archive-filters__sublist',
);
?>

<main id="primary" class="site-main" data-shop-url="<?php echo esc_url( $shop_url ); ?>">
	<!-- Shop Archive Hero Banner -->
	<section class="shop-archive-hero">
		<div class="container">
			<div class="shop-archive-hero__image-wrapper">
				<?php
				// Получаем изображение баннера (можно настроить через опции темы)
				$banner_image_desktop = get_theme_mod( 'shop_archive_banner_image_desktop', 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-62_result-scaled.webp' );
				$banner_image_mobile = get_theme_mod( 'shop_archive_banner_image_mobile', 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-62-1.png' );
				?>
				<picture>
					<source media="(max-width: 768px)" srcset="<?php echo esc_url( $banner_image_mobile ); ?>">
					<img src="<?php echo esc_url( $banner_image_desktop ); ?>" alt="<?php esc_attr_e( 'Каталог товарів', 'natura' ); ?>" class="shop-archive-hero__image">
				</picture>
				<div class="shop-archive-hero__content">
					<div class="shop-archive-hero__breadcrumb">
						<?php woocommerce_breadcrumb(); ?>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Mobile Filter Button (sticky, left side, egg-shaped) -->
	<button type="button" class="shop-filter-mobile-button" data-shop-filter-open aria-label="<?php esc_attr_e( 'Відкрити категорії', 'natura' ); ?>">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/game_13602763-1.svg" alt="" class="shop-filter-mobile-button__icon">
	</button>

	<!-- Mobile Filter Drawer (slides from left, centered) -->
	<div class="shop-filter-drawer" data-shop-filter-drawer>
		<div class="shop-filter-drawer__overlay" data-shop-filter-close></div>
		<div class="shop-filter-drawer__wrapper">
			<div class="shop-filter-drawer__content">
			<div class="shop-filter-drawer__header">
				<h2 class="shop-filter-drawer__title"><?php esc_html_e( 'Категорії', 'natura' ); ?></h2>
				<button type="button" class="shop-filter-drawer__close" data-shop-filter-close aria-label="<?php esc_attr_e( 'Закрити категорії', 'natura' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M18 6L6 18M6 6L18 18" stroke="#303030" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
			</div>
			<div class="shop-filter-drawer__body">
				<!-- Categories Filter -->
				<div class="shop-filter-section">
					<ul class="shop-filter-list">
						<?php
						natura_render_product_cat_items( 0, $current_term_id, $ancestor_ids, $excluded_slugs, $excluded_names, $mobile_opts );
						?>
					</ul>
				</div>
			</div>
		</div>
		</div>
	</div>

	<div class="shop-archive-content">
		<div class="container">
			<div class="shop-archive-layout">
				<aside class="shop-archive-sidebar">
					<h2 class="shop-archive-sidebar__title"><?php esc_html_e( 'Категорії', 'natura' ); ?></h2>
					<ul class="shop-archive-filters">
						<?php
						natura_render_product_cat_items( 0, $current_term_id, $ancestor_ids, $excluded_slugs, $excluded_names, $sidebar_opts );
						?>
					</ul>
				</aside>
				<div class="shop-archive-products">
					<?php if ( woocommerce_product_loop() ) : ?>
						<?php woocommerce_product_loop_start(); ?>

						<?php while ( have_posts() ) : ?>
							<?php the_post(); ?>
							<?php wc_get_template_part( 'content', 'product' ); ?>
						<?php endwhile; ?>

						<?php woocommerce_product_loop_end(); ?>

						<?php woocommerce_pagination(); ?>
					<?php else : ?>
						<?php wc_no_products_found(); ?>
					<?php endif; ?>
				</div>
			</div>
		</div>
	</div>
</main>

<?php
get_footer( 'shop' );










