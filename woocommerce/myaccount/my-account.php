<?php
/**
 * My Account page - Custom Natura Template
 *
 * @package Natura
 */

defined( 'ABSPATH' ) || exit;

$current_user = wp_get_current_user();

// Визначаємо поточний endpoint
$current_endpoint = '';
foreach ( wc_get_account_menu_items() as $endpoint => $label ) {
	if ( is_wc_endpoint_url( $endpoint ) ) {
		$current_endpoint = $endpoint;
		break;
	}
}
if ( empty( $current_endpoint ) ) {
	$current_endpoint = 'dashboard';
}

// Меню кабінету
$menu_items = [
	'orders' => 'Мої замовлення',
	'edit-account' => 'Профіль',
	'edit-address' => 'Адреса',
	'support' => 'Підтримка',
];
?>

<!-- Hero Banner (same as catalog) -->
<section class="shop-archive-hero">
	<div class="container">
		<div class="shop-archive-hero__image-wrapper">
			<?php
			$banner_image_desktop = get_theme_mod( 'shop_archive_banner_image_desktop', 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-62_result-scaled.webp' );
			$banner_image_mobile = get_theme_mod( 'shop_archive_banner_image_mobile', 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-62-1.png' );
			?>
			<picture>
				<source media="(max-width: 768px)" srcset="<?php echo esc_url( $banner_image_mobile ); ?>">
				<img src="<?php echo esc_url( $banner_image_desktop ); ?>" alt="<?php esc_attr_e( 'Особистий кабінет', 'natura' ); ?>" class="shop-archive-hero__image">
			</picture>
			<div class="shop-archive-hero__content">
				<div class="shop-archive-hero__breadcrumb">
					<nav class="woocommerce-breadcrumb">
						<a href="<?php echo esc_url(home_url('/')); ?>">Головна</a>
						<span class="breadcrumb-separator">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
						<?php 
						$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/catalog');
						?>
						<a href="<?php echo esc_url($catalog_url); ?>">Каталог</a>
						<span class="breadcrumb-separator">
							<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6 12L10 8L6 4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/></svg>
						</span>
						<span>Особистий кабінет</span>
					</nav>
				</div>
			</div>
		</div>
	</div>
</section>

<div class="account-page">
	<div class="container">
		<div class="account-page__layout">
			<!-- Sidebar -->
			<aside class="account-page__sidebar">
				<h1 class="account-page__title">Особистий кабінет</h1>
				<nav class="account-page__menu">
					<?php foreach ($menu_items as $endpoint => $label) : 
						$url = ($endpoint === 'support') ? '#support' : wc_get_account_endpoint_url($endpoint);
						$is_active = ($current_endpoint === $endpoint) || ($endpoint === 'orders' && $current_endpoint === 'dashboard');
					?>
						<a href="<?php echo esc_url($url); ?>" 
						   class="account-page__menu-item <?php echo $is_active ? 'account-page__menu-item--active' : ''; ?>"
						   <?php echo ($endpoint === 'support') ? 'data-support-link' : ''; ?>>
							<span class="account-page__menu-text"><?php echo esc_html($label); ?></span>
							<span class="account-page__menu-arrow">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
									<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
								</svg>
							</span>
						</a>
					<?php endforeach; ?>
					<a href="<?php echo esc_url(wp_logout_url(home_url('/'))); ?>" class="account-page__menu-item account-page__menu-item--logout">
						<span class="account-page__menu-text">Вийти</span>
						<span class="account-page__menu-arrow">
							<svg width="24" height="24" viewBox="0 0 24 24" fill="none">
								<path d="M9 18L15 12L9 6" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
							</svg>
						</span>
					</a>
				</nav>
			</aside>

			<!-- Content -->
			<div class="account-page__content">
				<?php do_action( 'woocommerce_account_content' ); ?>
			</div>
		</div>
	</div>
</div>

<!-- Support Modal -->
<div class="account-support-modal" data-support-modal style="display: none;">
	<div class="account-support-modal__overlay" data-support-close></div>
	<div class="account-support-modal__content">
		<button type="button" class="account-support-modal__close" data-support-close>&times;</button>
		<h2 class="account-section__title">Підтримка</h2>
		<div class="account-support__info">
			<div class="account-support__item">
				<span>📞</span>
				<span>Телефон:</span>
				<a href="tel:+380932002211">+38 (093) 200 22 11</a>
			</div>
			<div class="account-support__item">
				<span>📞</span>
				<span>Телефон:</span>
				<a href="tel:+380962002211">+38 (096) 200 22 11</a>
			</div>
			<div class="account-support__item">
				<span>✉️</span>
				<span>Email:</span>
				<a href="mailto:zakaz@naturamarket.kiev.ua">zakaz@naturamarket.kiev.ua</a>
			</div>
			<div class="account-support__item">
				<span>🕐</span>
				<span>Графік роботи:</span>
				<span>Пн-Сб: 09:00 – 17:00</span>
			</div>
		</div>
	</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
	const supportLinks = document.querySelectorAll('[data-support-link]');
	const modal = document.querySelector('[data-support-modal]');
	const closeButtons = document.querySelectorAll('[data-support-close]');
	
	supportLinks.forEach(link => {
		link.addEventListener('click', function(e) {
			e.preventDefault();
			modal.style.display = 'flex';
		});
	});
	
	closeButtons.forEach(btn => {
		btn.addEventListener('click', function() {
			modal.style.display = 'none';
		});
	});
});
</script>






