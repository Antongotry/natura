<?php
/**
 * Home block: Payment section.
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}
?>

<section class="payment" id="payment">
	<div class="payment__background-wrapper">
		<video class="payment__background" autoplay muted loop playsinline>
			<source src="<?php echo esc_url(content_url('uploads/videos/6004787_People_Person_3840x2160.mp4')); ?>" type="video/mp4">
		</video>
	</div>
	<div class="payment__container">
		<div class="payment__inner">
			<div class="payment__title-container">
				<div class="payment__title-wrapper">
					<h2 class="payment__title">
						<span aria-hidden="true">
							Обирайте<br>
							зручний спосіб<br>
							оплати та<br>
							доставки
						</span>
						<span class="sr-only">Обирайте зручний спосіб оплати та доставки</span>
					</h2>
				</div>
			</div>
			<div class="payment__bottom">
				<p class="payment__text">
					Ми подбали, щоб покупки були<br>
					простими: зручні варіанти оплати та<br>
					щоденна доставка по Києву і області
				</p>
				<div class="payment__buttons">
					<button class="payment__button" type="button" data-payment-modal-open>
						Ознайомитися
					</button>
					<button class="payment__button-icon" type="button" data-payment-modal-open>
						<img src="<?php echo esc_url(get_theme_file_uri('assets/img/hero/arrow-dark.svg')); ?>" alt="">
					</button>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Payment Modal -->
	<div class="payment-modal" data-payment-modal>
		<div class="payment-modal__overlay" data-payment-modal-close></div>
		<div class="payment-modal__panel">
			<div class="payment-modal__header">
				<h2 class="payment-modal__title">
					Оплата та<br>
					доставка
				</h2>
				<button class="payment-modal__close" type="button" data-payment-modal-close aria-label="Закрити">
					<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 1L14 14M14 1L1 14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
					</svg>
				</button>
			</div>
			
			<div class="payment-modal__content">
				<div class="payment-modal__accordion">
					<div class="payment-modal__accordion-item" data-accordion-item>
						<div class="payment-modal__accordion-header">
							<div class="payment-modal__accordion-icon-wrapper">
								<img class="payment-modal__accordion-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/pop-2.svg" alt="">
							</div>
							<h3 class="payment-modal__accordion-title">Способи оплати</h3>
							<button class="payment-modal__accordion-toggle" type="button" aria-label="Розгорнути">
								<img class="payment-modal__accordion-arrow" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/1v.svg" alt="">
							</button>
						</div>
						<div class="payment-modal__accordion-content">
							<div class="payment-modal__accordion-content-inner">
								<ul class="payment-modal__delivery-methods">
									<li class="payment-modal__delivery-method">
										<img class="payment-modal__delivery-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/1.svg" alt="">
										<span class="payment-modal__delivery-text">Готівкою при отриманні замовлення</span>
									</li>
									<li class="payment-modal__delivery-method">
										<img class="payment-modal__delivery-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/2.svg" alt="">
										<span class="payment-modal__delivery-text">Готівкою при отриманні замовлення</span>
									</li>
									<li class="payment-modal__delivery-method">
										<img class="payment-modal__delivery-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/3.svg" alt="">
										<span class="payment-modal__delivery-text">Передоплата на банківську карту</span>
									</li>
								</ul>
								<p class="payment-modal__payment-note">
									Якщо ви замовили ваговий товар, наш оператор<br>
									звʼяжеться з вами та надішле чек на точну суму.<br>
									Остаточна вартість визначається після зважування<br>
									товару під час комплектації.
								</p>
							</div>
						</div>
					</div>
					
					<div class="payment-modal__accordion-item" data-accordion-item>
						<div class="payment-modal__accordion-header">
							<div class="payment-modal__accordion-icon-wrapper">
								<img class="payment-modal__accordion-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/pop-1-.svg" alt="">
							</div>
							<h3 class="payment-modal__accordion-title">Доставка</h3>
							<button class="payment-modal__accordion-toggle" type="button" aria-label="Розгорнути">
								<img class="payment-modal__accordion-arrow" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/1v.svg" alt="">
							</button>
						</div>
						<div class="payment-modal__accordion-content">
							<div class="payment-modal__accordion-content-inner">
								<p class="payment-modal__delivery-note">
									Мінімальна сума замовлення залежить від населеного<br>
									пункту, куди здійснюватиметься доставка.
								</p>
								<p class="payment-modal__delivery-note-second">
									Доставка здійснюється у такі населені пункти: Вишневе,<br>
									Боярка, Ірпінь, Вишгород, Бровари, Бобриця, Бориспіль,<br>
									Петропавлівська та Софіївська Борщагівка, Стоянка, Гореничі.
								</p>
								<p class="payment-modal__delivery-note">
									Доставка в інші населені пункти в радіусі до 40 км<br>
									від Києва — за тарифом 10 грн / км.₴
								</p>
							</div>
						</div>
					</div>
				</div>
			</div>
			
			<div class="payment-modal__footer">
				<a class="payment-modal__contact" href="tel:+380000000000" aria-label="Позвонить">
					<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/pop-phone.svg" alt="">
				</a>
				<a class="payment-modal__contact payment-modal__contact--mail" href="mailto:info@natura.com" aria-label="Написать письмо">
					<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/pop-mail.svg" alt="">
				</a>
			</div>
		</div>
	</div>
</section>

