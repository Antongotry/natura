<?php
/**
 * Home block: Collaboration section.
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}
?>

<section class="collaboration" id="collaboration">
	<div class="collaboration__container">
		<div class="collaboration__left">
			<div class="collaboration__left-inner">
				<h2 class="collaboration__title">
					<span aria-hidden="true">
						Команда,<br>
						яка піклується<br>
						про <span class="collaboration__title-word--highlight">кожен</span><br>
						ваш продукт
					</span>
					<span class="sr-only">Команда, яка піклується про кожен ваш продукт</span>
				</h2>
				<div class="collaboration__bottom-content">
					<div class="collaboration__content-left">
						<p class="collaboration__paragraph">
							З 2008 року ми постачаємо свіжі овочі,<br>
							фрукти та інші продукти для ресторанів,<br>
							готелів, національних торгових мереж<br>
							і тепер — для приватних клієнтів.
						</p>
						<p class="collaboration__paragraph">
							Всі замовлення формуються під наглядом<br>
							персонального менеджера та доставляються<br>
							у спеціалізованому транспорті. Ви замовляєте<br>
							сьогодні — отримуєте вже завтра.
						</p>
						<?php 
						$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
						?>
						<div class="collaboration__buttons">
							<a class="collaboration__button" href="<?php echo esc_url($catalog_url); ?>">
								До каталогу
							</a>
							<a class="collaboration__button-icon" href="<?php echo esc_url($catalog_url); ?>">
								<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/right-svg-white.svg" alt="">
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div class="collaboration__right">
			<div class="collaboration__image-wrapper">
				<img class="collaboration__background" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/rectangle-56_result.webp" alt="">
				<div class="collaboration__content">
					<img class="collaboration__icon collaboration__icon--desktop" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/group-74.svg" alt="">
					<img class="collaboration__icon collaboration__icon--mobile" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/group-74-1.svg" alt="">
					<div class="collaboration__text">
						<span class="collaboration__brand">Natura Market</span><br>
						дбають про вашу<br>
						їжу з 2008 року
					</div>
				</div>
			</div>
		</div>
	</div>
</section>


