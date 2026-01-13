<?php
/**
 * Sales page content.
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}
?>

<section class="sales" id="sales">
	<div class="sales__inner container">
		<div class="sales__left">
			<h2 class="sales__title">
				<span class="sales__title-accent">Спеціальні</span><br>
				<span class="sales__title-normal">пропозиції</span>
			</h2>
		</div>

		<div class="sales__right">
			<div class="sales__content">
				<img class="sales__icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/235.svg" alt="">
				<p class="sales__text">
					Ми любимо тішити своїх клієнтів! Тут зібрані<br>
					найсвіжіші пропозиції, знижки й подарунки, що<br>
					зроблять ваші покупки ще приємнішими
				</p>
			</div>
		</div>
	</div>

	<div class="sales-cards container">
		<article class="sales-card">
			<div class="sales-card__media">
				<img
					src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-64_result.webp"
					alt="Еко-сумка зі свіжими овочами"
					loading="lazy"
				>
			</div>
			<div class="sales-card__content">
				<h3 class="sales-card__title">
					Безкоштовна доставка по Києву при замовленні продуктів через сайт
				</h3>
				<p class="sales-card__description">
					Замовляйте свіжі овочі, фрукти та товари<br>
					щоденного вжитку — ми привеземо їх до<br>
					вас <span class="sales-card__promo">без додаткової оплати</span>
				</p>
				<div class="sales-card__actions">
					<?php 
					$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
					?>
					<a class="sales-card__button" href="<?php echo esc_url($catalog_url); ?>">До каталогу</a>
					<span class="sales-card__note"><img class="sales-card__note-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/alarm_24dp_303030_fill1_wght300_grad0_opsz24-1.svg" alt=""><span class="sales-card__note-text">Акція діє до 23 жовтня</span></span>
				</div>
			</div>
		</article>

		<article class="sales-card">
			<div class="sales-card__media">
				<img
					src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-65_result.webp"
					alt="Знижка десять відсотків"
					loading="lazy"
				>
			</div>
			<div class="sales-card__content">
				<h3 class="sales-card__title">
					Ваше знайомство з нами ще приємніше — мінус 10% на перше замовлення
				</h3>
				<p class="sales-card__description">
					Замовляйте продукти через<br>
					сайт та отримайте миттєву знижку<br>
					<span class="sales-card__promo">-10% з промокодом <span class="sales-card__promo-code" data-promo-code="WELCOME10">WELCOME10</span> <img class="sales-card__copy-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/vector-11.svg" alt="Копіювати" width="13" height="15"></span>
				</p>
				<div class="sales-card__actions">
					<?php 
					$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
					?>
					<a class="sales-card__button" href="<?php echo esc_url($catalog_url); ?>">До каталогу</a>
					<span class="sales-card__note"><img class="sales-card__note-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/alarm_24dp_303030_fill1_wght300_grad0_opsz24-1.svg" alt=""><span class="sales-card__note-text">Акція діє до 30 жовтня</span></span>
				</div>
			</div>
		</article>
	</div>
</section>

<?php
// Featured products section
$sales_product_ids = natura_get_sales_products();

if ( ! empty( $sales_product_ids ) && class_exists( 'WooCommerce' ) ) {
	$products = array();
	foreach ( $sales_product_ids as $product_id ) {
		$product = wc_get_product( $product_id );
		if ( $product && $product->is_visible() ) {
			$products[] = $product;
		}
	}

	if ( ! empty( $products ) ) {
		$carousel_id = 'sales-products-carousel-' . wp_unique_id();
		?>
		<section class="related products single-product__related-section sales-related-products">
			<div class="container">
				<h2 class="single-product__related-heading">Акційні пропозиції</h2>
				<div
					id="<?php echo esc_attr( $carousel_id ); ?>"
					class="swiper single-product__related-swiper"
					data-swiper
				>
					<ul class="products columns-4 swiper-wrapper single-product__related-wrapper">
						<?php foreach ( $products as $product ) : ?>
							<?php
							$post_object = get_post( $product->get_id() );
							setup_postdata( $GLOBALS['post'] = $post_object ); // phpcs:ignore WordPress.WP.GlobalVariablesOverride.Prohibited, Squiz.PHP.DisallowMultipleAssignments.Found
							?>
							<?php wc_get_template_part( 'content', 'product' ); ?>
						<?php endforeach; ?>
					</ul>
				</div>
			</div>
		</section>
		<?php
		wp_reset_postdata();
	}
}
?>
