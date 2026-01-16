		<footer class="site-footer">
			<?php
			$is_home = is_front_page();
			$home_url = home_url('/');
			$home_hash_prefix = $is_home ? '' : $home_url;
			?>
			<div class="site-footer__container container">
				<div class="site-footer__brand">
					<a class="site-footer__logo" href="<?php echo esc_url(home_url('/')); ?>" aria-label="<?php esc_attr_e('На головну', 'natura'); ?>">
						<img src="<?php echo esc_url(get_theme_file_uri('assets/img/footer/logo-footer.svg')); ?>" alt="<?php bloginfo('name'); ?>">
					</a>
					<button class="site-footer__scroll-top" type="button" data-scroll-top aria-label="<?php esc_attr_e('Повернутися нагору', 'natura'); ?>">
						<img src="<?php echo esc_url(get_theme_file_uri('assets/img/footer/arrow-top.svg')); ?>" alt="" aria-hidden="true">
					</button>
				</div>
				<!-- Мобильная версия: Меню -->
				<div class="site-footer__section site-footer__section--mobile-menu">
					<p class="site-footer__title"><?php esc_html_e('Меню', 'natura'); ?></p>
					<ul class="site-footer__list" aria-label="<?php esc_attr_e('Меню сайту', 'natura'); ?>">
						<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#insights'); ?>"><?php esc_html_e('Про нас', 'natura'); ?></a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#trusted'); ?>"><?php esc_html_e('Клієнти', 'natura'); ?></a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#payment'); ?>"><?php esc_html_e('Оплата і доставка', 'natura'); ?></a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#cooperation'); ?>"><?php esc_html_e('Співпраця', 'natura'); ?></a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="#sales"><?php esc_html_e('Акції', 'natura'); ?></a></li>
					</ul>
				</div>
				<!-- Мобильная версия: Соцмережі -->
				<div class="site-footer__section site-footer__section--mobile-social">
					<p class="site-footer__title"><?php esc_html_e('Соцмережі', 'natura'); ?></p>
					<ul class="site-footer__list" aria-label="<?php esc_attr_e('Ми в соцмережах', 'natura'); ?>">
						<li class="site-footer__item"><a class="site-footer__link" href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer">Facebook</a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer">Instagram</a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="https://t.me/naturamarket" target="_blank" rel="noopener noreferrer">Telegram</a></li>
					</ul>
				</div>
				<!-- Мобильная версия: Контакты -->
				<div class="site-footer__section site-footer__section--mobile-contacts">
					<p class="site-footer__title"><?php esc_html_e('Контакти', 'natura'); ?></p>
					<ul class="site-footer__list">
						<li class="site-footer__item"><a class="site-footer__link" href="tel:+380932002211">+38 (093) 200 22 11</a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="tel:+380962002211">+38 (096) 200 22 11</a></li>
						<li class="site-footer__item"><a class="site-footer__link" href="mailto:zakaz@naturamarket.kiev.ua">zakaz@naturamarket.kiev.ua</a></li>
					</ul>
				</div>
				<!-- Мобильная версия: Компанія -->
				<div class="site-footer__company-mobile">
					<div class="site-footer__section">
						<p class="site-footer__title"><?php esc_html_e('Компанія', 'natura'); ?></p>
						<div class="site-footer__company-address">
							<p class="site-footer__text site-footer__text--mobile">08140 Київська область,</p>
							<p class="site-footer__text site-footer__text--mobile">Києво-Святошинський район,</p>
							<p class="site-footer__text site-footer__text--mobile">с.Шевченкове, вул. Київська 94</p>
						</div>
					</div>
					<div class="site-footer__company-schedule">
						<p class="site-footer__text site-footer__text--mobile">Пн-Сб: 09:00 – 17:00</p>
						<p class="site-footer__text site-footer__text--mobile">Прийом замовлень на сайті 24/7</p>
					</div>
				</div>
				<!-- Мобильная версия: Кнопки карты -->
				<div class="site-footer__map-mobile">
					<a class="site-footer__map-button-mobile-bottom" href="https://maps.app.goo.gl/BHQsmNbWMVkHtcb59" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Відкрити адресу на Google Maps', 'natura'); ?>">
						Google Maps
					</a>
					<a class="site-footer__map-arrow-mobile" href="https://maps.app.goo.gl/BHQsmNbWMVkHtcb59" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Відкрити адресу на Google Maps', 'natura'); ?>">
						<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/vecto3r.svg" alt="">
					</a>
				</div>
				<!-- Мобильная версия: Юридические ссылки -->
				<div class="site-footer__legal-mobile">
					<a class="site-footer__legal" href="#" aria-label="<?php esc_attr_e('Політика конфіденційності', 'natura'); ?>">
						<?php esc_html_e('Політика конфіденційності', 'natura'); ?>
					</a>
					<a class="site-footer__legal" href="#" aria-label="<?php esc_attr_e('Договір оферти', 'natura'); ?>">
						<?php esc_html_e('Договір оферти', 'natura'); ?>
					</a>
				</div>
			<!-- Мобильная версия: Нижняя строка с Copyright слева и кнопкой наверх справа -->
				<div class="site-footer__bottom-mobile">
					<div class="site-footer__copyright-mobile">
						<p class="site-footer__text">© 2025 Natura Market.<br>All Rights Reserved.</p>
					</div>
					<button class="site-footer__scroll-top site-footer__scroll-top--mobile-bottom" type="button" data-scroll-top aria-label="<?php esc_attr_e('Повернутися нагору', 'natura'); ?>">
						<img src="<?php echo esc_url(get_theme_file_uri('assets/img/footer/arrow-top.svg')); ?>" alt="" aria-hidden="true">
					</button>
				</div>
				<div class="site-footer__navigation">
					<div class="site-footer__section">
						<p class="site-footer__title"><?php esc_html_e('Меню', 'natura'); ?></p>
						<ul class="site-footer__list" aria-label="<?php esc_attr_e('Меню сайту', 'natura'); ?>">
							<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#insights'); ?>"><?php esc_html_e('Про нас', 'natura'); ?></a></li>
							<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#trusted'); ?>"><?php esc_html_e('Клієнти', 'natura'); ?></a></li>
							<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#payment'); ?>"><?php esc_html_e('Оплата і доставка', 'natura'); ?></a></li>
							<li class="site-footer__item"><a class="site-footer__link" href="<?php echo esc_url($home_hash_prefix . '#cooperation'); ?>"><?php esc_html_e('Співпраця', 'natura'); ?></a></li>
							<li class="site-footer__item"><a class="site-footer__link" href="#sales"><?php esc_html_e('Акції', 'natura'); ?></a></li>
							<li class="site-footer__item"><a class="site-footer__link" href="#schedule"><?php esc_html_e('Графік роботи', 'natura'); ?></a></li>
						</ul>
					</div>
					<a class="site-footer__legal" href="#" aria-label="<?php esc_attr_e('Договір оферти', 'natura'); ?>">
						<?php esc_html_e('Договір оферти', 'natura'); ?>
					</a>
				</div>
				<div class="site-footer__contacts">
					<div class="site-footer__contacts-content">
						<div class="site-footer__section site-footer__section--social">
							<p class="site-footer__title"><?php esc_html_e('Соцмережі', 'natura'); ?></p>
							<ul class="site-footer__list" aria-label="<?php esc_attr_e('Ми в соцмережах', 'natura'); ?>">
								<li class="site-footer__item"><a class="site-footer__link" href="https://www.facebook.com/" target="_blank" rel="noopener noreferrer">Facebook</a></li>
								<li class="site-footer__item"><a class="site-footer__link" href="https://www.instagram.com/" target="_blank" rel="noopener noreferrer">Instagram</a></li>
								<li class="site-footer__item"><a class="site-footer__link" href="https://t.me/naturamarket" target="_blank" rel="noopener noreferrer">Telegram</a></li>
							</ul>
						</div>
						<div class="site-footer__section">
							<p class="site-footer__title"><?php esc_html_e('Контакти', 'natura'); ?></p>
							<ul class="site-footer__list">
								<li class="site-footer__item"><a class="site-footer__link" href="tel:+380932002211">+38 (093) 200 22 11</a></li>
								<li class="site-footer__item"><a class="site-footer__link" href="tel:+380962002211">+38 (096) 200 22 11</a></li>
								<li class="site-footer__item"><a class="site-footer__link" href="mailto:zakaz@naturamarket.kiev.ua">zakaz@naturamarket.kiev.ua</a></li>
							</ul>
						</div>
					</div>
					<a class="site-footer__legal" href="#" aria-label="<?php esc_attr_e('Політика конфіденційності', 'natura'); ?>">
						<?php esc_html_e('Політика конфіденційності', 'natura'); ?>
					</a>
				</div>
				<div class="site-footer__company">
					<div class="site-footer__company-content">
						<div class="site-footer__section">
							<p class="site-footer__title"><?php esc_html_e('Компанія', 'natura'); ?></p>
							<div class="site-footer__company-address">
								<p class="site-footer__text">08140 Київська область,</p>
								<p class="site-footer__text">Києво-Святошинський район,</p>
								<p class="site-footer__text">с.Шевченкове, вул. Київська 94</p>
							</div>
						</div>
						<div class="site-footer__company-schedule">
							<p class="site-footer__text">Пн-Сб: 09:00 – 17:00</p>
							<p class="site-footer__text">Прийом замовлень на сайті 24/7</p>
						</div>
						<div class="site-footer__map">
							<a class="site-footer__map-button" href="https://maps.app.goo.gl/BHQsmNbWMVkHtcb59" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Відкрити адресу на Google Maps', 'natura'); ?>">
								Google Maps
							</a>
							<a class="site-footer__map-icon" href="https://maps.app.goo.gl/BHQsmNbWMVkHtcb59" target="_blank" rel="noopener noreferrer" aria-label="<?php esc_attr_e('Відкрити адресу на Google Maps', 'natura'); ?>">
								<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/vecto3r.svg" alt="">
							</a>
						</div>
					</div>
					<div class="site-footer__copyright">
						<p class="site-footer__text">© 2025 Natura Market.</p>
						<p class="site-footer__text">All Rights Reserved.</p>
					</div>
				</div>
			</div>
			<div class="site-footer__bottom">
				<button class="site-footer__scroll-top site-footer__scroll-top--mobile" type="button" data-scroll-top aria-label="<?php esc_attr_e('Повернутися нагору', 'natura'); ?>">
					<img src="<?php echo esc_url(get_theme_file_uri('assets/img/footer/arrow-top.svg')); ?>" alt="" aria-hidden="true">
				</button>
			</div>
		</footer>
		
		<?php
		// Global form popups (collaboration & feedback) should be available on all pages.
		get_template_part( 'template-parts/modals' );
		?>

		<?php if ( class_exists( 'WooCommerce' ) ) : ?>
		<!-- Mini Cart Sidebar -->
		<div class="mini-cart-sidebar" id="mini-cart-sidebar" data-mini-cart>
			<div class="mini-cart-sidebar__overlay" data-mini-cart-close></div>
			<div class="mini-cart-sidebar__content">
				<div class="mini-cart-sidebar__header">
					<h2 class="mini-cart-sidebar__title"><?php esc_html_e( 'Кошик', 'natura' ); ?></h2>
					<button class="mini-cart-sidebar__close" type="button" data-mini-cart-close aria-label="<?php esc_attr_e( 'Закрити', 'natura' ); ?>">
						<svg width="15" height="15" viewBox="0 0 15 15" fill="none" xmlns="http://www.w3.org/2000/svg">
							<path d="M1 1L14 14M14 1L1 14" stroke="white" stroke-width="1.5" stroke-linecap="round"/>
						</svg>
					</button>
				</div>
				<div class="mini-cart-sidebar__body">
					<div class="widget_shopping_cart_content">
						<?php woocommerce_mini_cart(); ?>
					</div>
				</div>
			</div>
		</div>
		<?php endif; ?>
		
		<?php wp_footer(); ?>
	</body>
</html>
