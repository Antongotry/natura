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
		$product_ids = isset( $_POST['sales_products'] ) && is_array( $_POST['sales_products'] ) 
			? array_map( 'intval', $_POST['sales_products'] ) 
			: array();
		$product_ids = array_filter( $product_ids );
		update_option( 'natura_sales_products', $product_ids );
		echo '<div class="notice notice-success"><p>' . esc_html__( 'Налаштування збережено!', 'natura' ) . '</p></div>';
	}

	$saved_products = get_option( 'natura_sales_products', array() );
	if ( ! is_array( $saved_products ) ) {
		$saved_products = array();
	}

	// Enqueue scripts for search
	wp_enqueue_script( 'jquery' );
	?>
	<div class="wrap">
		<h1><?php echo esc_html__( 'Товари на сторінці акцій', 'natura' ); ?></h1>
		<p><?php echo esc_html__( 'Виберіть товари, які будуть відображатися на сторінці акцій. Можна вибрати 4 або 8 товарів.', 'natura' ); ?></p>
		
		<form method="post" action="">
			<?php wp_nonce_field( 'natura_sales_products_save' ); ?>
			
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="product_search"><?php echo esc_html__( 'Пошук товарів', 'natura' ); ?></label>
					</th>
					<td>
						<input 
							type="text" 
							id="product_search" 
							class="regular-text" 
							placeholder="<?php echo esc_attr__( 'Введіть назву або артикул товару...', 'natura' ); ?>"
							autocomplete="off"
						>
						<div id="product_search_results" style="margin-top: 10px; max-height: 400px; overflow-y: auto; border: 1px solid #ddd; padding: 10px; display: none;"></div>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label><?php echo esc_html__( 'Вибрані товари', 'natura' ); ?></label>
					</th>
					<td>
						<div id="selected_products" style="min-height: 200px; border: 1px solid #ddd; padding: 10px; background: #f9f9f9;">
							<?php if ( ! empty( $saved_products ) ) : ?>
								<?php foreach ( $saved_products as $product_id ) : ?>
									<?php
									$product = wc_get_product( $product_id );
									if ( ! $product ) {
										continue;
									}
									$sku = $product->get_sku();
									?>
									<div class="selected-product-item" data-product-id="<?php echo esc_attr( $product_id ); ?>" style="padding: 8px; margin: 5px 0; background: #fff; border: 1px solid #ccc; display: flex; justify-content: space-between; align-items: center;">
										<span>
											<strong><?php echo esc_html( $product->get_name() ); ?></strong>
											<?php if ( $sku ) : ?>
												<small style="color: #666;">(Артикул: <?php echo esc_html( $sku ); ?>)</small>
											<?php endif; ?>
											<small style="color: #999;">ID: <?php echo esc_html( $product_id ); ?></small>
										</span>
										<button type="button" class="button remove-product" data-product-id="<?php echo esc_attr( $product_id ); ?>" style="margin-left: 10px;">×</button>
										<input type="hidden" name="sales_products[]" value="<?php echo esc_attr( $product_id ); ?>">
									</div>
								<?php endforeach; ?>
							<?php else : ?>
								<p style="color: #999;"><?php echo esc_html__( 'Товари не вибрані. Використовуйте пошук вище для додавання товарів.', 'natura' ); ?></p>
							<?php endif; ?>
						</div>
						<p class="description">
							<?php echo esc_html__( 'Рекомендується вибрати 4 або 8 товарів.', 'natura' ); ?>
						</p>
					</td>
				</tr>
			</table>
			
			<?php submit_button( __( 'Зберегти', 'natura' ), 'primary', 'natura_sales_products_save' ); ?>
		</form>
	</div>

	<script type="text/javascript">
	jQuery(document).ready(function($) {
		let searchTimeout;
		const $searchInput = $('#product_search');
		const $results = $('#product_search_results');
		const $selectedContainer = $('#selected_products');
		const selectedIds = new Set();
		
		// Initialize selected IDs
		$selectedContainer.find('input[type="hidden"]').each(function() {
			selectedIds.add($(this).val());
		});

		// Search products
		$searchInput.on('input', function() {
			const query = $(this).val().trim();
			
			clearTimeout(searchTimeout);
			
			if (query.length < 2) {
				$results.hide().empty();
				return;
			}

			searchTimeout = setTimeout(function() {
				$.ajax({
					url: ajaxurl,
					type: 'POST',
					data: {
						action: 'natura_search_products_admin',
						query: query,
						nonce: '<?php echo wp_create_nonce( 'natura_search_products_admin' ); ?>'
					},
					success: function(response) {
						if (response.success && response.data.length > 0) {
							let html = '<ul style="list-style: none; margin: 0; padding: 0;">';
							response.data.forEach(function(product) {
								if (selectedIds.has(String(product.id))) {
									return; // Skip already selected
								}
								html += '<li style="padding: 8px; border-bottom: 1px solid #eee; cursor: pointer;" class="product-result-item" data-product-id="' + product.id + '">';
								html += '<strong>' + product.name + '</strong>';
								if (product.sku) {
									html += ' <small style="color: #666;">(Артикул: ' + product.sku + ')</small>';
								}
								html += ' <small style="color: #999;">ID: ' + product.id + '</small>';
								html += '</li>';
							});
							html += '</ul>';
							$results.html(html).show();
						} else {
							$results.html('<p style="padding: 10px; color: #999;">Товари не знайдено</p>').show();
						}
					},
					error: function() {
						$results.html('<p style="padding: 10px; color: #d63638;">Помилка пошуку</p>').show();
					}
				});
			}, 300);
		});

		// Add product to selection
		$(document).on('click', '.product-result-item', function() {
			const productId = $(this).data('product-id');
			const productName = $(this).find('strong').text();
			const productSku = $(this).find('small').first().text().replace(/[()]/g, '');
			
			if (selectedIds.has(String(productId))) {
				return;
			}

			selectedIds.add(String(productId));
			
			let html = '<div class="selected-product-item" data-product-id="' + productId + '" style="padding: 8px; margin: 5px 0; background: #fff; border: 1px solid #ccc; display: flex; justify-content: space-between; align-items: center;">';
			html += '<span><strong>' + productName + '</strong>';
			if (productSku) {
				html += ' <small style="color: #666;">' + productSku + '</small>';
			}
			html += ' <small style="color: #999;">ID: ' + productId + '</small></span>';
			html += '<button type="button" class="button remove-product" data-product-id="' + productId + '" style="margin-left: 10px;">×</button>';
			html += '<input type="hidden" name="sales_products[]" value="' + productId + '">';
			html += '</div>';

			if ($selectedContainer.find('p').length > 0) {
				$selectedContainer.empty();
			}
			$selectedContainer.append(html);
			$results.hide();
			$searchInput.val('');
		});

		// Remove product from selection
		$(document).on('click', '.remove-product', function() {
			const productId = $(this).data('product-id');
			selectedIds.delete(String(productId));
			$(this).closest('.selected-product-item').remove();
			
			if ($selectedContainer.find('.selected-product-item').length === 0) {
				$selectedContainer.html('<p style="color: #999;"><?php echo esc_js( __( 'Товари не вибрані. Використовуйте пошук вище для додавання товарів.', 'natura' ) ); ?></p>');
			}
		});

		// Hide results when clicking outside
		$(document).on('click', function(e) {
			if (!$(e.target).closest('#product_search, #product_search_results').length) {
				$results.hide();
			}
		});
	});
	</script>
	<?php
}

