<?php
/**
 * Sales page featured products settings
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add admin menu for sales page products
 */
function natura_sales_products_admin_menu() {
	add_submenu_page(
		'themes.php',
		__( 'Товари на сторінці акцій', 'natura' ),
		__( 'Товари акцій', 'natura' ),
		'manage_options',
		'natura-sales-products',
		'natura_sales_products_admin_page'
	);
}
add_action( 'admin_menu', 'natura_sales_products_admin_menu' );

/**
 * Admin page for selecting sales products
 */
function natura_sales_products_admin_page() {
	if ( ! current_user_can( 'manage_options' ) ) {
		return;
	}

	// Handle form submission
	if ( isset( $_POST['natura_sales_products_save'] ) && check_admin_referer( 'natura_sales_products_save' ) ) {
		$product_ids = isset( $_POST['sales_products'] ) ? array_map( 'intval', $_POST['sales_products'] ) : array();
		update_option( 'natura_sales_products', $product_ids );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Налаштування збережено!', 'natura' ) . '</p></div>';
	}

	$saved_products = get_option( 'natura_sales_products', array() );
	if ( ! is_array( $saved_products ) ) {
		$saved_products = array();
	}

	// Get all products for selection
	$all_products = get_posts( array(
		'post_type'      => 'product',
		'posts_per_page' => -1,
		'post_status'    => 'publish',
		'orderby'        => 'title',
		'order'          => 'ASC',
	) );
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Товари на сторінці акцій', 'natura' ); ?></h1>
		<p><?php echo esc_html__( 'Виберіть товари, які будуть відображатися на сторінці акцій. Можна вибрати 4 або 8 товарів.', 'natura' ); ?></p>
		
		<form method="post" action="">
			<?php wp_nonce_field( 'natura_sales_products_save' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="sales_products"><?php echo esc_html__( 'Вибрані товари', 'natura' ); ?></label>
					</th>
					<td>
						<select name="sales_products[]" id="sales_products" multiple="multiple" style="width: 100%; min-height: 300px;">
							<?php foreach ( $all_products as $product ) : ?>
								<?php
								$wc_product = wc_get_product( $product->ID );
								if ( ! $wc_product ) {
									continue;
								}
								$selected = in_array( $product->ID, $saved_products, true ) ? 'selected' : '';
								?>
								<option value="<?php echo esc_attr( $product->ID ); ?>" <?php echo esc_attr( $selected ); ?>>
									<?php echo esc_html( $product->post_title . ' (ID: ' . $product->ID . ')' ); ?>
								</option>
							<?php endforeach; ?>
						</select>
						<p class="description">
							<?php echo esc_html__( 'Утримуйте Ctrl (Cmd на Mac) для вибору кількох товарів. Рекомендується вибрати 4 або 8 товарів.', 'natura' ); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<?php submit_button( __( 'Зберегти', 'natura' ), 'primary', 'natura_sales_products_save' ); ?>
		</form>
	</div>
	<?php
}

/**
 * Get sales page featured products
 *
 * @return array Array of product IDs
 */
function natura_get_sales_products() {
	$product_ids = get_option( 'natura_sales_products', array() );
	if ( ! is_array( $product_ids ) ) {
		return array();
	}
	return array_filter( array_map( 'intval', $product_ids ) );
}
