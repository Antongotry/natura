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
			'value'       => '1000',
			'suffix'      => '',
			'text'        => '<span class="insights-list__bold">м² сучасного складу.</span> Продукти<br>зберігаються у правильних умовах,<br>щоб зберегти їхню користь і смак',
			'preview'     => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/300-image_result.webp',
		],
		[
			'icon'        => 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/icon-20.svg',
			'value'       => '20',
			'suffix'      => '',
			'text'        => '<span class="insights-list__bold">власних авто з холодильним</span><br><span class="insights-list__bold">обладнанням.</span> Доставляємо швидко,<br>якісні продукти до ваших дверей',
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
</section>

