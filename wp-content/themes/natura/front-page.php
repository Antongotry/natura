<?php

get_header();

?>

<main id="primary" class="site-main">
	<section class="hero" aria-hidden="true">
		<video class="hero__video" autoplay loop muted playsinline>
			<?php
			$upload_dir = wp_upload_dir();
			$video_url = $upload_dir['baseurl'] . '/videos/525428_Farmer_Basket_3840x2160.mp4';
			?>
			<source src="<?php echo esc_url($video_url); ?>" type="video/mp4">
		</video>
		<div class="hero__overlay"></div>
		<div class="hero__content">
			<div class="hero__container container">
				<div class="hero__top">
					<h1 class="hero__title" aria-hidden="true">
						Продукти<span class="hero__title-break hero__title-break--mobile"><br></span> з усього<span class="hero__title-break hero__title-break--desktop"><br></span> світу —<span class="hero__title-break hero__title-break--mobile"><br></span> в один клік
					</h1>
					<h1 class="sr-only">
						Продукти з усього світу — в один клік
					</h1>
				</div>
				<div class="hero__bottom">
					<ul class="hero__features">
						<li class="hero__feature">
							<div class="hero__feature-icon">
								<img src="<?php echo esc_url(get_theme_file_uri('assets/img/hero/icon-1.svg')); ?>" alt="">
							</div>
							<div class="hero__feature-text">
								<h3 class="hero__feature-title">Безкоштовна доставка</h3>
								<p class="hero__feature-desc">ми доставимо ваші товари безкоштовно,
при замовленні від 1500 гривень</p>
							</div>
						</li>
						<li class="hero__feature">
							<div class="hero__feature-icon">
								<img src="<?php echo esc_url(get_theme_file_uri('assets/img/hero/icon-2.svg')); ?>" alt="">
							</div>
							<div class="hero__feature-text">
								<h3 class="hero__feature-title">Варіанти розрахунку</h3>
								<p class="hero__feature-desc">ви можете обрати найбільш
комфортний спосіб оплати</p>
							</div>
						</li>
						<li class="hero__feature">
							<div class="hero__feature-icon">
								<img src="<?php echo esc_url(get_theme_file_uri('assets/img/hero/icon-3.svg')); ?>" alt="">
							</div>
							<div class="hero__feature-text">
								<h3 class="hero__feature-title">Без вихідних</h3>
								<p class="hero__feature-desc">завжди на зв'язку для вас —
щодня і без вихідних</p>
							</div>
						</li>
					</ul>
					<div class="hero__cta">
						<p class="hero__cta-text">Свіжі, якісні поставки для ресторанів, готелів, кавʼярень та приватних клієнтів. Від екзотичних фруктів до локальної органіки — усе, що потрібно для ідеального меню чи щоденного раціону,
ви знайдете у нашому каталозі</p>
						<?php 
						$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
						?>
						<div class="hero__cta-buttons">
							<a class="hero__cta-button" href="<?php echo esc_url($catalog_url); ?>">
								До каталогу
							</a>
							<a class="hero__cta-button-icon" href="<?php echo esc_url($catalog_url); ?>">
								<img src="<?php echo esc_url(get_theme_file_uri('assets/img/hero/arrow-dark.svg')); ?>" alt="">
							</a>
						</div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<?php get_template_part('template-parts/home/categories-carousel'); ?>
	<?php get_template_part('template-parts/home/insights'); ?>
	<?php get_template_part('template-parts/home/collaboration'); ?>
	<?php get_template_part('template-parts/home/assortment'); ?>
	<?php get_template_part('template-parts/home/trusted'); ?>
	<?php get_template_part('template-parts/home/payment'); ?>
	<?php get_template_part('template-parts/home/faq'); ?>
</main>

<?php

get_footer();


