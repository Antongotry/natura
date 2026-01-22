<?php
/**
 * Home block: Trusted section.
 *
 * @package Natura
 */

if (! defined('ABSPATH')) {
	exit;
}
?>

<section class="trusted" id="trusted">
	<div class="trusted__container">
		<div class="trusted__header">
			<div class="trusted__left">
				<h2 class="trusted__title">
					<span aria-hidden="true">
						Нам довіряють<br>
						<span class="trusted__title-highlight">лідери</span> ринку
					</span>
					<span class="sr-only">Нам довіряють лідери ринку</span>
				</h2>
				<p class="trusted__text trusted__text--mobile">
					Серед наших клієнтів — відомі<br>
					мережі, готелі та ресторани, які<br>
					цінують якість і стабільність
				</p>
			</div>
			<div class="trusted__right">
				<p class="trusted__text trusted__text--desktop">
					Серед наших клієнтів — відомі<br>
					мережі, готелі та ресторани, які<br>
					цінують якість і стабільність.
				</p>
			</div>
		</div>
		<div class="trusted__carousels">
			<?php
			$carousel_items = natura_get_trusted_carousel_items();
			$top_items = ! empty($carousel_items['top']) ? $carousel_items['top'] : array();
			$bottom_items = ! empty($carousel_items['bottom']) ? $carousel_items['bottom'] : array();

			// Top carousel (right)
			$trusted_static_icon = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2026/01/Mask-group-1_result.webp';
			$trusted_static_icon_hover = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2026/01/Mask-group_result.webp';
			if (! empty($top_items) || $trusted_static_icon || $trusted_static_icon_hover) :
				?>
				<div class="trusted__carousel trusted__carousel--right">
					<div class="trusted__carousel-track">
						<?php
						// Static trusted image (ordinary + hover)
						if ($trusted_static_icon || $trusted_static_icon_hover) {
							?>
							<div class="trusted__carousel-item">
								<div class="trusted__item-content">
									<?php if ($trusted_static_icon) : ?>
										<img class="trusted__icon" src="<?php echo esc_url($trusted_static_icon); ?>" alt="">
									<?php endif; ?>
									<?php if ($trusted_static_icon_hover) : ?>
										<img class="trusted__icon-hover" src="<?php echo esc_url($trusted_static_icon_hover); ?>" alt="">
									<?php endif; ?>
								</div>
							</div>
							<?php
						}
						// Render original items
						foreach ($top_items as $item) {
							$icon_id = isset($item['icon_id']) ? (int) $item['icon_id'] : 0;
							$icon_hover_id = isset($item['icon_hover_id']) ? (int) $item['icon_hover_id'] : 0;
							$icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'full') : '';
							$icon_hover_url = $icon_hover_id ? wp_get_attachment_image_url($icon_hover_id, 'full') : '';

							if ($icon_url || $icon_hover_url) {
								?>
								<div class="trusted__carousel-item">
									<div class="trusted__item-content">
										<?php if ($icon_url) : ?>
											<img class="trusted__icon" src="<?php echo esc_url($icon_url); ?>" alt="">
										<?php endif; ?>
										<?php if ($icon_hover_url) : ?>
											<img class="trusted__icon-hover" src="<?php echo esc_url($icon_hover_url); ?>" alt="">
										<?php endif; ?>
									</div>
								</div>
								<?php
							}
						}
						// Duplicate items for seamless loop
						if ($trusted_static_icon || $trusted_static_icon_hover) {
							?>
							<div class="trusted__carousel-item">
								<div class="trusted__item-content">
									<?php if ($trusted_static_icon) : ?>
										<img class="trusted__icon" src="<?php echo esc_url($trusted_static_icon); ?>" alt="">
									<?php endif; ?>
									<?php if ($trusted_static_icon_hover) : ?>
										<img class="trusted__icon-hover" src="<?php echo esc_url($trusted_static_icon_hover); ?>" alt="">
									<?php endif; ?>
								</div>
							</div>
							<?php
						}
						foreach ($top_items as $item) {
							$icon_id = isset($item['icon_id']) ? (int) $item['icon_id'] : 0;
							$icon_hover_id = isset($item['icon_hover_id']) ? (int) $item['icon_hover_id'] : 0;
							$icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'full') : '';
							$icon_hover_url = $icon_hover_id ? wp_get_attachment_image_url($icon_hover_id, 'full') : '';

							if ($icon_url || $icon_hover_url) {
								?>
								<div class="trusted__carousel-item">
									<div class="trusted__item-content">
										<?php if ($icon_url) : ?>
											<img class="trusted__icon" src="<?php echo esc_url($icon_url); ?>" alt="">
										<?php endif; ?>
										<?php if ($icon_hover_url) : ?>
											<img class="trusted__icon-hover" src="<?php echo esc_url($icon_hover_url); ?>" alt="">
										<?php endif; ?>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
			endif;

			// Bottom carousel (left)
			if (! empty($bottom_items)) :
				?>
				<div class="trusted__carousel trusted__carousel--left">
					<div class="trusted__carousel-track">
						<?php
						// Render original items
						foreach ($bottom_items as $item) {
							$icon_id = isset($item['icon_id']) ? (int) $item['icon_id'] : 0;
							$icon_hover_id = isset($item['icon_hover_id']) ? (int) $item['icon_hover_id'] : 0;
							$icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'full') : '';
							$icon_hover_url = $icon_hover_id ? wp_get_attachment_image_url($icon_hover_id, 'full') : '';

							if ($icon_url || $icon_hover_url) {
								?>
								<div class="trusted__carousel-item">
									<div class="trusted__item-content">
										<?php if ($icon_url) : ?>
											<img class="trusted__icon" src="<?php echo esc_url($icon_url); ?>" alt="">
										<?php endif; ?>
										<?php if ($icon_hover_url) : ?>
											<img class="trusted__icon-hover" src="<?php echo esc_url($icon_hover_url); ?>" alt="">
										<?php endif; ?>
									</div>
								</div>
								<?php
							}
						}
						// Duplicate items for seamless loop
						foreach ($bottom_items as $item) {
							$icon_id = isset($item['icon_id']) ? (int) $item['icon_id'] : 0;
							$icon_hover_id = isset($item['icon_hover_id']) ? (int) $item['icon_hover_id'] : 0;
							$icon_url = $icon_id ? wp_get_attachment_image_url($icon_id, 'full') : '';
							$icon_hover_url = $icon_hover_id ? wp_get_attachment_image_url($icon_hover_id, 'full') : '';

							if ($icon_url || $icon_hover_url) {
								?>
								<div class="trusted__carousel-item">
									<div class="trusted__item-content">
										<?php if ($icon_url) : ?>
											<img class="trusted__icon" src="<?php echo esc_url($icon_url); ?>" alt="">
										<?php endif; ?>
										<?php if ($icon_hover_url) : ?>
											<img class="trusted__icon-hover" src="<?php echo esc_url($icon_hover_url); ?>" alt="">
										<?php endif; ?>
									</div>
								</div>
								<?php
							}
						}
						?>
					</div>
				</div>
				<?php
			endif;
			?>
		</div>
	</div>
</section>

