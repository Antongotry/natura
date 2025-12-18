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










