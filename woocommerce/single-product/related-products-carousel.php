<?php
/**
 * Related products carousel - same category
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $product;

// Получаем категории текущего товара
$product_categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'ids' ) );

if ( empty( $product_categories ) || is_wp_error( $product_categories ) ) {
	return;
}

// Получаем товары той же категории, исключая текущий
$args = array(
	'post_type'      => 'product',
	'posts_per_page' => -1,
	'post__not_in'   => array( $product->get_id() ),
	'tax_query'      => array(
		array(
			'taxonomy' => 'product_cat',
			'field'    => 'term_id',
			'terms'    => $product_categories,
		),
	),
	'meta_query'     => array(
		array(
			'key'     => '_visibility',
			'value'   => array( 'catalog', 'visible' ),
			'compare' => 'IN',
		),
	),
);

$related_products = new WP_Query( $args );

if ( ! $related_products->have_posts() ) {
	return;
}

$carousel_id = 'related-products-carousel-' . wp_unique_id();
?>

<section class="single-product__related-carousel">
	<div class="container">
		<div
			id="<?php echo esc_attr( $carousel_id ); ?>"
			class="swiper single-product__related-swiper"
			data-swiper
		>
			<div class="swiper-wrapper single-product__related-wrapper">
				<?php while ( $related_products->have_posts() ) : $related_products->the_post(); ?>
					<?php
					global $product;
					$product = wc_get_product( get_the_ID() );
					?>
					<div class="swiper-slide single-product__related-slide">
						<a href="<?php echo esc_url( get_permalink() ); ?>" class="single-product__related-link">
							<?php if ( has_post_thumbnail() ) : ?>
								<div class="single-product__related-image">
									<?php the_post_thumbnail( 'woocommerce_thumbnail', array( 'alt' => get_the_title() ) ); ?>
								</div>
							<?php endif; ?>
							<div class="single-product__related-content">
								<h3 class="single-product__related-title"><?php the_title(); ?></h3>
								<div class="single-product__related-price">
									<?php echo $product->get_price_html(); ?>
								</div>
							</div>
						</a>
					</div>
				<?php endwhile; ?>
			</div>
		</div>
	</div>
</section>

<?php
wp_reset_postdata();
?>












