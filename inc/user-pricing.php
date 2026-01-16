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
	if ( 'toplevel_page_natura-user-pricing' !== $hook ) {
		return;
	}

	wp_enqueue_style( 'select2', WC()->plugin_url() . '/assets/css/select2.css', array(), '4.0.3' );
	wp_enqueue_script( 'select2', WC()->plugin_url() . '/assets/js/select2/select2.full.min.js', array( 'jquery' ), '4.0.3', true );

	// Natura-style admin CSS
	wp_add_inline_style( 'select2', '
		/* Natura Admin Theme */
		.natura-admin-wrap {
			max-width: 1400px;
			margin: 20px auto;
			padding: 0 20px;
			font-family: "Inter", -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
		}
		
		.natura-admin-wrap * {
			box-sizing: border-box;
		}
		
		.natura-admin-header {
			background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
			color: #fff;
			padding: 30px 40px;
			border-radius: 16px;
			margin-bottom: 30px;
			box-shadow: 0 4px 20px rgba(76, 175, 80, 0.3);
		}
		
		.natura-admin-header h1 {
			margin: 0 0 8px 0;
			font-size: 28px;
			font-weight: 700;
			color: #fff;
		}
		
		.natura-admin-header p {
			margin: 0;
			opacity: 0.9;
			font-size: 15px;
		}
		
		/* Tabs */
		.natura-tabs {
			display: flex;
			gap: 0;
			margin-bottom: 0;
			border-bottom: none;
		}
		
		.natura-tab {
			padding: 16px 32px;
			background: #f5f5f5;
			border: none;
			border-radius: 12px 12px 0 0;
			cursor: pointer;
			font-size: 15px;
			font-weight: 600;
			color: #666;
			transition: all 0.3s ease;
			margin-right: 4px;
		}
		
		.natura-tab:hover {
			background: #e8e8e8;
			color: #333;
		}
		
		.natura-tab.active {
			background: #fff;
			color: #4CAF50;
			box-shadow: 0 -2px 10px rgba(0,0,0,0.05);
		}
		
		/* Tab Content */
		.natura-tab-content {
			display: none;
			background: #fff;
			border-radius: 0 16px 16px 16px;
			padding: 30px;
			box-shadow: 0 2px 20px rgba(0,0,0,0.08);
		}
		
		.natura-tab-content.active {
			display: block;
		}
		
		/* Form Card */
		.natura-form-card {
			background: linear-gradient(135deg, #f8fdf8 0%, #f0f7f0 100%);
			border: 1px solid #c8e6c9;
			border-radius: 12px;
			padding: 24px;
			margin-bottom: 30px;
		}
		
		.natura-form-card h3 {
			margin: 0 0 20px 0;
			font-size: 18px;
			font-weight: 600;
			color: #2E7D32;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		
		.natura-form-card h3::before {
			content: "+";
			display: inline-flex;
			align-items: center;
			justify-content: center;
			width: 28px;
			height: 28px;
			background: #4CAF50;
			color: #fff;
			border-radius: 50%;
			font-size: 18px;
			font-weight: 700;
		}
		
		.natura-form-grid {
			display: grid;
			grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
			gap: 20px;
			align-items: end;
		}
		
		.natura-form-group {
			display: flex;
			flex-direction: column;
			gap: 8px;
		}
		
		.natura-form-group label {
			font-weight: 600;
			font-size: 13px;
			color: #555;
			text-transform: uppercase;
			letter-spacing: 0.5px;
		}
		
		.natura-form-group select,
		.natura-form-group input[type="number"] {
			padding: 12px 16px;
			border: 2px solid #e0e0e0;
			border-radius: 10px;
			font-size: 15px;
			transition: all 0.3s ease;
			background: #fff;
			width: 100%;
		}
		
		.natura-form-group select:focus,
		.natura-form-group input[type="number"]:focus {
			border-color: #4CAF50;
			outline: none;
			box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1);
		}
		
		.natura-btn {
			padding: 14px 28px;
			border: none;
			border-radius: 10px;
			font-size: 15px;
			font-weight: 600;
			cursor: pointer;
			transition: all 0.3s ease;
			display: inline-flex;
			align-items: center;
			gap: 8px;
		}
		
		.natura-btn-primary {
			background: linear-gradient(135deg, #4CAF50 0%, #43A047 100%);
			color: #fff;
			box-shadow: 0 4px 15px rgba(76, 175, 80, 0.3);
		}
		
		.natura-btn-primary:hover {
			background: linear-gradient(135deg, #43A047 0%, #388E3C 100%);
			transform: translateY(-2px);
			box-shadow: 0 6px 20px rgba(76, 175, 80, 0.4);
		}
		
		/* Table */
		.natura-table-wrap {
			overflow-x: auto;
		}
		
		.natura-table {
			width: 100%;
			border-collapse: separate;
			border-spacing: 0;
		}
		
		.natura-table th {
			background: #f5f5f5;
			padding: 16px 20px;
			text-align: left;
			font-weight: 600;
			font-size: 13px;
			text-transform: uppercase;
			letter-spacing: 0.5px;
			color: #666;
			border-bottom: 2px solid #e0e0e0;
		}
		
		.natura-table th:first-child {
			border-radius: 10px 0 0 0;
		}
		
		.natura-table th:last-child {
			border-radius: 0 10px 0 0;
		}
		
		.natura-table td {
			padding: 16px 20px;
			border-bottom: 1px solid #f0f0f0;
			vertical-align: middle;
		}
		
		.natura-table tbody tr {
			transition: all 0.2s ease;
		}
		
		.natura-table tbody tr:hover {
			background: #f8fdf8;
		}
		
		.natura-table tbody tr:last-child td {
			border-bottom: none;
		}
		
		.natura-table tbody tr:last-child td:first-child {
			border-radius: 0 0 0 10px;
		}
		
		.natura-table tbody tr:last-child td:last-child {
			border-radius: 0 0 10px 0;
		}
		
		.natura-user-info {
			display: flex;
			align-items: center;
			gap: 12px;
		}
		
		.natura-user-avatar {
			width: 40px;
			height: 40px;
			border-radius: 50%;
			background: linear-gradient(135deg, #4CAF50 0%, #81C784 100%);
			display: flex;
			align-items: center;
			justify-content: center;
			color: #fff;
			font-weight: 700;
			font-size: 16px;
			flex-shrink: 0;
		}
		
		.natura-user-details {
			display: flex;
			flex-direction: column;
			gap: 2px;
		}
		
		.natura-user-name {
			font-weight: 600;
			color: #333;
		}
		
		.natura-user-email {
			font-size: 13px;
			color: #888;
		}
		
		.natura-price-original {
			color: #999;
			text-decoration: line-through;
			font-size: 14px;
		}
		
		.natura-price-custom {
			color: #4CAF50;
			font-weight: 700;
			font-size: 18px;
		}
		
		.natura-discount-badge {
			display: inline-flex;
			align-items: center;
			gap: 6px;
			background: linear-gradient(135deg, #4CAF50 0%, #66BB6A 100%);
			color: #fff;
			padding: 8px 16px;
			border-radius: 20px;
			font-weight: 700;
			font-size: 16px;
		}
		
		.natura-btn-delete {
			background: #fff;
			color: #e53935;
			border: 2px solid #ffcdd2;
			padding: 8px 16px;
			font-size: 13px;
		}
		
		.natura-btn-delete:hover {
			background: #ffebee;
			border-color: #e53935;
		}
		
		.natura-empty-state {
			text-align: center;
			padding: 60px 20px;
			color: #999;
		}
		
		.natura-empty-state svg {
			width: 80px;
			height: 80px;
			margin-bottom: 20px;
			opacity: 0.3;
		}
		
		.natura-empty-state p {
			font-size: 16px;
			margin: 0;
		}
		
		/* Notice */
		.natura-notice {
			padding: 16px 20px;
			border-radius: 10px;
			margin-bottom: 20px;
			display: flex;
			align-items: center;
			gap: 12px;
			font-weight: 500;
		}
		
		.natura-notice.success {
			background: #e8f5e9;
			color: #2E7D32;
			border: 1px solid #c8e6c9;
		}
		
		.natura-notice.error {
			background: #ffebee;
			color: #c62828;
			border: 1px solid #ffcdd2;
		}
		
		/* Select2 Custom Styles */
		.select2-container--default .select2-selection--single {
			height: 48px !important;
			border: 2px solid #e0e0e0 !important;
			border-radius: 10px !important;
			padding: 8px 12px !important;
		}
		
		.select2-container--default .select2-selection--single .select2-selection__rendered {
			line-height: 28px !important;
			padding-left: 4px !important;
			color: #333 !important;
		}
		
		.select2-container--default .select2-selection--single .select2-selection__arrow {
			height: 46px !important;
			right: 8px !important;
		}
		
		.select2-container--default.select2-container--focus .select2-selection--single {
			border-color: #4CAF50 !important;
			box-shadow: 0 0 0 3px rgba(76, 175, 80, 0.1) !important;
		}
		
		.select2-dropdown {
			border: 2px solid #e0e0e0 !important;
			border-radius: 10px !important;
			box-shadow: 0 8px 30px rgba(0,0,0,0.12) !important;
			margin-top: 4px !important;
		}
		
		.select2-results__option--highlighted[aria-selected] {
			background: #4CAF50 !important;
		}
		
		.section-title {
			font-size: 18px;
			font-weight: 600;
			color: #333;
			margin: 0 0 20px 0;
			display: flex;
			align-items: center;
			gap: 10px;
		}
		
		.section-title::before {
			content: "";
			display: block;
			width: 4px;
			height: 24px;
			background: #4CAF50;
			border-radius: 2px;
		}
		
		.form-description {
			color: #888;
			font-size: 14px;
			margin-bottom: 20px;
			padding-left: 14px;
			border-left: 3px solid #e0e0e0;
		}
	' );

	wp_add_inline_script( 'select2', '
		jQuery(document).ready(function($) {
			// Tab switching
			$(".natura-tab").on("click", function() {
				var tab = $(this).data("tab");
				$(".natura-tab").removeClass("active");
				$(this).addClass("active");
				$(".natura-tab-content").removeClass("active");
				$("#" + tab).addClass("active");
			});

			// Select2 for users
			$(".natura-select2-users").select2({
				placeholder: "Почніть вводити ім\'я або email...",
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
				minimumInputLength: 1
			});

			// Select2 for products
			$(".natura-select2-products").select2({
				placeholder: "Почніть вводити назву товару...",
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
				minimumInputLength: 1
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

	if ( empty( $term ) ) {
		// Return first 20 users if no search term
		$users = get_users( array(
			'number'  => 20,
			'orderby' => 'display_name',
			'order'   => 'ASC',
		) );
	} else {
		$users = get_users( array(
			'search'         => '*' . $term . '*',
			'search_columns' => array( 'user_login', 'user_email', 'display_name' ),
			'number'         => 20,
			'orderby'        => 'display_name',
			'order'          => 'ASC',
		) );
	}

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

	$args = array(
		'limit'   => 20,
		'status'  => 'publish',
		'orderby' => 'title',
		'order'   => 'ASC',
	);

	if ( ! empty( $term ) ) {
		$args['s'] = $term;
	}

	$products = wc_get_products( $args );

	$results = array();
	foreach ( $products as $product ) {
		$results[] = array(
			'id'   => $product->get_id(),
			'text' => sprintf( '%s — %s', $product->get_name(), wc_price( $product->get_price() ) ),
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
	?>
	<div class="natura-admin-wrap">
		<!-- Header -->
		<div class="natura-admin-header">
			<h1><?php esc_html_e( 'Ціни для користувачів', 'natura' ); ?></h1>
			<p><?php esc_html_e( 'Встановлюйте індивідуальні ціни та знижки для ваших клієнтів', 'natura' ); ?></p>
		</div>

		<?php if ( $notice ) : ?>
			<div class="natura-notice <?php echo esc_attr( $notice_type ); ?>">
				<?php if ( 'success' === $notice_type ) : ?>
					<span class="dashicons dashicons-yes-alt"></span>
				<?php else : ?>
					<span class="dashicons dashicons-warning"></span>
				<?php endif; ?>
				<?php echo esc_html( $notice ); ?>
			</div>
		<?php endif; ?>

		<!-- Tabs -->
		<div class="natura-tabs">
			<button type="button" class="natura-tab active" data-tab="individual-prices">
				<?php esc_html_e( 'Індивідуальні ціни', 'natura' ); ?>
			</button>
			<button type="button" class="natura-tab" data-tab="percentage-discounts">
				<?php esc_html_e( 'Відсоткові знижки', 'natura' ); ?>
			</button>
		</div>

		<!-- Individual Prices Tab -->
		<div id="individual-prices" class="natura-tab-content active">
			<div class="natura-form-card">
				<h3><?php esc_html_e( 'Додати індивідуальну ціну', 'natura' ); ?></h3>
				<form method="post">
					<?php wp_nonce_field( 'natura_add_user_price' ); ?>
					<div class="natura-form-grid">
						<div class="natura-form-group">
							<label for="user_id"><?php esc_html_e( 'Користувач', 'natura' ); ?></label>
							<select name="user_id" id="user_id" class="natura-select2-users" required style="width: 100%;">
								<option value=""><?php esc_html_e( 'Виберіть користувача...', 'natura' ); ?></option>
							</select>
						</div>
						<div class="natura-form-group">
							<label for="product_id"><?php esc_html_e( 'Товар', 'natura' ); ?></label>
							<select name="product_id" id="product_id" class="natura-select2-products" required style="width: 100%;">
								<option value=""><?php esc_html_e( 'Виберіть товар...', 'natura' ); ?></option>
							</select>
						</div>
						<div class="natura-form-group">
							<label for="custom_price"><?php esc_html_e( 'Ціна (₴)', 'natura' ); ?></label>
							<input type="number" name="custom_price" id="custom_price" step="0.01" min="0" required placeholder="0.00">
						</div>
						<div class="natura-form-group">
							<label>&nbsp;</label>
							<button type="submit" name="natura_add_price" class="natura-btn natura-btn-primary">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Зберегти', 'natura' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>

			<h3 class="section-title"><?php esc_html_e( 'Поточні індивідуальні ціни', 'natura' ); ?></h3>

			<?php if ( ! empty( $all_prices ) ) : ?>
				<div class="natura-table-wrap">
					<table class="natura-table">
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

								$initials = mb_strtoupper( mb_substr( $user->display_name, 0, 1 ) );

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
											<div class="natura-user-info">
												<div class="natura-user-avatar"><?php echo esc_html( $initials ); ?></div>
												<div class="natura-user-details">
													<span class="natura-user-name"><?php echo esc_html( $user->display_name ); ?></span>
													<span class="natura-user-email"><?php echo esc_html( $user->user_email ); ?></span>
												</div>
											</div>
										</td>
										<td>
											<a href="<?php echo esc_url( get_edit_post_link( $product_id ) ); ?>" target="_blank" style="color: #333; text-decoration: none; font-weight: 500;">
												<?php echo esc_html( $product->get_name() ); ?>
											</a>
										</td>
										<td><span class="natura-price-original"><?php echo wc_price( $product->get_regular_price() ); ?></span></td>
										<td><span class="natura-price-custom"><?php echo wc_price( $custom_price ); ?></span></td>
										<td>
											<a href="<?php echo esc_url( $delete_url ); ?>" class="natura-btn natura-btn-delete" onclick="return confirm('<?php esc_attr_e( 'Видалити цю ціну?', 'natura' ); ?>');">
												<span class="dashicons dashicons-trash"></span>
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
				</div>
			<?php else : ?>
				<div class="natura-empty-state">
					<span class="dashicons dashicons-tag" style="font-size: 60px; width: 60px; height: 60px; color: #ccc;"></span>
					<p><?php esc_html_e( 'Індивідуальних цін поки немає', 'natura' ); ?></p>
				</div>
			<?php endif; ?>
		</div>

		<!-- Percentage Discounts Tab -->
		<div id="percentage-discounts" class="natura-tab-content">
			<div class="natura-form-card">
				<h3><?php esc_html_e( 'Додати відсоткову знижку', 'natura' ); ?></h3>
				<p class="form-description"><?php esc_html_e( 'Знижка застосовується автоматично до всіх товарів для цього користувача (якщо немає індивідуальної ціни на товар).', 'natura' ); ?></p>
				<form method="post">
					<?php wp_nonce_field( 'natura_add_user_discount' ); ?>
					<div class="natura-form-grid">
						<div class="natura-form-group">
							<label for="discount_user_id"><?php esc_html_e( 'Користувач', 'natura' ); ?></label>
							<select name="discount_user_id" id="discount_user_id" class="natura-select2-users" required style="width: 100%;">
								<option value=""><?php esc_html_e( 'Виберіть користувача...', 'natura' ); ?></option>
							</select>
						</div>
						<div class="natura-form-group">
							<label for="discount_percent"><?php esc_html_e( 'Знижка (%)', 'natura' ); ?></label>
							<input type="number" name="discount_percent" id="discount_percent" step="0.1" min="0.1" max="100" required placeholder="10">
						</div>
						<div class="natura-form-group">
							<label>&nbsp;</label>
							<button type="submit" name="natura_add_discount" class="natura-btn natura-btn-primary">
								<span class="dashicons dashicons-plus-alt2"></span>
								<?php esc_html_e( 'Зберегти', 'natura' ); ?>
							</button>
						</div>
					</div>
				</form>
			</div>

			<h3 class="section-title"><?php esc_html_e( 'Поточні відсоткові знижки', 'natura' ); ?></h3>

			<?php if ( ! empty( $all_discounts ) ) : ?>
				<div class="natura-table-wrap">
					<table class="natura-table">
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

								$initials = mb_strtoupper( mb_substr( $user->display_name, 0, 1 ) );

								$delete_url = wp_nonce_url(
									add_query_arg( array(
										'delete_discount' => 1,
										'user'            => $user_id,
									) ),
									'natura_delete_discount'
								);
								?>
								<tr>
									<td>
										<div class="natura-user-info">
											<div class="natura-user-avatar"><?php echo esc_html( $initials ); ?></div>
											<div class="natura-user-details">
												<span class="natura-user-name"><?php echo esc_html( $user->display_name ); ?></span>
											</div>
										</div>
									</td>
									<td><?php echo esc_html( $user->user_email ); ?></td>
									<td>
										<span class="natura-discount-badge">
											<span class="dashicons dashicons-ticket-alt"></span>
											<?php echo esc_html( $discount ); ?>%
										</span>
									</td>
									<td>
										<a href="<?php echo esc_url( $delete_url ); ?>" class="natura-btn natura-btn-delete" onclick="return confirm('<?php esc_attr_e( 'Видалити цю знижку?', 'natura' ); ?>');">
											<span class="dashicons dashicons-trash"></span>
											<?php esc_html_e( 'Видалити', 'natura' ); ?>
										</a>
									</td>
								</tr>
								<?php
							endforeach;
							?>
						</tbody>
					</table>
				</div>
			<?php else : ?>
				<div class="natura-empty-state">
					<span class="dashicons dashicons-tickets-alt" style="font-size: 60px; width: 60px; height: 60px; color: #ccc;"></span>
					<p><?php esc_html_e( 'Відсоткових знижок поки немає', 'natura' ); ?></p>
				</div>
			<?php endif; ?>
		</div>
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
		echo '<div class="woocommerce-message" style="margin-bottom: 15px; background: #e8f5e9; border-color: #4CAF50; color: #2E7D32;">';
		echo '<span class="dashicons dashicons-star-filled" style="color: #4CAF50;"></span> ';
		echo esc_html__( 'Для вас встановлено індивідуальну ціну на цей товар!', 'natura' );
		echo '</div>';
	} elseif ( $discount > 0 ) {
		echo '<div class="woocommerce-message" style="margin-bottom: 15px; background: #e8f5e9; border-color: #4CAF50; color: #2E7D32;">';
		echo '<span class="dashicons dashicons-ticket-alt" style="color: #4CAF50;"></span> ';
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
		echo '<div class="woocommerce-message" style="margin-bottom: 20px; background: #e8f5e9; border-color: #4CAF50; color: #2E7D32;">';
		echo '<span class="dashicons dashicons-ticket-alt" style="color: #4CAF50;"></span> ';
		printf(
			esc_html__( 'Ваша персональна знижка на всі товари: %s%%', 'natura' ),
			esc_html( $discount )
		);
		echo '</div>';
	}
}
add_action( 'woocommerce_account_dashboard', 'natura_show_discount_in_account' );
