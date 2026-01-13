<?php
/**
 * Account - Orders Section
 */

$current_user = wp_get_current_user();
$customer_orders = wc_get_orders([
	'customer' => $current_user->ID,
	'limit' => 20,
	'orderby' => 'date',
	'order' => 'DESC',
]);
?>

<div class="account-orders">
	<?php
	// Показываем мини-попап если заказ только что был оформлен
	$order_placed = isset( $_GET['order_placed'] ) && $_GET['order_placed'] === '1';
	if ( $order_placed ) :
		?>
		<div class="account-orders__success-popup" data-order-success-popup>
			<div class="account-orders__success-popup-content">
				<button class="account-orders__success-popup-close" data-order-success-popup-close aria-label="Закрити">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M18 6L6 18M6 6L18 18" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</button>
				<h3 class="account-orders__success-popup-title">Дякуємо, що обираєте нас</h3>
				<p class="account-orders__success-popup-text">Ваше замовлення буде доставлене завтра протягом дня.</p>
			</div>
		</div>
	<?php endif; ?>
	<?php if (empty($customer_orders)) : ?>
		<div class="account-orders__empty">
			<div class="account-orders__empty-icon">📦</div>
			<p>У вас ще немає замовлень</p>
			<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="account-form__submit" style="display: inline-block; margin-top: 16px; text-decoration: none;">
				Перейти до каталогу
			</a>
		</div>
	<?php else : ?>
		<div class="account-orders__table-wrap">
			<table class="account-orders__table account-orders__table--detailed">
				<thead>
					<tr>
						<th class="account-orders__th account-orders__th--products">Товари</th>
						<th class="account-orders__th account-orders__th--date">Дата</th>
						<th class="account-orders__th account-orders__th--number">Номер замовлення</th>
						<th class="account-orders__th account-orders__th--status">Статус обробки</th>
						<th class="account-orders__th account-orders__th--total">Сума замовлення</th>
					</tr>
				</thead>
				<tbody>
					<?php foreach ( $customer_orders as $order ) : ?>
						<?php
						$status       = $order->get_status();
						$status_class = 'account-orders__status--' . $status;
						$status_label = function_exists( 'wc_get_order_status_name' ) ? wc_get_order_status_name( $status ) : $status;
						$date_created = $order->get_date_created();
						$date_value   = $date_created ? wp_date( 'd.m.Y', $date_created->getTimestamp() ) : '';
						?>

						<tr class="account-orders__row">
							<td class="account-orders__cell account-orders__cell--products">
								<div class="account-orders__products" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>">
									<?php
									$items      = $order->get_items( 'line_item' );
									$max_items  = 3;
									$shown      = 0;
									$total_items = is_countable( $items ) ? count( $items ) : 0;
									$order_id_attr = 'order-' . $order->get_id();

									foreach ( $items as $item_id => $item ) :
										$product    = $item->get_product();
										$product_id = $product && method_exists( $product, 'get_id' ) ? (int) $product->get_id() : 0;

										$unit = $product_id ? get_post_meta( $product_id, '_product_unit', true ) : '';
										if ( empty( $unit ) ) {
											$unit = get_option( 'woocommerce_weight_unit', '' );
										}

										$unit_display = '';
										if ( ! empty( $unit ) ) {
											$unit_display = ( function_exists( 'natura_is_kg_unit' ) && natura_is_kg_unit( $unit ) ) ? 'кг' : (string) $unit;
											$unit_display = trim( $unit_display );
										}

										$thumb_html = '';
										if ( $product ) {
											// Use woocommerce_single to keep proportions (like checkout), thumbnails can be square-cropped.
											$thumb_html = $product->get_image( 'woocommerce_single' );
										} elseif ( function_exists( 'wc_placeholder_img' ) ) {
											$thumb_html = wc_placeholder_img( 'woocommerce_single' );
										}

										$line_total_html = method_exists( $order, 'get_formatted_line_subtotal' )
											? $order->get_formatted_line_subtotal( $item )
											: ( function_exists( 'wc_price' ) ? wc_price( (float) $item->get_total() ) : '' );
										
										// Скрываем товары после первых 3
										$is_hidden = $shown >= $max_items;
										$hidden_class = $is_hidden ? 'account-orders__product--hidden' : '';
										?>

										<div class="account-orders__product <?php echo esc_attr( $hidden_class ); ?>" data-item-index="<?php echo esc_attr( $shown ); ?>">
											<div class="account-orders__product-thumb">
												<?php echo wp_kses_post( $thumb_html ); ?>
											</div>
											<div class="account-orders__product-info">
												<div class="account-orders__product-name">
													<?php echo esc_html( $item->get_name() ); ?>
													<?php if ( ! empty( $unit_display ) ) : ?>
														<span class="account-orders__product-unit">(<?php echo esc_html( $unit_display ); ?>)</span>
													<?php endif; ?>
												</div>
												<div class="account-orders__product-price"><?php echo wp_kses_post( $line_total_html ); ?></div>
											</div>
										</div>

										<?php
										$shown++;
									endforeach;

									// Правильно вычисляем оставшиеся товары
									$remaining = $total_items > $max_items ? ( $total_items - $max_items ) : 0;
									if ( $remaining > 0 ) :
										// Функция для правильного склонения
										$get_plural = function( $count ) {
											$mod10 = $count % 10;
											$mod100 = $count % 100;
											
											if ( $mod10 == 1 && $mod100 != 11 ) {
												return 'товар';
											} elseif ( in_array( $mod10, [2, 3, 4] ) && ! in_array( $mod100, [12, 13, 14] ) ) {
												return 'товари';
											} else {
												return 'товарів';
											}
										};
										?>
										<div class="account-orders__more" data-order-id="<?php echo esc_attr( $order->get_id() ); ?>" style="cursor: pointer;">
											<?php
											$plural = $get_plural( $remaining );
											echo esc_html( sprintf( 'Ще %d %s', $remaining, $plural ) );
											?>
										</div>
									<?php endif; ?>
								</div>
							</td>

							<td class="account-orders__cell account-orders__cell--date">
								<?php echo esc_html( $date_value ); ?>
							</td>

							<td class="account-orders__cell account-orders__cell--number">
								<a class="account-orders__order-link" href="<?php echo esc_url( $order->get_view_order_url() ); ?>">
									<?php echo esc_html( '№' . $order->get_order_number() ); ?>
								</a>
							</td>

							<td class="account-orders__cell account-orders__cell--status">
								<span class="account-orders__status <?php echo esc_attr( $status_class ); ?>">
									<?php echo esc_html( $status_label ); ?>
								</span>
							</td>

							<td class="account-orders__cell account-orders__cell--total">
								<?php echo wp_kses_post( $order->get_formatted_order_total() ); ?>
							</td>
						</tr>
					<?php endforeach; ?>
				</tbody>
			</table>
		</div>
	<?php endif; ?>
</div>






