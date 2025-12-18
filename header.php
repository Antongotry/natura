<!doctype html>
<html <?php language_attributes(); ?>>
	<head>
		<meta charset="<?php bloginfo('charset'); ?>">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<?php wp_head(); ?>
	</head>
	<body <?php body_class(); ?>>
		<?php wp_body_open(); ?>
		<?php
		$is_home = is_front_page();
		$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
		$is_sales = strpos($current_url, '/sales') !== false || is_page('sales');
		$is_alt_header = !$is_home && !$is_sales;
		?>
		<header class="site-header<?php echo !is_front_page() ? ' site-header--not-home' : ''; ?><?php echo $is_alt_header ? ' site-header--alt' : ''; ?>" data-header>
			<div class="container site-header__container">
				<div class="site-header__left">
					<a class="site-header__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('На главную', 'natura'); ?>">
						<img class="site-header__logo-image site-header__logo-image--light" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/logo-white.svg')); ?>" alt="<?php bloginfo('name'); ?>">
						<img class="site-header__logo-image site-header__logo-image--dark" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/logo-dark.svg')); ?>" alt="<?php bloginfo('name'); ?>">
					</a>
					<?php if ($is_alt_header) : ?>
						<div class="site-header__alt-actions">
							<div class="site-header__dropdown-wrapper">
								<button class="site-header__menu-button" type="button" data-menu-dropdown>
									<span class="site-header__menu-button-text">Меню</span>
									<span class="site-header__menu-button-icon">
										<img class="site-header__dropdown-icon site-header__dropdown-icon--default" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/w-chev.svg" alt="">
										<img class="site-header__dropdown-icon site-header__dropdown-icon--hover" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/d-chev.svg" alt="">
									</span>
								</button>
								<div class="site-header__dropdown" data-menu-dropdown-content>
									<nav class="site-header__nav" aria-label="<?php esc_attr_e('Основное меню', 'natura'); ?>">
										<ul class="site-header__menu site-header__menu--dropdown">
											<?php
											$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
											?>
											<li class="site-header__menu-item"><a class="site-header__catalog" href="<?php echo esc_url($catalog_url); ?>">Каталог</a></li>
											<li class="site-header__menu-item"><a href="#insights">Про нас</a></li>
											<li class="site-header__menu-item"><a href="#trusted">Клієнти</a></li>
											<li class="site-header__menu-item"><a href="#payment">Оплата і доставка</a></li>
											<li class="site-header__menu-item"><a href="#cooperation" data-collaboration-modal-open>Співпраця</a></li>
											<li class="site-header__menu-item"><a href="<?php echo esc_url(home_url('/sales')); ?>">Акції</a></li>
											<li class="site-header__menu-item"><a href="#feedback" data-feedback-modal-open>Залишити відгук</a></li>
										</ul>
									</nav>
								</div>
							</div>
							<div class="site-header__search-wrapper">
								<form class="site-header__search-form" role="search" method="get" action="<?php echo esc_url(home_url('/')); ?>">
									<input 
										type="search" 
										class="site-header__search-input" 
										name="s" 
										placeholder="Пошук товару в Natura Market..." 
										value="<?php echo get_search_query(); ?>"
										aria-label="<?php esc_attr_e('Пошук товару', 'natura'); ?>"
										autocomplete="off"
									>
									<button type="button" class="site-header__search-clear" aria-label="<?php esc_attr_e('Очистити пошук', 'natura'); ?>" style="display: none;">
										<svg width="16" height="16" viewBox="0 0 16 16" fill="none" xmlns="http://www.w3.org/2000/svg">
											<path d="M12 4L4 12M4 4L12 12" stroke="rgba(48, 48, 48, 0.4)" stroke-width="1.5" stroke-linecap="round"/>
										</svg>
									</button>
									<button type="submit" class="site-header__search-button" aria-label="<?php esc_attr_e('Виконати пошук', 'natura'); ?>">
										<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/search_24dp_303030_fill0_wght400_grad0_opsz24-1.svg" alt="" class="site-header__search-icon">
									</button>
								</form>
								<div class="site-header__search-results" data-search-results></div>
							</div>
						</div>
					<?php else : ?>
						<?php 
						$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
						?>
						<a class="site-header__catalog" href="<?php echo esc_url($catalog_url); ?>">Каталог</a>
					<?php endif; ?>
				</div>
				<div class="site-header__right">
					<?php if (!$is_alt_header) : ?>
						<nav class="site-header__nav" aria-label="<?php esc_attr_e('Основное меню', 'natura'); ?>">
							<ul class="site-header__menu">
								<li class="site-header__menu-item"><a href="#insights">Про нас</a></li>
								<li class="site-header__separator" aria-hidden="true"></li>
								<li class="site-header__menu-item"><a href="#trusted">Клієнти</a></li>
								<li class="site-header__separator" aria-hidden="true"></li>
								<li class="site-header__menu-item"><a href="#payment">Оплата і доставка</a></li>
								<li class="site-header__separator" aria-hidden="true"></li>
								<li class="site-header__menu-item"><a href="#cooperation" data-collaboration-modal-open>Співпраця</a></li>
								<li class="site-header__separator" aria-hidden="true"></li>
								<li class="site-header__menu-item"><a href="<?php echo esc_url(home_url('/sales')); ?>">Акції</a></li>
								<li class="site-header__separator" aria-hidden="true"></li>
								<li class="site-header__menu-item"><a href="#feedback" data-feedback-modal-open>Залишити відгук</a></li>
							</ul>
						</nav>
					<?php endif; ?>
					<?php if ($is_alt_header) : ?>
						<?php if (is_user_logged_in()) : ?>
							<a class="site-header__account-button" href="<?php echo esc_url(natura_get_account_url()); ?>" aria-label="<?php esc_attr_e('Особистий кабінет', 'natura'); ?>">
								<span class="site-header__account-button-text">Особистий кабінет</span>
							</a>
						<?php else : ?>
							<a class="site-header__account-button" href="<?php echo esc_url(natura_get_auth_url()); ?>" aria-label="<?php esc_attr_e('Увійти', 'natura'); ?>">
								<span class="site-header__account-button-text">Увійти</span>
							</a>
						<?php endif; ?>
					<?php endif; ?>
					<div class="site-header__contacts">
						<a class="site-header__contact" href="tel:+380000000000" aria-label="<?php esc_attr_e('Позвонить', 'natura'); ?>">
							<?php if (is_front_page()) : ?>
								<img class="site-header__contact-icon site-header__contact-icon--light" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/phone-icon.svg')); ?>" alt="">
								<img class="site-header__contact-icon site-header__contact-icon--dark" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/phone-icon-dark.svg')); ?>" alt="">
							<?php else : ?>
								<img class="site-header__contact-icon site-header__contact-icon--light" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/phone-header-1_result.webp" alt="">
								<img class="site-header__contact-icon site-header__contact-icon--dark" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/phone-header-1_result.webp" alt="">
							<?php endif; ?>
						</a>
						<?php if (!$is_alt_header) : ?>
							<a class="site-header__contact" href="mailto:info@natura.com" aria-label="<?php esc_attr_e('Написать письмо', 'natura'); ?>">
								<?php if (is_front_page()) : ?>
									<img class="site-header__contact-icon site-header__contact-icon--light" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/mail-icon.svg')); ?>" alt="">
									<img class="site-header__contact-icon site-header__contact-icon--dark" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/mail-icon-dark.svg')); ?>" alt="">
								<?php elseif ($is_sales) : ?>
									<img class="site-header__contact-icon site-header__contact-icon--light" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/mail-header-1_result.webp" alt="">
									<img class="site-header__contact-icon site-header__contact-icon--dark" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/mail-header-1_result.webp" alt="">
								<?php else : ?>
									<img class="site-header__contact-icon site-header__contact-icon--light" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/mail-icon.svg')); ?>" alt="">
									<img class="site-header__contact-icon site-header__contact-icon--dark" src="<?php echo esc_url(get_theme_file_uri('assets/img/header/mail-icon-dark.svg')); ?>" alt="">
								<?php endif; ?>
							</a>
						<?php endif; ?>
						<?php if ($is_alt_header) : ?>
							<div class="site-header__cart-wrapper">
								<button class="site-header__contact site-header__cart" type="button" data-mini-cart-open aria-label="<?php esc_attr_e('Відкрити кошик', 'natura'); ?>">
									<img class="site-header__contact-icon site-header__contact-icon--light" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/caart_result.webp" alt="">
									<img class="site-header__contact-icon site-header__contact-icon--dark" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/caart_result.webp" alt="">
									<span class="site-header__cart-count" data-cart-count>0</span>
								</button>
							</div>
						<?php endif; ?>
					</div>
				</div>
				<button class="site-header__hamburger" type="button" aria-label="<?php esc_attr_e('Открыть меню', 'natura'); ?>" data-hamburger>
					<img class="site-header__hamburger-icon site-header__hamburger-icon--menu site-header__hamburger-icon--light" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/hamb-white.svg" alt="">
					<img class="site-header__hamburger-icon site-header__hamburger-icon--menu site-header__hamburger-icon--dark" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/hamb-dark.svg" alt="">
					<img class="site-header__hamburger-icon site-header__hamburger-icon--close" src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/group-95.svg" alt="">
				</button>
			</div>
			<div class="site-header__mobile-menu" data-mobile-menu>
				<div class="site-header__mobile-menu-content">
					<nav class="site-header__nav" aria-label="<?php esc_attr_e('Основное меню', 'natura'); ?>">
						<ul class="site-header__menu">
							<li class="site-header__menu-item"><a href="<?php echo esc_url(home_url('/')); ?>">Головна</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<?php
							$catalog_url = function_exists('wc_get_page_permalink') ? wc_get_page_permalink('shop') : (function_exists('wc_get_page_id') ? get_permalink(wc_get_page_id('shop')) : home_url('/catalog'));
							?>
							<li class="site-header__menu-item"><a class="site-header__catalog" href="<?php echo esc_url($catalog_url); ?>">Каталог</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<li class="site-header__menu-item"><a href="#insights">Про нас</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<li class="site-header__menu-item"><a href="#trusted">Клієнти</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<li class="site-header__menu-item"><a href="#payment">Оплата і доставка</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<li class="site-header__menu-item"><a href="#cooperation" data-collaboration-modal-open>Співпраця</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<li class="site-header__menu-item"><a href="<?php echo esc_url(home_url('/sales')); ?>">Акції</a></li>
							<li class="site-header__separator" aria-hidden="true"></li>
							<li class="site-header__menu-item"><a href="#feedback" data-feedback-modal-open>Залишити відгук</a></li>
						</ul>
					</nav>
					<div class="site-header__mobile-contacts">
						<a class="site-header__mobile-contact" href="mailto:info@natura.com" aria-label="<?php esc_attr_e('Написать письмо', 'natura'); ?>">
							<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/group-94.svg" alt="">
						</a>
						<a class="site-header__mobile-contact" href="tel:+380932002211" aria-label="<?php esc_attr_e('Позвонить', 'natura'); ?>">
							<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/group-92.svg" alt="">
						</a>
						<div class="site-header__mobile-phones">
							<a href="tel:+380932002211">+38 (093) 200 22 11</a>
							<a href="tel:+380962002211">+38 (096) 200 22 11</a>
						</div>
					</div>
				</div>
			</div>
		</header>
		<?php if ($is_alt_header) : ?>
			<!-- Cart Notification - вне header для правильного позиционирования -->
			<div class="site-header__cart-notification" data-cart-notification>
				<img class="site-header__cart-notification-image" src="" alt="" data-cart-notification-image>
				<div class="site-header__cart-notification-text">
					<span>Товар додано</span>
					<span>до кошика</span>
				</div>
			</div>
			<!-- Mobile Bottom Navbar -->
			<nav class="site-header__bottom-nav" aria-label="<?php esc_attr_e('Нижнее меню', 'natura'); ?>">
				<?php if (is_user_logged_in()) : ?>
					<a href="<?php echo esc_url(natura_get_account_url()); ?>" class="site-header__bottom-nav-item" aria-label="<?php esc_attr_e('Особистий кабінет', 'natura'); ?>">
						<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/234.svg" alt="<?php esc_attr_e('Особистий кабінет', 'natura'); ?>" class="site-header__bottom-nav-icon">
					</a>
				<?php else : ?>
					<a href="<?php echo esc_url(natura_get_auth_url()); ?>" class="site-header__bottom-nav-item" aria-label="<?php esc_attr_e('Увійти', 'natura'); ?>">
						<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/234.svg" alt="<?php esc_attr_e('Увійти', 'natura'); ?>" class="site-header__bottom-nav-icon">
					</a>
				<?php endif; ?>
				<div class="site-header__bottom-nav-text">
					<span class="site-header__bottom-nav-text-line">Безкоштовна доставка</span>
					<span class="site-header__bottom-nav-text-line">від 1500 гривень</span>
				</div>
				<button class="site-header__bottom-nav-item site-header__bottom-nav-cart" type="button" data-mini-cart-open aria-label="<?php esc_attr_e('Відкрити кошик', 'natura'); ?>">
					<div class="site-header__bottom-nav-cart-icon-wrapper">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="site-header__bottom-nav-cart-icon">
							<path d="M7 18C5.9 18 5.01 18.9 5.01 20C5.01 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM1 2V4H3L6.6 11.59L5.25 14.04C5.09 14.32 5 14.65 5 15C5 16.1 5.9 17 7 17H19V15H7.42C7.28 15 7.17 14.89 7.17 14.75L7.2 14.66L8.1 13H15.55C16.3 13 16.96 12.59 17.3 11.97L20.88 5.5C20.96 5.34 21 5.17 21 5C21 4.45 20.55 4 20 4H5.21L4.27 2H1V2ZM17 18C15.9 18 15.01 18.9 15.01 20C15.01 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18Z" fill="white"/>
						</svg>
					</div>
					<span class="site-header__bottom-nav-cart-count" data-cart-count>0</span>
				</button>
			</nav>
		<?php endif; ?>

