<?php
/**
 * User-specific pricing functionality
 * Allows admins to set custom prices for specific users
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Add admin menu for user pricing
 */
function natura_user_pricing_menu() {
	add_menu_page(
		__( 'Ціни для користувачів', 'natura' ),
		__( 'Ціни користувачів', 'natura' ),
		'manage_woocommerce',
		'natura-user-pricing',
		'natura_user_pricing_page',
		'dashicons-tag',
		27
	);
}
add_action( 'admin_menu', 'natura_user_pricing_menu' );

/**
 * Enqueue admin styles and scripts
 */
function natura_user_pricing_admin_assets( $hook ) {
	if ( 'woocommerce_page_natura-user-pricing' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', array(), '4.0.3' );
	wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array( 'jquery' ), '4.0.3', true );

	wp_add_inline_style( 'select2', '
		.natura-user-pricing-wrap { max-width: 1200px; margin-top: 20px; }
		.natura-user-pricing-wrap h1 { margin-bottom: 20px; }
		.natura-pricing-form { background: #fff; padding: 20px; border: 1px solid #ccd0d4; margin-bottom: 20px; }
		.natura-pricing-form .form-row { margin-bottom: 15px; display: flex; align-items: center; gap: 15px; flex-wrap: wrap; }
		.natura-pricing-form label { font-weight: 600; min-width: 150px; }
		.natura-pricing-form .select2-container { min-width: 300px !important; }
		.natura-pricing-form input[type="number"] { width: 150px; }
		.natura-pricing-table { width: 100%; border-collapse: collapse; background: #fff; }
		.natura-pricing-table th, .natura-pricing-table td { padding: 12px; text-align: left; border: 1px solid #ccd0d4; }
		.natura-pricing-table th { background: #f1f1f1; font-weight: 600; }
		.natura-pricing-table tr:hover { background: #f9f9f9; }
		.natura-pricing-table .price-original { color: #999; text-decoration: line-through; }
		.natura-pricing-table .price-custom { color: #0073aa; font-weight: 600; }
		.natura-pricing-table .actions { white-space: nowrap; }
		.natura-pricing-table .delete-price { color: #a00; text-decoration: none; }
		.natura-pricing-table .delete-price:hover { color: #dc3232; }
		.natura-notice { padding: 10px 15px; margin-bottom: 20px; border-left: 4px solid; }
		.natura-notice.success { background: #d4edda; border-color: #28a745; }
		.natura-notice.error { background: #f8d7da; border-color: #dc3545; }
		.natura-filters { margin-bottom: 20px; display: flex; gap: 15px; align-items: center; }
		.natura-bulk-section { background: #fff3cd; padding: 15px; border: 1px solid #ffc107; margin-bottom: 20px; border-radius: 4px; }
		.natura-bulk-section h3 { margin-top: 0; }
	' );

	wp_add_inline_script( 'select2', '
		jQuery(document).ready(function($) {
			$(".natura-select2-users").select2({
				placeholder: "Виберіть користувача...",
				allowClear: true,
				ajax: {
					url: ajaxurl,
					dataType: "json",
					delay: 250,
					data: function(params) {
						return {
							action: "natura_search_users",
							term: params.term,
							nonce: naturaUserPricing.nonce
						};
					},
					processResults: function(data) {
						return { results: data };
					},
					cache: true
				},
				minimumInputLength: 2
			});

			$(".natura-select2-products").select2({
				placeholder: "Виберіть товар...",
				allowClear: true,
				ajax: {
					url: ajaxurl,
					dataType: "json",
					delay: 250,
					data: function(params) {
						return {
							action: "natura_search_products",
							term: params.term,
							nonce: naturaUserPricing.nonce
						};
					},
					processResults: function(data) {
						return { results: data };
					},
					cache: true
				},
				minimumInputLength: 2
			});

			$(".natura-select2-categories").select2({
				placeholder: "Виберіть категорію...",
				allowClear: true
			});

			$(".natura-filter-user").select2({
				placeholder: "Фільтр за користувачем...",
				allowClear: true,
				ajax: {
					url: ajaxurl,
					dataType: "json",
					delay: 250,
					data: function(params) {
						return {
							action: "natura_search_users",
							term: params.term,
							nonce: naturaUserPricing.nonce
						};
					},
					processResults: function(data) {
						return { results: data };
					},
					cache: true
				},
				minimumInputLength: 2
			});
		});
	' );

	wp_localize_script( 'select2', 'naturaUserPricing', array(
		'nonce' => wp_create_nonce( 'natura_user_pricing_nonce' ),
	) );
}
add_action( 'admin_enqueue_scripts', 'natura_user_pricing_admin_assets' );

/**
 * AJAX: Search users
 */
function natura_ajax_search_users() {
	check_ajax_referer( 'natura_user_pricing_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_send_json( array() );
	}

	$term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';

	$users = get_users( array(
		'search'         => '*' . $term . '*',
		'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
		'number'         => 20,
		'orderby'        => 'display_name',
		'order'          => 'ASC',
	) );

	$results = array();
	foreach ( $users as $user ) {
		$results[] = array(
			'id'   => $user->ID,
			'text' => sprintf( '%s (%s)', $user->display_name, $user->user_email ),
		);
	}

	wp_send_json( $results );
}
add_action( 'wp_ajax_natura_search_users', 'natura_ajax_search_users' );

/**
 * AJAX: Search products
 */
function natura_ajax_search_products() {
	check_ajax_referer( 'natura_user_pricing_nonce', 'nonce' );

	if ( ! current_user_can( 'manage_woocommerce' ) ) {
		wp_send_json( array() );
	}

	$term = isset( $_GET['term'] ) ? sanitize_text_field( $_GET['term'] ) : '';

	$products = wc_get_products( array(
		's'       => $term,
		'limit'   => 20,
		'status'  => 'publish',
		'orderby' => 'title',
		'order'   => 'ASC',
	) );

	$results = array();
	foreach ( $products as $product ) {
		$results[] = array(
			'id'   => $product->get_id(),
			'text' => sprintf( '%s (₴%s)', $product->get_name(), $product->get_price() ),
		);
	}

	wp_send_json( $results );
}
add_action( 'wp_ajax_natura_search_products', 'natura_ajax_search_products' );

/**
 * Get user custom prices
 */
function natura_get_user_prices( $user_id = null ) {
	$prices = get_option( 'natura_user_prices', array() );

	if ( $user_id ) {
		return isset( $prices[ $user_id ] ) ? $prices[ $user_id ] : array();
	}

	return $prices;
}

/**
 * Save user custom price
 */
function natura_save_user_price( $user_id, $product_id, $price ) {
	$prices = get_option( 'natura_user_prices', array() );

	if ( ! isset( $prices[ $user_id ] ) ) {
		$prices[ $user_id ] = array();
	}

	$prices[ $user_id ][ $product_id ] = floatval( $price );

	update_option( 'natura_user_prices', $prices );
}

/**
 * Delete user custom price
 */
function natura_delete_user_price( $user_id, $product_id ) {
	$prices = get_option( 'natura_user_prices', array() );

	if ( isset( $prices[ $user_id ][ $product_id ] ) ) {
		unset( $prices[ $user_id ][ $product_id ] );

		// Remove user entry if no prices left
		if ( empty( $prices[ $user_id ] ) ) {
			unset( $prices[ $user_id ] );
		}

		update_option( 'natura_user_prices', $prices );
	}
}

/**
 * Get user discount percentage
 */
function natura_get_user_discount( $user_id ) {
	$discounts = get_option( 'natura_user_discounts', array() );
	return isset( $discounts[ $user_id ] ) ? floatval( $discounts[ $user_id ] ) : 0;
}

/**
 * Save user discount percentage
 */
function natura_save_user_discount( $user_id, $discount ) {
	$discounts = get_option( 'natura_user_discounts', array() );
	$discounts[ $user_id ] = floatval( $discount );
	update_option( 'natura_user_discounts', $discounts );
}

/**
 * Delete user discount
 */
function natura_delete_user_discount( $user_id ) {
	$discounts = get_option( 'natura_user_discounts', array() );
	if ( isset( $discounts[ $user_id ] ) ) {
		unset( $discounts[ $user_id ] );
		update_option( 'natura_user_discounts', $discounts );
	}
}

/**
 * Admin page
 */
function natura_user_pricing_page() {
	// Handle form submissions
	$notice = '';
	$notice_type = '';

	// Add individual price
	if ( isset( $_POST['natura_add_price'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'natura_add_user_price' ) ) {
		$user_id    = intval( $_POST['user_id'] );
		$product_id = intval( $_POST['product_id'] );
		$price      = floatval( $_POST['custom_price'] );

		if ( $user_id && $product_id && $price >= 0 ) {
			natura_save_user_price( $user_id, $product_id, $price );
			$notice = __( 'Ціну успішно збережено!', 'natura' );
			$notice_type = 'success';
		} else {
			$notice = __( 'Помилка: перевірте введені дані.', 'natura' );
			$notice_type = 'error';
		}
	}

	// Add percentage discount
	if ( isset( $_POST['natura_add_discount'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'natura_add_user_discount' ) ) {
		$user_id  = intval( $_POST['discount_user_id'] );
		$discount = floatval( $_POST['discount_percent'] );

		if ( $user_id && $discount > 0 && $discount <= 100 ) {
			natura_save_user_discount( $user_id, $discount );
			$notice = __( 'Знижку успішно збережено!', 'natura' );
			$notice_type = 'success';
		} else {
			$notice = __( 'Помилка: перевірте введені дані (знижка від 1 до 100%).', 'natura' );
			$notice_type = 'error';
		}
	}

	// Delete price
	if ( isset( $_GET['delete_price'] ) && isset( $_GET['user'] ) && isset( $_GET['product'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'natura_delete_price' ) ) {
		natura_delete_user_price( intval( $_GET['user'] ), intval( $_GET['product'] ) );
		$notice = __( 'Ціну видалено!', 'natura' );
		$notice_type = 'success';
	}

	// Delete discount
	if ( isset( $_GET['delete_discount'] ) && isset( $_GET['user'] ) && wp_verify_nonce( $_GET['_wpnonce'], 'natura_delete_discount' ) ) {
		natura_delete_user_discount( intval( $_GET['user'] ) );
		$notice = __( 'Знижку видалено!', 'natura' );
		$notice_type = 'success';
	}

	$all_prices = natura_get_user_prices();
	$all_discounts = get_option( 'natura_user_discounts', array() );

	// Filter by user
	$filter_user = isset( $_GET['filter_user'] ) ? intval( $_GET['filter_user'] ) : 0;
	?>
	<div class="wrap natura-user-pricing-wrap">
		<h1><?php esc_html_e( 'Ціни для користувачів', 'natura' ); ?></h1>

		<?php if ( $notice ) : ?>
			<div class="natura-notice <?php echo esc_attr( $notice_type ); ?>">
				<?php echo esc_html( $notice ); ?>
			</div>
		<?php endif; ?>

		<!-- Tab navigation -->
		<h2 class="nav-tab-wrapper">
			<a href="#individual-prices" class="nav-tab nav-tab-active" data-tab="individual-prices">
				<?php esc_html_e( 'Індивідуальні ціни', 'natura' ); ?>
			</a>
			<a href="#percentage-discounts" class="nav-tab" data-tab="percentage-discounts">
				<?php esc_html_e( 'Відсоткові знижки', 'natura' ); ?>
			</a>
		</h2>

		<!-- Individual Prices Tab -->
		<div id="individual-prices" class="tab-content" style="display: block;">
			<div class="natura-pricing-form">
				<h3><?php esc_html_e( 'Додати індивідуальну ціну', 'natura' ); ?></h3>
				<form method="post">
					<?php wp_nonce_field( 'natura_add_user_price' ); ?>
					<div class="form-row">
						<label for="user_id"><?php esc_html_e( 'Користувач:', 'natura' ); ?></label>
						<select name="user_id" id="user_id" class="natura-select2-users" required>
							<option value=""><?php esc_html_e( 'Виберіть користувача...', 'natura' ); ?></option>
						</select>
					</div>
					<div class="form-row">
						<label for="product_id"><?php esc_html_e( 'Товар:', 'natura' ); ?></label>
						<select name="product_id" id="product_id" class="natura-select2-products" required>
							<option value=""><?php esc_html_e( 'Виберіть товар...', 'natura' ); ?></option>
						</select>
					</div>
					<div class="form-row">
						<label for="custom_price"><?php esc_html_e( 'Ціна (₴):', 'natura' ); ?></label>
						<input type="number" name="custom_price" id="custom_price" step="0.01" min="0" required>
					</div>
					<div class="form-row">
						<label></label>
						<button type="submit" name="natura_add_price" class="button button-primary">
							<?php esc_html_e( 'Зберегти ціну', 'natura' ); ?>
						</button>
					</div>
				</form>
			</div>

			<h3><?php esc_html_e( 'Поточні індивідуальні ціни', 'natura' ); ?></h3>

			<?php if ( ! empty( $all_prices ) ) : ?>
				<table class="natura-pricing-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Користувач', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Товар', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Звичайна ціна', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Індивідуальна ціна', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Дії', 'natura' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $all_prices as $user_id => $products ) :
							$user = get_user_by( 'id', $user_id );
							if ( ! $user ) {
								continue;
							}

							foreach ( $products as $product_id => $custom_price ) :
								$product = wc_get_product( $product_id );
								if ( ! $product ) {
									continue;
								}

								$delete_url = wp_nonce_url(
									add_query_arg( array(
										'delete_price' => 1,
										'user'         => $user_id,
										'product'      => $product_id,
									) ),
									'natura_delete_price'
								);
								?>
								<tr>
									<td>
										<?php echo esc_html( $user->display_name ); ?>
										<br><small><?php echo esc_html( $user->user_email ); ?></small>
									</td>
									<td>
										<a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" target="_blank">
											<?php echo esc_html( $product->get_name() ); ?>
										</a>
									</td>
									<td class="price-original">₴<?php echo esc_html( $product->get_regular_price() ); ?></td>
									<td class="price-custom">₴<?php echo esc_html( number_format( $custom_price, 2 ) ); ?></td>
									<td class="actions">
										<a href="<?php echo esc_url( $delete_url ); ?>" class="delete-price" onclick="return confirm('<?php esc_attr_e( 'Видалити цю ціну?', 'natura' ); ?>');">
											<?php esc_html_e( 'Видалити', 'natura' ); ?>
										</a>
									</td>
								</tr>
								<?php
							endforeach;
						endforeach;
						?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'Індивідуальних цін поки немає.', 'natura' ); ?></p>
			<?php endif; ?>
		</div>

		<!-- Percentage Discounts Tab -->
		<div id="percentage-discounts" class="tab-content" style="display: none;">
			<div class="natura-pricing-form">
				<h3><?php esc_html_e( 'Додати відсоткову знижку для користувача', 'natura' ); ?></h3>
				<p class="description"><?php esc_html_e( 'Знижка застосовується до всіх товарів для цього користувача (якщо немає індивідуальної ціни).', 'natura' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'natura_add_user_discount' ); ?>
					<div class="form-row">
						<label for="discount_user_id"><?php esc_html_e( 'Користувач:', 'natura' ); ?></label>
						<select name="discount_user_id" id="discount_user_id" class="natura-select2-users" required>
							<option value=""><?php esc_html_e( 'Виберіть користувача...', 'natura' ); ?></option>
						</select>
					</div>
					<div class="form-row">
						<label for="discount_percent"><?php esc_html_e( 'Знижка (%):', 'natura' ); ?></label>
						<input type="number" name="discount_percent" id="discount_percent" step="0.1" min="0.1" max="100" required>
					</div>
					<div class="form-row">
						<label></label>
						<button type="submit" name="natura_add_discount" class="button button-primary">
							<?php esc_html_e( 'Зберегти знижку', 'natura' ); ?>
						</button>
					</div>
				</form>
			</div>

			<h3><?php esc_html_e( 'Поточні відсоткові знижки', 'natura' ); ?></h3>

			<?php if ( ! empty( $all_discounts ) ) : ?>
				<table class="natura-pricing-table">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Користувач', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Email', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Знижка', 'natura' ); ?></th>
							<th><?php esc_html_e( 'Дії', 'natura' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php
						foreach ( $all_discounts as $user_id => $discount ) :
							$user = get_user_by( 'id', $user_id );
							if ( ! $user ) {
								continue;
							}

							$delete_url = wp_nonce_url(
								add_query_arg( array(
									'delete_discount' => 1,
									'user'            => $user_id,
								) ),
								'natura_delete_discount'
							);
							?>
							<tr>
								<td><?php echo esc_html( $user->display_name ); ?></td>
								<td><?php echo esc_html( $user->user_email ); ?></td>
								<td class="price-custom"><?php echo esc_html( $discount ); ?>%</td>
								<td class="actions">
									<a href="<?php echo esc_url( $delete_url ); ?>" class="delete-price" onclick="return confirm('<?php esc_attr_e( 'Видалити цю знижку?', 'natura' ); ?>');">
										<?php esc_html_e( 'Видалити', 'natura' ); ?>
									</a>
								</td>
							</tr>
							<?php
						endforeach;
						?>
					</tbody>
				</table>
			<?php else : ?>
				<p><?php esc_html_e( 'Відсоткових знижок поки немає.', 'natura' ); ?></p>
			<?php endif; ?>
		</div>

		<script>
		jQuery(document).ready(function($) {
			$('.nav-tab').on('click', function(e) {
				e.preventDefault();
				var tab = $(this).data('tab');
				
				$('.nav-tab').removeClass('nav-tab-active');
				$(this).addClass('nav-tab-active');
				
				$('.tab-content').hide();
				$('#' + tab).show();
			});
		});
		</script>
	</div>
	<?php
}

/**
 * Apply custom prices on frontend
 */
function natura_apply_custom_price( $price, $product ) {
	if ( ! is_user_logged_in() || is_admin() ) {
		return $price;
	}

	$user_id    = get_current_user_id();
	$product_id = $product->get_id();

	// Check for individual price first
	$user_prices = natura_get_user_prices( $user_id );
	if ( isset( $user_prices[ $product_id ] ) ) {
		return $user_prices[ $product_id ];
	}

	// Check for percentage discount
	$discount = natura_get_user_discount( $user_id );
	if ( $discount > 0 ) {
		$discounted_price = $price * ( 1 - $discount / 100 );
		return round( $discounted_price, 2 );
	}

	return $price;
}
add_filter( 'woocommerce_product_get_price', 'natura_apply_custom_price', 100, 2 );
add_filter( 'woocommerce_product_get_regular_price', 'natura_apply_custom_price', 100, 2 );

/**
 * Apply custom prices in cart
 */
function natura_apply_custom_cart_price( $cart ) {
	if ( is_admin() && ! defined( 'DOING_AJAX' ) ) {
		return;
	}

	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id     = get_current_user_id();
	$user_prices = natura_get_user_prices( $user_id );
	$discount    = natura_get_user_discount( $user_id );

	foreach ( $cart->get_cart() as $cart_item_key => $cart_item ) {
		$product_id = $cart_item['product_id'];
		$product    = $cart_item['data'];

		// Individual price takes priority
		if ( isset( $user_prices[ $product_id ] ) ) {
			$cart_item['data']->set_price( $user_prices[ $product_id ] );
		} elseif ( $discount > 0 ) {
			// Apply percentage discount
			$original_price   = $product->get_regular_price();
			$discounted_price = $original_price * ( 1 - $discount / 100 );
			$cart_item['data']->set_price( round( $discounted_price, 2 ) );
		}
	}
}
add_action( 'woocommerce_before_calculate_totals', 'natura_apply_custom_cart_price', 100 );

/**
 * Show custom price notice to user
 */
function natura_show_custom_price_notice() {
	if ( ! is_user_logged_in() || ! is_product() ) {
		return;
	}

	$user_id     = get_current_user_id();
	$user_prices = natura_get_user_prices( $user_id );
	$discount    = natura_get_user_discount( $user_id );

	global $product;
	$product_id = $product->get_id();

	if ( isset( $user_prices[ $product_id ] ) ) {
		echo '<div class="woocommerce-message" style="margin-bottom: 15px;">';
		echo esc_html__( 'Для вас встановлено індивідуальну ціну на цей товар!', 'natura' );
		echo '</div>';
	} elseif ( $discount > 0 ) {
		echo '<div class="woocommerce-message" style="margin-bottom: 15px;">';
		printf(
			esc_html__( 'Ваша персональна знижка: %s%%', 'natura' ),
			esc_html( $discount )
		);
		echo '</div>';
	}
}
add_action( 'woocommerce_before_single_product', 'natura_show_custom_price_notice' );

/**
 * Add user discount info to My Account
 */
function natura_show_discount_in_account() {
	if ( ! is_user_logged_in() ) {
		return;
	}

	$user_id  = get_current_user_id();
	$discount = natura_get_user_discount( $user_id );

	if ( $discount > 0 ) {
		echo '<div class="woocommerce-message" style="margin-bottom: 20px;">';
		printf(
			esc_html__( 'Ваша персональна знижка на всі товари: %s%%', 'natura' ),
			esc_html( $discount )
		);
		echo '</div>';
	}
}
add_action( 'woocommerce_account_dashboard', 'natura_show_discount_in_account' );
