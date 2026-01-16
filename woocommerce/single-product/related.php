<?php
/**
 * Related Products with Swiper Carousel (Infinite Loop)
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

if ( $related_products ) :
	/**
	 * Ensure all images of related products are lazy loaded by increasing the
	 * current media count to WordPress's lazy loading threshold if needed.
	 */
	if ( function_exists( 'wp_increase_content_media_count' ) ) {
		$content_media_count = wp_increase_content_media_count( 0 );
		if ( $content_media_count < wp_omit_loading_attr_threshold() ) {
			wp_increase_content_media_count( wp_omit_loading_attr_threshold() - $content_media_count );
		}
	}

	$carousel_id = 'related-products-carousel-' . wp_unique_id();
	$products_count = count( $related_products );
	?>

	<section class="related products single-product__related-section">
		<div class="container">
			<div class="single-product__related-header">
			<h2 class="single-product__related-heading">Схожі товари</h2>
				<?php if ( $products_count > 4 ) : ?>
				<div class="single-product__related-nav">
					<button type="button" class="single-product__related-prev" aria-label="<?php esc_attr_e( 'Попередні товари', 'natura' ); ?>">
						<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/left-hover.svg" 
							data-icon-default="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/left-hover.svg"
							data-icon-hover="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/white-left.svg"
							alt="" width="20" height="20">
					</button>
					<button type="button" class="single-product__related-next" aria-label="<?php esc_attr_e( 'Наступні товари', 'natura' ); ?>">
						<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/right-hover.svg"
							data-icon-default="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/right-hover.svg"
							data-icon-hover="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/white-right.svg"
							alt="" width="20" height="20">
					</button>
				</div>
				<?php endif; ?>
			</div>
			<div
				id="<?php echo esc_attr( $carousel_id ); ?>"
				class="swiper single-product__related-swiper"
				data-swiper
			>
				<ul class="products columns-4 swiper-wrapper single-product__related-wrapper">
					<?php foreach ( $related_products as $related_product ) : ?>
						<?php
						$post_object = get_post( $related_product->get_id() );
						setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
						?>
						<?php wc_get_template_part( 'content', 'product' ); ?>
					<?php endforeach; ?>
				</ul>
			</div>
		</div>
	</section>
	<?php
endif;

wp_reset_postdata();

