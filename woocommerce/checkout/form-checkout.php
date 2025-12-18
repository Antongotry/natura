<?php
/**
 * Checkout Form - Natura theme custom layout
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Получаем объект checkout, если он не передан
if ( ! isset( $checkout ) ) {
	$checkout = WC()->checkout();
}

do_action( 'woocommerce_before_checkout_form', $checkout );

// If checkout registration is disabled and not logged in, the user cannot checkout.
if ( ! $checkout->is_registration_enabled() && $checkout->is_registration_required() && ! is_user_logged_in() ) {
	echo esc_html( apply_filters( 'woocommerce_checkout_must_be_logged_in_message', __( 'You must be logged in to checkout.', 'woocommerce' ) ) );
	return;
}
?>

<main class="checkout-page">
	<div class="container checkout-page__container">

		<?php // Хлебные крошки как на странице товара. ?>
		<?php woocommerce_breadcrumb(); ?>

		<form name="checkout" method="post" class="checkout woocommerce-checkout" action="<?php echo esc_url( wc_get_checkout_url() ); ?>" enctype="multipart/form-data">
			<div class="checkout-page__layout">
				<div class="checkout-page__left">
					<section class="checkout-section checkout-section--primary">
						<h2 class="checkout-section__title"><?php esc_html_e( 'Основна інформація', 'natura' ); ?></h2>

						<?php do_action( 'woocommerce_checkout_before_customer_details' ); ?>

						<div class="checkout-section__group">
							<?php do_action( 'woocommerce_checkout_billing' ); ?>
						</div>
					</section>

					<section class="checkout-section checkout-section--shipping">
						<h2 class="checkout-section__title"><?php esc_html_e( 'Доставка', 'natura' ); ?></h2>

						<div class="checkout-section__group">
							<?php do_action( 'woocommerce_checkout_shipping' ); ?>
						</div>
					</section>

					<section class="checkout-section checkout-section--payment">
						<h2 class="checkout-section__title"><?php esc_html_e( 'Спосіб оплати', 'natura' ); ?></h2>

						<div class="checkout-section__group">
							<?php 
							if ( ! wp_doing_ajax() ) {
								do_action( 'woocommerce_review_order_before_payment' );
							}
							
							// Выводим способы оплаты
							if ( WC()->cart && WC()->cart->needs_payment() ) {
								$available_gateways = WC()->payment_gateways()->get_available_payment_gateways();
								WC()->payment_gateways()->set_current_gateway( $available_gateways );
							} else {
								$available_gateways = array();
							}
							
							if ( ! empty( $available_gateways ) ) {
								?>
								<ul class="wc_payment_methods payment_methods methods">
									<?php
									foreach ( $available_gateways as $gateway ) {
										wc_get_template( 'checkout/payment-method.php', array( 'gateway' => $gateway ) );
									}
									?>
								</ul>
								<?php
							} else {
								?>
								<ul class="wc_payment_methods payment_methods methods">
									<li>
										<?php
										wc_print_notice( 
											apply_filters( 
												'woocommerce_no_available_payment_methods_message', 
												WC()->customer->get_billing_country() 
													? esc_html__( 'Вибачте, але здається, у нас відсутні доступні способи оплати. Будь ласка, зв\'яжіться з нами.', 'woocommerce' ) 
													: esc_html__( 'Будь ласка, заповніть ваші дані вище, щоб побачити доступні способи оплати.', 'woocommerce' ) 
											), 
											'notice' 
										);
										?>
									</li>
								</ul>
								<?php
							}
							
							if ( ! wp_doing_ajax() ) {
								do_action( 'woocommerce_review_order_after_payment' );
							}
							?>
						</div>
					</section>

					<?php // Кнопка подтверждения заказа ?>
					<div class="checkout-section checkout-section--submit">
						<?php wp_nonce_field( 'woocommerce-process_checkout', 'woocommerce-process-checkout-nonce' ); ?>
						<button type="submit" class="checkout-submit-button" name="woocommerce_checkout_place_order" id="place_order" value="<?php esc_attr_e( 'Підтвердити замовлення', 'woocommerce' ); ?>" data-value="<?php esc_attr_e( 'Підтвердити замовлення', 'woocommerce' ); ?>">
							<?php esc_html_e( 'Підтвердити замовлення', 'woocommerce' ); ?>
						</button>
					</div>
				</div>

				<div class="checkout-page__right">
					<section class="checkout-summary">
						<h2 class="checkout-summary__title"><?php esc_html_e( 'Ваші товари', 'natura' ); ?></h2>

						<?php if ( $checkout->get_checkout_fields() ) : ?>
							<div id="customer_details" class="checkout-summary__hidden-fields">
								<?php do_action( 'woocommerce_checkout_after_customer_details' ); ?>
							</div>
						<?php endif; ?>

						<div id="order_review" class="woocommerce-checkout-review-order checkout-summary__order-review">
							<?php do_action( 'woocommerce_checkout_order_review' ); ?>
						</div>
					</section>
				</div>
			</div>
		</form>
	</div>
</main>

<?php do_action( 'woocommerce_after_checkout_form', $checkout ); ?>


