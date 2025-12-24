<?php
/**
 * Thank you page (order received).
 * Minimal layout for guests + soft registration.
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

// WooCommerce passes $order in this template.
?>

<main class="checkout-page thankyou-page">
	<div class="container checkout-page__container thankyou-page__container">
		<?php if ( $order ) : ?>
			<?php if ( $order->has_status( 'failed' ) ) : ?>
				<div class="thankyou-page__card">
					<h1 class="thankyou-page__title"><?php esc_html_e( 'Оплата не пройшла', 'natura' ); ?></h1>
					<p class="thankyou-page__text"><?php esc_html_e( 'Спробуйте ще раз або оберіть інший спосіб оплати.', 'natura' ); ?></p>
				</div>
			<?php else : ?>
				<?php
				$order_number = $order->get_order_number();
				$email        = $order->get_billing_email();
				?>

				<div class="thankyou-page__card">
					<h1 class="thankyou-page__title"><?php esc_html_e( 'Дякуємо за покупку!', 'natura' ); ?></h1>
					<p class="thankyou-page__text">
						<?php
						echo esc_html(
							sprintf(
								/* translators: %s: order number */
								__( 'Замовлення №%s прийнято.', 'natura' ),
								$order_number
							)
						);
						?>
					</p>
					<?php if ( ! empty( $email ) ) : ?>
						<p class="thankyou-page__text thankyou-page__text--muted">
							<?php
							echo esc_html(
								sprintf(
									/* translators: %s: email */
									__( 'Підтвердження надіслано на %s', 'natura' ),
									$email
								)
							);
							?>
						</p>
					<?php endif; ?>
				</div>

				<?php if ( ! is_user_logged_in() && 0 === (int) $order->get_customer_id() ) : ?>
					<div class="thankyou-page__card thankyou-page__soft-account">
						<h2 class="thankyou-page__subtitle"><?php esc_html_e( 'Створіть кабінет', 'natura' ); ?></h2>
						<p class="thankyou-page__text thankyou-page__text--muted">
							<?php esc_html_e( 'Хочете відстежувати замовлення зручніше? Придумайте пароль — і ми створимо кабінет, де вже є це замовлення.', 'natura' ); ?>
						</p>

						<form
							class="thankyou-page__form"
							data-soft-account-form
							data-order-id="<?php echo esc_attr( $order->get_id() ); ?>"
							data-order-key="<?php echo esc_attr( $order->get_order_key() ); ?>"
						>
							<div class="thankyou-page__field">
								<label class="thankyou-page__label" for="soft-account-password">
									<?php esc_html_e( 'Пароль', 'natura' ); ?>
								</label>
								<input
									id="soft-account-password"
									class="thankyou-page__input"
									type="password"
									name="password"
									minlength="6"
									autocomplete="new-password"
									required
								/>
							</div>

							<button type="submit" class="checkout-submit-button thankyou-page__submit">
								<?php esc_html_e( 'Створити кабінет', 'natura' ); ?>
							</button>

							<p class="thankyou-page__message" data-soft-account-message role="status" aria-live="polite"></p>

							<p class="thankyou-page__text thankyou-page__text--muted thankyou-page__login-hint">
								<?php
								$auth_url = function_exists( 'natura_get_auth_url' ) ? natura_get_auth_url( 'login' ) : wp_login_url();
								echo wp_kses_post(
									sprintf(
										/* translators: %s: login url */
										__( 'Вже маєте кабінет? <a href="%s">Увійти</a>', 'natura' ),
										esc_url( $auth_url )
									)
								);
								?>
							</p>
						</form>
					</div>
				<?php endif; ?>
			<?php endif; ?>
		<?php else : ?>
			<div class="thankyou-page__card">
				<h1 class="thankyou-page__title"><?php esc_html_e( 'Дякуємо!', 'natura' ); ?></h1>
				<p class="thankyou-page__text"><?php esc_html_e( 'Ваше замовлення прийнято.', 'natura' ); ?></p>
			</div>
		<?php endif; ?>
	</div>
</main>