/**
 * AJAX handler for product search in admin
 */
function natura_search_products_admin_ajax() {
	check_ajax_referer( 'natura_search_products_admin', 'nonce' );

	if ( ! current_user_can( 'manage_options' ) ) {
		wp_send_json_error( array( 'message' => 'Unauthorized' ) );
		return;
	}

	$query = isset( $_POST['query'] ) ? sanitize_text_field( $_POST['query'] ) : '';

	if ( strlen( $query ) < 2 ) {
		wp_send_json_success( array() );
		return;
	}

	$args = array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		's'              => $query,
		'orderby'        => 'relevance',
	);

	// Also search by SKU
	$sku_query = new WP_Query( array(
		'post_type'      => 'product',
		'post_status'    => 'publish',
		'posts_per_page' => 20,
		'meta_query'     => array(
			array(
				'key'     => '_sku',
				'value'   => $query,
				'compare' => 'LIKE',
			),
		),
	) );

	$products_query = new WP_Query( $args );
	$products = array();
	$product_ids = array();

	// Add products from title search
	if ( $products_query->have_posts() ) {
		while ( $products_query->have_posts() ) {
			$products_query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( $product ) {
				$product_ids[] = $product->get_id();
				$products[] = array(
					'id'   => $product->get_id(),
					'name' => $product->get_name(),
					'sku'  => $product->get_sku(),
				);
			}
		}
		wp_reset_postdata();
	}

	// Add products from SKU search (avoid duplicates)
	if ( $sku_query->have_posts() ) {
		while ( $sku_query->have_posts() ) {
			$sku_query->the_post();
			$product = wc_get_product( get_the_ID() );
			if ( $product && ! in_array( $product->get_id(), $product_ids, true ) ) {
				$products[] = array(
					'id'   => $product->get_id(),
					'name' => $product->get_name(),
					'sku'  => $product->get_sku(),
				);
			}
		}
		wp_reset_postdata();
	}

	wp_send_json_success( $products );
}
add_action( 'wp_ajax_natura_search_products_admin', 'natura_search_products_admin_ajax' );

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
