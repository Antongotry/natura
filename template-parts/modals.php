<?php
/**
 * Global modals (available on all pages).
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

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


