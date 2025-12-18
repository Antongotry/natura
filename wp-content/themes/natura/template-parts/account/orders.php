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
	<h2 class="account-section__title">Мої замовлення</h2>
	
	<?php if (empty($customer_orders)) : ?>
		<div class="account-orders__empty">
			<div class="account-orders__empty-icon">📦</div>
			<p>У вас ще немає замовлень</p>
			<a href="<?php echo esc_url(wc_get_page_permalink('shop')); ?>" class="account-form__submit" style="display: inline-block; margin-top: 16px; text-decoration: none;">
				Перейти до каталогу
			</a>
		</div>
	<?php else : ?>
		<table class="account-orders__table">
			<thead>
				<tr>
					<th>№ замовлення</th>
					<th>Дата</th>
					<th>Статус</th>
					<th>Сума</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ($customer_orders as $order) : 
					$status = $order->get_status();
					$status_class = 'account-orders__status--' . $status;
					$status_labels = [
						'pending' => 'Очікує оплати',
						'processing' => 'В обробці',
						'on-hold' => 'На утриманні',
						'completed' => 'Виконано',
						'cancelled' => 'Скасовано',
						'refunded' => 'Повернено',
						'failed' => 'Не вдалося',
					];
					$status_label = isset($status_labels[$status]) ? $status_labels[$status] : $status;
				?>
					<tr>
						<td>#<?php echo $order->get_order_number(); ?></td>
						<td><?php echo wc_format_datetime($order->get_date_created()); ?></td>
						<td><span class="account-orders__status <?php echo esc_attr($status_class); ?>"><?php echo esc_html($status_label); ?></span></td>
						<td><?php echo $order->get_formatted_order_total(); ?></td>
						<td><a href="<?php echo esc_url($order->get_view_order_url()); ?>" class="account-orders__view">Детальніше</a></td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
	<?php endif; ?>
</div>







