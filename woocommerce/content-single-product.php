<?php
/**
 * The Template for displaying all single products
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

global $product;
?>

<div id="product-<?php the_ID(); ?>" <?php wc_product_class( 'single-product__content', $product ); ?>>
	
	<div class="single-product__layout">
		<div class="single-product__image-wrapper">
			<?php
			/**
			 * Hook: woocommerce_before_single_product_summary.
			 *
			 * @hooked woocommerce_show_product_sale_flash - 10
			 * @hooked woocommerce_show_product_images - 20
			 */
			do_action( 'woocommerce_before_single_product_summary' );
			?>
		</div>

		<div class="single-product__summary">
			<?php
			// Бейдж наличия товара
			$is_in_stock = $product->is_in_stock();
			$stock_class = $is_in_stock ? '' : ' single-product__stock-badge--outofstock';
			?>
			<div class="single-product__stock-badge<?php echo esc_attr( $stock_class ); ?>">
				<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/ellipse-45.svg" alt="" class="single-product__stock-icon">
				<span class="single-product__stock-text">
					<?php echo $is_in_stock ? 'Товар в наявності' : 'Товар не в наявності'; ?>
				</span>
			</div>

			<div class="single-product__title-price-wrapper">
				<?php
				// Output title
				woocommerce_template_single_title();
				// Output price
				woocommerce_template_single_price();
				?>
			</div>

			<?php
			// Output short description immediately after title
			woocommerce_template_single_excerpt();
			
			// Output full product description (from tabs)
			$product_description = $product->get_description();
			if ( ! empty( $product_description ) ) {
				?>
				<div class="single-product__full-description">
					<?php echo apply_filters( 'the_content', $product_description ); ?>
				</div>
				<?php
			}
			?>

			<?php
			/**
			 * Hook: woocommerce_single_product_summary.
			 * Note: Title, price, excerpt and add to cart are output separately, so we remove them from the hook
			 *
			 * @hooked woocommerce_template_single_rating - 10
			 * @hooked woocommerce_template_single_excerpt - 20 (output above)
			 * @hooked woocommerce_template_single_add_to_cart - 30 (output below)
			 * @hooked woocommerce_template_single_meta - 40 (removed in functions.php)
			 * @hooked woocommerce_template_single_sharing - 50
			 * @hooked WC_Structured_Data::generate_product_data() - 60
			 */
			// Remove title, price, excerpt and add to cart from default hooks
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_excerpt', 20 );
			remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
			
			do_action( 'woocommerce_single_product_summary' );
			?>

			<?php
			// Output add to cart form at the bottom, after description
			woocommerce_template_single_add_to_cart();
			?>
		</div>
	</div>

	<?php
	/**
	 * Hook: woocommerce_after_single_product_summary.
	 *
	 * @hooked woocommerce_output_product_data_tabs - 10 (removed in functions.php)
	 * @hooked woocommerce_upsell_display - 15
	 * @hooked woocommerce_output_related_products - 20
	 */
	do_action( 'woocommerce_after_single_product_summary' );
	?>
</div>


<?php do_action( 'woocommerce_after_single_product' ); ?>




