<?php
/**
 * The Template for displaying product archives, including the main shop page
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

get_header( 'shop' ); ?>

<main id="primary" class="site-main">
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
	<button type="button" class="shop-filter-mobile-button" data-shop-filter-open aria-label="<?php esc_attr_e( 'Відкрити фільтри', 'natura' ); ?>">
		<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/game_13602763-1.svg" alt="" class="shop-filter-mobile-button__icon">
	</button>

	<!-- Mobile Filter Drawer (slides from left, centered) -->
	<div class="shop-filter-drawer" data-shop-filter-drawer>
		<div class="shop-filter-drawer__overlay" data-shop-filter-close></div>
		<div class="shop-filter-drawer__wrapper">
			<div class="shop-filter-drawer__content">
			<div class="shop-filter-drawer__header">
				<h2 class="shop-filter-drawer__title"><?php esc_html_e( 'Фільтри', 'natura' ); ?></h2>
				<button type="button" class="shop-filter-drawer__close" data-shop-filter-close aria-label="<?php esc_attr_e( 'Закрити фільтри', 'natura' ); ?>">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M18 6L6 18M6 6L18 18" stroke="#303030" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
			</div>
			<div class="shop-filter-drawer__body">
				<!-- Categories Filter -->
				<div class="shop-filter-section">
					<h3 class="shop-filter-section__title"><?php esc_html_e( 'Категорії', 'natura' ); ?></h3>
					<?php
					$shop_categories = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'hide_empty' => false,
							'parent'     => 0,
						)
					);

					if ( ! is_wp_error( $shop_categories ) && ! empty( $shop_categories ) ) :
						$current_term = get_queried_object();
						?>
						<ul class="shop-filter-list">
							<?php foreach ( $shop_categories as $cat ) : ?>
								<?php
								$is_active = ( $current_term instanceof WP_Term ) && $current_term->taxonomy === 'product_cat' && (int) $current_term->term_id === (int) $cat->term_id;
								$item_classes = 'shop-filter-list__item' . ( $is_active ? ' shop-filter-list__item--active' : '' );
								?>
								<li class="<?php echo esc_attr( $item_classes ); ?>">
									<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>" class="shop-filter-list__link">
										<?php echo esc_html( $cat->name ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
				</div>

				<!-- Price Filter -->
				<div class="shop-filter-section">
					<h3 class="shop-filter-section__title"><?php esc_html_e( 'Ціна', 'natura' ); ?></h3>
					<div class="shop-filter-price">
						<div class="shop-filter-price__inputs">
							<input type="number" class="shop-filter-price__input" placeholder="Від" min="0" id="filter-price-min">
							<span class="shop-filter-price__separator">—</span>
							<input type="number" class="shop-filter-price__input" placeholder="До" min="0" id="filter-price-max">
						</div>
						<button type="button" class="shop-filter-price__apply" data-price-filter-apply><?php esc_html_e( 'Застосувати', 'natura' ); ?></button>
					</div>
				</div>
			</div>
			<div class="shop-filter-drawer__footer">
				<button type="button" class="shop-filter-drawer__reset" data-filter-reset><?php esc_html_e( 'Скинути фільтри', 'natura' ); ?></button>
				<button type="button" class="shop-filter-drawer__apply" data-filter-apply><?php esc_html_e( 'Застосувати', 'natura' ); ?></button>
			</div>
		</div>
		</div>
	</div>

	<div class="shop-archive-content">
		<div class="container">
			<div class="shop-archive-layout">
				<aside class="shop-archive-sidebar">
					<h2 class="shop-archive-sidebar__title"><?php esc_html_e( 'Категорії', 'natura' ); ?></h2>
					<?php
					// Выводим список категорий товаров как фильтры.
					$shop_categories = get_terms(
						array(
							'taxonomy'   => 'product_cat',
							'hide_empty' => false, // показываем даже пустые категории
							'parent'     => 0,
						)
					);

					if ( ! is_wp_error( $shop_categories ) && ! empty( $shop_categories ) ) :
						$current_term = get_queried_object();
						?>
						<ul class="shop-archive-filters">
							<?php foreach ( $shop_categories as $cat ) : ?>
								<?php
								$is_active = ( $current_term instanceof WP_Term ) && $current_term->taxonomy === 'product_cat' && (int) $current_term->term_id === (int) $cat->term_id;
								$item_classes = 'shop-archive-filters__item' . ( $is_active ? ' shop-archive-filters__item--active' : '' );
								?>
								<li class="<?php echo esc_attr( $item_classes ); ?>">
									<a href="<?php echo esc_url( get_term_link( $cat ) ); ?>">
										<?php echo esc_html( $cat->name ); ?>
									</a>
								</li>
							<?php endforeach; ?>
						</ul>
					<?php endif; ?>
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










