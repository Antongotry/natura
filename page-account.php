<?php
/**
 * Template Name: Особистий кабінет
 * Сторінка особистого кабінету користувача
 */

// Редірект якщо не залогінений
if (!is_user_logged_in()) {
	wp_redirect(natura_get_auth_url());
	exit;
}

get_header();

$current_user = wp_get_current_user();
$current_section = isset($_GET['section']) ? sanitize_text_field($_GET['section']) : 'orders';

// Меню кабінету
$menu_items = [
	'orders' => [
		'title' => 'Мої замовлення',
		'icon' => 'orders'
	],
	'profile' => [
		'title' => 'Профіль',
		'icon' => 'profile'
	],
	'address' => [
		'title' => 'Адреса',
		'icon' => 'address'
	],
	'support' => [
		'title' => 'Підтримка',
		'icon' => 'support'
	],
];
?>

<main class="account-page">
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
						<nav class="woocommerce-breadcrumb" aria-label="<?php esc_attr_e( 'Хлібні крихти', 'natura' ); ?>">
							<?php $breadcrumb_icon_url = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/vector-12.svg'; ?>
							<a href="<?php echo esc_url( home_url( '/' ) ); ?>" class="woocommerce-breadcrumb__link">Головна</a>
							<span class="woocommerce-breadcrumb__separator">
								<img src="<?php echo esc_url( $breadcrumb_icon_url ); ?>" alt="" class="woocommerce-breadcrumb__icon">
							</span>
							<?php 
							$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : home_url('/catalog');
							?>
							<a href="<?php echo esc_url( $catalog_url ); ?>" class="woocommerce-breadcrumb__link">Каталог</a>
							<span class="woocommerce-breadcrumb__separator">
								<img src="<?php echo esc_url( $breadcrumb_icon_url ); ?>" alt="" class="woocommerce-breadcrumb__icon">
							</span>
							<span class="woocommerce-breadcrumb__current">Особистий кабінет</span>
						</nav>
					</div>
				</div>
			</div>
		</div>
	</section>

	<div class="container">
		<div class="account-page__layout">
			<!-- Sidebar -->
			<aside class="account-page__sidebar">
				<h1 class="account-page__title">Особистий кабінет</h1>
				<nav class="account-page__menu">
					<?php foreach ($menu_items as $key => $item) : ?>
						<a href="?section=<?php echo esc_attr($key); ?>" 
						   class="account-page__menu-item <?php echo $current_section === $key ? 'account-page__menu-item--active' : ''; ?>">
							<span class="account-page__menu-text"><?php echo esc_html($item['title']); ?></span>
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
				<?php
				switch ($current_section) {
					case 'orders':
						get_template_part('template-parts/account/orders');
						break;
					case 'profile':
						get_template_part('template-parts/account/profile');
						break;
					case 'address':
						get_template_part('template-parts/account/address');
						break;
					case 'support':
						get_template_part('template-parts/account/support');
						break;
					default:
						get_template_part('template-parts/account/orders');
				}
				?>
			</div>
		</div>
	</div>
</main>

<?php get_footer(); ?>

