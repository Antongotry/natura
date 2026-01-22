<?php
/**
 * 404 Error Page Template
 * Сторінка помилки - не знайдено
 */

get_header();

$home_url = home_url('/');
$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/catalog');
?>

<main class="error-page">
	<div class="error-page__container">
		<div class="error-page__content">
			<div class="error-page__code">
				<span class="error-page__number">4</span>
				<span class="error-page__leaf">
					<svg width="100%" height="100%" viewBox="0 0 120 140" fill="none" xmlns="http://www.w3.org/2000/svg">
						<path d="M60 0C60 0 20 40 20 80C20 102.091 37.909 120 60 120C82.091 120 100 102.091 100 80C100 40 60 0 60 0Z" fill="#4DBD56"/>
						<path d="M60 30C60 30 60 100 60 120" stroke="white" stroke-width="3" stroke-linecap="round"/>
						<path d="M60 50C45 65 40 80 42 95" stroke="white" stroke-width="2" stroke-linecap="round"/>
						<path d="M60 50C75 65 80 80 78 95" stroke="white" stroke-width="2" stroke-linecap="round"/>
						<path d="M60 70C50 80 48 90 50 100" stroke="white" stroke-width="2" stroke-linecap="round"/>
						<path d="M60 70C70 80 72 90 70 100" stroke="white" stroke-width="2" stroke-linecap="round"/>
					</svg>
				</span>
				<span class="error-page__number">4</span>
			</div>
			<h1 class="error-page__title">Сторінку не знайдено</h1>
			<p class="error-page__description">
				На жаль, сторінка, яку ви шукаєте, не існує або була переміщена.<br>
				Перевірте правильність адреси або поверніться на головну.
			</p>
			<div class="error-page__buttons">
				<a href="<?php echo esc_url($home_url); ?>" class="error-page__button error-page__button--primary">
					На головну
				</a>
				<a href="<?php echo esc_url($catalog_url); ?>" class="error-page__button error-page__button--secondary">
					До каталогу
				</a>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>
