<?php
/**
 * Home block: Natura Market insights.
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}
?>

<section class="insights" id="insights">
	<div class="insights__inner container">
		<div class="insights__left">
			<p class="insights__intro">
				Ми знаємо, що таке висока планка — тому співпрацюємо з п'ятизірковими готелями та провідними ресторанами. Щодня дбаємо про якість продуктів, швидкість доставки та уважний сервіс.
			</p>

			<a class="insights__cta" href="#collaboration" data-collaboration-modal-open>
				Співпраця
				<img src="<?php echo esc_url(get_theme_file_uri('assets/img/insights/icon-spiv.svg')); ?>" alt="" class="insights__cta-icon">
			</a>
		</div>

		<div class="insights__right">
			<h2 class="insights__title">
				<!-- Desktop version: 4 lines -->
				<span class="insights__title-desktop" aria-hidden="true">
					<span class="insights__title-line"><span class="insights__title-accent">Natura Market —</span></span>
					<span class="insights__title-line">це більше, ніж</span>
					<span class="insights__title-line">просто доставка</span>
					<span class="insights__title-line">продуктів</span>
				</span>
				<!-- Mobile version: 3 lines -->
				<span class="insights__title-mobile" aria-hidden="true">
					<span class="insights__title-line"><span class="insights__title-accent">Natura Market —</span></span>
					<span class="insights__title-line">це більше, ніж просто</span>
					<span class="insights__title-line">доставка продуктів</span>
				</span>
				<span class="sr-only">Natura Market — це більше, ніж просто доставка продуктів</span>
			</h2>
		</div>
	</div>
	<?php
	$insights_rows = [
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-16.svg',
			'value'       => '16',
			'suffix'      => '',
			'text'        => '<span class="insights-list__bold">років досвіду у сфері HoReCa —</span><br>ми знаємо, які продукти потрібні<br>для ідеальної кухні — від<br>ресторану до вашого дому',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/16-image_result.webp',
		],
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-100.svg',
			'value'       => '100',
			'suffix'      => '(+)',
			'text'        => '<span class="insights-list__bold">перевірених українських</span><br><span class="insights-list__bold">постачальників.</span> Обираємо тільки<br>надійних партнерів, щоб ви завжди<br>отримували свіжі продукти',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/100-image_result.webp',
		],
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-700.svg',
			'value'       => '700',
			'suffix'      => '(+)',
			'text'        => '<span class="insights-list__bold">позицій в асортименті,</span> великий вибір:<br>овочі, фрукти, зелень, бакалія, горіхи,<br>солодощі на будь-який смак',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/700-image_result.webp',
		],
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-520.svg',
			'value'       => '520',
			'suffix'      => '(+)',
			'text'        => '<span class="insights-list__bold">задоволених клієнтів —</span> нам<br>довіряють кафе, ресторани та<br>сотні родин по Києву та області',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/520-image_result.webp',
		],
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-300.svg',
			'value'       => '300',
			'suffix'      => '',
			'text'        => '<span class="insights-list__bold">м² сучасного складу.</span> Продукти<br>зберігаються у правильних умовах,<br>щоб зберегти їхню користь і смак',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/300-image_result.webp',
		],
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-20.svg',
			'value'       => '20',
			'suffix'      => '',
			'text'        => '<span class="insights-list__bold">власних авто з холодильним</span><br><span class="insights-list__bold">обладнанням.</span> Доставляємо щодня<br>та без вихідних — швидко й<br>охолоджено прямо до ваших дверей',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/20-image_result.webp',
		],
	];
	?>
	<div class="insights-list">
		<div class="insights-list__table container">
			<?php foreach ($insights_rows as $index => $row) : ?>
				<div class="insights-list__row" data-preview="<?php echo esc_url($row['preview']); ?>">
					<span class="insights-list__number">(<?php echo str_pad($index + 1, 3, '0', STR_PAD_LEFT); ?>)</span>
					<div class="insights-list__icon">
						<img src="<?php echo esc_url($row['icon']); ?>" alt="">
					</div>
					<div class="insights-list__row-top">
						<div class="insights-list__photo">
							<img src="<?php echo esc_url($row['preview']); ?>" alt="">
						</div>
					</div>
					<div class="insights-list__right-content">
						<div class="insights-list__value-area">
							<div class="insights-list__value-wrap">
								<span class="insights-list__value">
									<?php echo esc_html($row['value']); ?>
									<?php if (! empty($row['suffix'])) : ?>
										<span class="insights-list__suffix"><?php echo esc_html($row['suffix']); ?></span>
									<?php endif; ?>
								</span>
							</div>
						</div>
						<div class="insights-list__text">
							<?php echo wp_kses_post($row['text']); ?>
						</div>
					</div>
				</div>
			<?php endforeach; ?>
			<div class="insights-list__preview">
				<img class="is-hidden" alt="">
				<img class="is-hidden" alt="">
			</div>
		</div>
		<div class="insights-list__preview">
			<img src="" alt="">
			<img src="" alt="">
		</div>
		<!-- Mobile buttons -->
		<div class="insights__cta-mobile">
			<div class="insights__cta-buttons">
				<a class="insights__cta-button" href="#collaboration" data-collaboration-modal-open>
					Співпрацювати
				</a>
				<a class="insights__cta-button-icon" href="#collaboration" data-collaboration-modal-open>
					<img src="<?php echo esc_url(get_theme_file_uri('assets/img/hero/arrow-dark.svg')); ?>" alt="">
				</a>
			</div>
		</div>
	</div>
	
	<!-- Collaboration Modal -->
	<div class="collaboration-modal" data-collaboration-modal>
		<div class="collaboration-modal__overlay" data-collaboration-modal-close></div>
		<div class="collaboration-modal__container">
			<div class="collaboration-modal__left">
				<h2 class="collaboration-modal__title">
					Ми завжди відкриті до нових партнерств та довгострокової співпраці
				</h2>
				<div class="collaboration-modal__contacts">
					<img class="collaboration-modal__phone-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/223.svg" alt="">
					<div class="collaboration-modal__phones">
						<a href="tel:+380932002211">+38 (093) 200 22 11</a>
						<a href="tel:+380962002211">+38 (096) 200 22 11</a>
					</div>
				</div>
			</div>
			<div class="collaboration-modal__right">
				<button class="collaboration-modal__close" type="button" data-collaboration-modal-close aria-label="Закрити">
					<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 1L14 14M14 1L1 14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
					</svg>
				</button>
				<div class="collaboration-modal__content">
					<h2 class="collaboration-modal__title collaboration-modal__title--mobile">
						Ми завжди<br>
						відкриті до нових<br>
						партнерств та<br>
						довгострокової<br>
						співпраці
					</h2>
					<img class="collaboration-modal__icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/66.svg" alt="">
					<p class="collaboration-modal__text">
						Якщо ви — виробник, фермер чи<br>
						імпортер якісної продукції, який поділяє<br>
						наші цінності щодо свіжості, чесності та<br>
						<strong>надійності — запрошуємо до співпраці</strong>
					</p>
					<p class="collaboration-modal__email-text">
						↳ Надішліть інформацію<br>
						про себе на <a href="mailto:natura.market@ukr.net" class="collaboration-modal__email-link">natura.market@ukr.net</a><br>
						або заповніть форму.
					</p>
					<form class="collaboration-modal__form">
						<div class="collaboration-modal__form-row">
							<div class="collaboration-modal__form-field">
								<label class="collaboration-modal__label" for="collaboration-name">Ваше ім'я *</label>
								<input type="text" id="collaboration-name" class="collaboration-modal__input" placeholder="Олександр" required>
							</div>
							<div class="collaboration-modal__form-field">
								<label class="collaboration-modal__label" for="collaboration-phone">Номер телефону *</label>
								<input type="tel" id="collaboration-phone" class="collaboration-modal__input" placeholder="+38 (093) 200 22 11" required>
							</div>
						</div>
						<div class="collaboration-modal__form-field">
							<label class="collaboration-modal__label" for="collaboration-comment">
								Коментар <span class="collaboration-modal__label-light">(Коротка інформація щодо співпраці)</span> *
							</label>
							<textarea id="collaboration-comment" class="collaboration-modal__textarea" placeholder="Я пропоную..." required></textarea>
						</div>
						<button type="submit" class="collaboration-modal__submit">Надіслати</button>
					</form>
				</div>
			</div>
		</div>
	</div>
	
	<!-- Feedback Modal -->
	<div class="feedback-modal" data-feedback-modal>
		<div class="feedback-modal__overlay" data-feedback-modal-close></div>
		<div class="feedback-modal__container">
			<div class="feedback-modal__left">
				<h2 class="feedback-modal__title">
					Ми цінуємо<br>
					кожне ваше<br>
					повідомлення —<br>
					подяку, пораду<br>
					чи критику
				</h2>
				<div class="feedback-modal__contacts">
					<img class="feedback-modal__phone-icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/223.svg" alt="">
					<div class="feedback-modal__phones">
						<a href="tel:+380932002211">+38 (093) 200 22 11</a>
						<a href="tel:+380962002211">+38 (096) 200 22 11</a>
					</div>
				</div>
			</div>
			<div class="feedback-modal__right">
				<button class="feedback-modal__close" type="button" data-feedback-modal-close aria-label="Закрити">
					<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M1 1L14 14M14 1L1 14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
					</svg>
				</button>
				<div class="feedback-modal__content">
					<h2 class="feedback-modal__title feedback-modal__title--mobile">
						Ми цінуємо<br>
						кожне ваше<br>
						повідомлення —<br>
						подяку, пораду<br>
						чи критику
					</h2>
					<img class="feedback-modal__icon" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/icon-v.svg" alt="">
					<p class="feedback-modal__text">
						Це можливість для нас ставати кращими<br>
						щодня: вдосконалювати сервіс,<br>
						розширювати асортимент і дбати про те,<br>
						що справді важливо. Залиште свій відгук<br>
						нижче або зв'яжіться з нами напряму
					</p>
					<p class="feedback-modal__subtext">
						Разом ми створюємо сервіс,<br>
						який заслуговує на довіру
					</p>
					<p class="feedback-modal__email-text">
						↳ Залиште свій відгук нижче<br>
						або зв'яжіться з нами напряму<br>
						<a href="mailto:natura.market@ukr.net" class="feedback-modal__email-link">natura.market@ukr.net</a>
					</p>
					<form class="feedback-modal__form">
						<div class="feedback-modal__form-row">
							<div class="feedback-modal__form-field">
								<label class="feedback-modal__label" for="feedback-name">Ваше ім'я *</label>
								<input type="text" id="feedback-name" class="feedback-modal__input" placeholder="Олександр" required>
							</div>
							<div class="feedback-modal__form-field">
								<label class="feedback-modal__label" for="feedback-phone">Номер телефону *</label>
								<input type="tel" id="feedback-phone" class="feedback-modal__input" placeholder="+38 (093) 200 22 11" required>
							</div>
						</div>
						<div class="feedback-modal__form-field">
							<label class="feedback-modal__label" for="feedback-message">Ваші враження *</label>
							<textarea id="feedback-message" class="feedback-modal__textarea" placeholder="Тут може бути ваш відгук..." required></textarea>
						</div>
						<button type="submit" class="feedback-modal__submit">Надіслати</button>
					</form>
				</div>
			</div>
		</div>
	</div>
</section>

