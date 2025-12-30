/**
 * Универсальные утилиты для обработки событий и AJAX запросов
 */

/**
 * Debounce функция для предотвращения множественных вызовов
 * @param {Function} func - Функция для выполнения
 * @param {number} wait - Время задержки в миллисекундах
 * @param {boolean} immediate - Выполнить немедленно при первом вызове
 * @returns {Function} Debounced функция
 */
const debounce = (func, wait = 300, immediate = false) => {
	let timeout;
	return function executedFunction(...args) {
		const later = () => {
			timeout = null;
			if (!immediate) func.apply(this, args);
		};
		const callNow = immediate && !timeout;
		clearTimeout(timeout);
		timeout = setTimeout(later, wait);
		if (callNow) func.apply(this, args);
	};
};

/**
 * Helpers for decimal quantities (например, единица "кг/kg" с шагом 0.1)
 */
const naturaNormalizeUnit = (unit) => String(unit || '').trim().toLowerCase().replace(/\s+/g, '');
const naturaIsKgUnit = (unit) => ['кг', 'kg'].includes(naturaNormalizeUnit(unit));
const naturaGetStepByUnit = (unit) => (naturaIsKgUnit(unit) ? 0.1 : 1);

const naturaParseNumber = (value) => {
	if (value === null || typeof value === 'undefined') return NaN;
	const str = String(value).trim().replace(',', '.');
	const num = parseFloat(str);
	return Number.isFinite(num) ? num : NaN;
};

const naturaGetStepDecimals = (step) => {
	const s = String(step);
	if (s.includes('e-')) {
		const exp = parseInt(s.split('e-')[1], 10);
		return Number.isFinite(exp) ? exp : 0;
	}
	const parts = s.split('.');
	return parts[1] ? parts[1].length : 0;
};

const naturaRoundToStep = (value, step) => {
	const v = naturaParseNumber(value);
	const s = naturaParseNumber(step);
	if (!Number.isFinite(v) || !Number.isFinite(s) || s <= 0) return v;

	const decimals = naturaGetStepDecimals(s);
	const factor = Math.pow(10, decimals);
	return Math.round(v * factor) / factor;
};

const naturaFormatQuantity = (quantity, step) => {
	const q = naturaParseNumber(quantity);
	const s = naturaParseNumber(step);
	if (!Number.isFinite(q)) return '';
	if (!Number.isFinite(s) || s <= 0) return String(q);

	const decimals = naturaGetStepDecimals(s);
	const rounded = naturaRoundToStep(q, s);

	if (decimals <= 0) {
		return String(Math.round(rounded));
	}

	return rounded
		.toFixed(decimals)
		.replace(/\.?0+$/, '');
};

/**
 * Scroll lock helpers (used by mini-cart)
 */
let naturaMiniCartScrollLocked = false;

const naturaLockPageScrollForMiniCart = () => {
	if (naturaMiniCartScrollLocked) return;
	naturaMiniCartScrollLocked = true;

	// Prevent layout shift when scrollbar disappears
	const scrollbarWidth = window.innerWidth - document.documentElement.clientWidth;
	if (scrollbarWidth > 0) {
		document.body.style.paddingRight = `${scrollbarWidth}px`;
		document.documentElement.style.paddingRight = `${scrollbarWidth}px`;
	}
};

const naturaUnlockPageScrollForMiniCart = () => {
	if (!naturaMiniCartScrollLocked) return;
	naturaMiniCartScrollLocked = false;

	document.body.style.paddingRight = '';
	document.documentElement.style.paddingRight = '';
};

/**
 * Универсальная функция для AJAX запросов к WooCommerce
 * @param {string} endpoint - Endpoint WooCommerce (add_to_cart, update_cart, get_refreshed_fragments, remove_from_cart)
 * @param {Object} data - Данные для отправки
 * @param {Object} options - Дополнительные опции (success, error, beforeSend)
 * @returns {Promise|jQuery.jqXHR} Promise или jQuery AJAX объект
 */
const wcAjax = (endpoint, data = {}, options = {}) => {
	if (typeof jQuery === 'undefined' || typeof wc_add_to_cart_params === 'undefined') {
		console.warn('[wcAjax] jQuery или wc_add_to_cart_params не доступны');
		if (options.error) {
			options.error({}, 'error', 'Dependencies not available');
		}
		return Promise.reject('Dependencies not available');
	}

	const url = wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', endpoint);
	const defaultOptions = {
		type: 'POST',
		url: url,
		dataType: 'json',
		data: data,
		...options
	};

	// Добавляем nonce для безопасности, если это не get_refreshed_fragments
	if (endpoint !== 'get_refreshed_fragments' && wc_add_to_cart_params.wc_cart_nonce) {
		if (defaultOptions.data instanceof FormData) {
			defaultOptions.data.append('_wpnonce', wc_add_to_cart_params.wc_cart_nonce);
		} else if (typeof defaultOptions.data === 'object') {
			defaultOptions.data._wpnonce = wc_add_to_cart_params.wc_cart_nonce;
		}
	}

	return jQuery.ajax(defaultOptions);
};

/**
 * Универсальная функция для обновления фрагментов корзины
 * @param {Function} callback - Callback функция после успешного обновления
 * @returns {Promise|jQuery.jqXHR}
 */
const refreshCartFragments = (callback) => {
	return wcAjax('get_refreshed_fragments', {}, {
		success: function(response) {
			if (response && response.fragments) {
				jQuery.each(response.fragments, function(key, value) {
					jQuery(key).replaceWith(value);
				});
			}
			// Не триггерим wc_fragment_refresh здесь: это вызывает повторные запросы (и лаги),
			// потому что WooCommerce и/или наш код могут слушать этот эвент и снова дергать фрагменты.
			jQuery(document.body).trigger('wc_fragments_refreshed');
			jQuery(document.body).trigger('natura_fragments_refreshed');
			jQuery(document.body).trigger('updated_cart_totals');
			if (callback) callback(response);
		}
	});
};

/**
 * Универсальный обработчик событий с debounce
 * @param {HTMLElement|Document|Window} element - Элемент для привязки события
 * @param {string} event - Тип события
 * @param {Function} handler - Обработчик события
 * @param {Object} options - Опции (debounce, passive, capture)
 * @returns {Function} Функция для удаления обработчика
 */
const addEventListenerDebounced = (element, event, handler, options = {}) => {
	const { debounce: debounceTime = 0, passive = false, capture = false, ...restOptions } = options;
	
	let debouncedHandler = handler;
	if (debounceTime > 0) {
		debouncedHandler = debounce(handler, debounceTime);
	}
	
	element.addEventListener(event, debouncedHandler, { passive, capture, ...restOptions });
	
	// Возвращаем функцию для удаления обработчика
	return () => {
		element.removeEventListener(event, debouncedHandler, { passive, capture, ...restOptions });
	};
};

/**
 * Универсальный обработчик кликов с делегированием событий
 * @param {HTMLElement|Document} container - Контейнер для делегирования
 * @param {string} selector - Селектор целевого элемента
 * @param {Function} handler - Обработчик события
 * @param {Object} options - Опции (debounce, preventDefault, stopPropagation)
 * @returns {Function} Функция для удаления обработчика
 */
const delegateClick = (container, selector, handler, options = {}) => {
	const { debounce: debounceTime = 0, preventDefault = true, stopPropagation = false } = options;
	
	const eventHandler = (e) => {
		const target = e.target.closest(selector);
		if (!target) return;
		
		if (preventDefault) e.preventDefault();
		if (stopPropagation) e.stopPropagation();
		
		handler.call(target, e, target);
	};
	
	let debouncedHandler = eventHandler;
	if (debounceTime > 0) {
		debouncedHandler = debounce(eventHandler, debounceTime);
	}
	
	container.addEventListener('click', debouncedHandler, true);
	
	// Возвращаем функцию для удаления обработчика
	return () => {
		container.removeEventListener('click', debouncedHandler, true);
	};
};

// Глобальные функции для работы с корзиной и уведомлениями
const showCartNotification = (productImage) => {
	console.log('[showCartNotification] Вызвана функция, productImage:', productImage);
	const notification = document.querySelector('[data-cart-notification]');
	const notificationImage = document.querySelector('[data-cart-notification-image]');
	const cartButton = document.querySelector('[data-mini-cart-open]');
	
	console.log('[showCartNotification] notification:', notification);
	console.log('[showCartNotification] notificationImage:', notificationImage);
	
	if (!notification) {
		console.error('[showCartNotification] Элемент уведомления не найден в DOM!');
		return;
	}

	// Устанавливаем изображение товара
	if (notificationImage) {
		if (productImage) {
			notificationImage.src = productImage;
			notificationImage.style.display = 'block';
			notificationImage.alt = 'Товар';
			console.log('[showCartNotification] Изображение установлено:', productImage);
		} else {
			notificationImage.style.display = 'none';
			console.log('[showCartNotification] Изображение товара не найдено');
		}
	}

	// Проверяем, мобильная ли версия
	const isMobile = window.innerWidth <= 1025;
	
	// Вычисляем позицию кнопки корзины и позиционируем уведомление
	// Для position: fixed используем координаты относительно viewport (getBoundingClientRect)
	if (isMobile) {
		// В мобильной версии не устанавливаем позицию через JS - CSS полностью контролирует позиционирование
		// Убираем все инлайн стили, чтобы CSS медиа-запрос работал
		notification.style.top = '';
		notification.style.right = '';
		notification.style.left = '';
		notification.style.bottom = '';
		
		console.log('[showCartNotification] Мобильная версия - позиция контролируется CSS');
	} else if (cartButton) {
		const cartButtonRect = cartButton.getBoundingClientRect();
		
		// Позиционируем уведомление справа от кнопки корзины, немного ниже
		// Для fixed позиционирования используем координаты viewport напрямую
		const notificationTop = cartButtonRect.bottom + 10; // 10px отступ снизу от кнопки
		const notificationRight = window.innerWidth - cartButtonRect.right;
		
		notification.style.top = notificationTop + 'px';
		notification.style.right = notificationRight + 'px';
		notification.style.left = 'auto';
		notification.style.bottom = 'auto';
		
		console.log('[showCartNotification] Позиция установлена:', {
			top: notificationTop,
			right: notificationRight,
			cartButtonRect: cartButtonRect
		});
	} else {
		// Fallback: позиционируем в правом верхнем углу
		notification.style.top = '80px';
		notification.style.right = '20px';
		notification.style.left = 'auto';
		notification.style.bottom = 'auto';
		console.log('[showCartNotification] Кнопка корзины не найдена, используется fallback позиция');
	}

	// Показываем уведомление
	notification.classList.add('is-visible');
	
	console.log('[showCartNotification] Уведомление показано');

	// Скрываем через 3 секунды
	setTimeout(() => {
		notification.classList.remove('is-visible');
		console.log('[showCartNotification] Уведомление скрыто');
	}, 3000);
};

// Глобальная функция обновления счетчика корзины (с debounce для предотвращения множественных запросов)
const updateCartCountGlobal = debounce(() => {
	const cartCountElements = document.querySelectorAll('[data-cart-count]');
	if (!cartCountElements || cartCountElements.length === 0) {
		return;
	}

	const setCount = (totalQuantity) => {
		if (totalQuantity > 0) {
			cartCountElements.forEach(element => {
				element.textContent = String(totalQuantity);
				element.style.display = 'flex';
			});
		} else {
			cartCountElements.forEach(element => {
				element.textContent = '0';
				element.style.display = 'none';
			});
		}
	};

	// Если CartManager уже загрузил состояние корзины — обновляем без лишних запросов
	if (typeof CartManager !== 'undefined' && CartManager && CartManager.hasLoaded) {
		let totalQuantity = 0;
		CartManager.cartState.forEach((item) => {
			totalQuantity += naturaParseNumber(item?.quantity) || 0;
		});
		const badgeCount = totalQuantity > 0 ? Math.max(1, Math.round(totalQuantity)) : 0;
		setCount(badgeCount);
		return;
	}

	// Fallback: если состояние еще не загружено, берем количество из фрагментов
	wcAjax('get_refreshed_fragments', {}, {
		success: function(response) {
			if (!response || !response.fragments) {
				return;
			}

			const cartContent = response.fragments['div.widget_shopping_cart_content'] || 
			                    response.fragments['.widget_shopping_cart_content'] ||
			                    Object.values(response.fragments).find(fragment => 
			                    	typeof fragment === 'string' && fragment.includes('cart_item')
			                    );

			if (!cartContent) {
				setCount(0);
				return;
			}

			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = typeof cartContent === 'string' ? cartContent : cartContent.outerHTML || '';
			const cartItems = tempDiv.querySelectorAll('.mini-cart-item, .cart_item, .woocommerce-mini-cart-item');
			let totalQuantity = 0;

			cartItems.forEach(item => {
				const quantityElement = item.querySelector('.quantity, .mini-cart-item__quantity-value, .woocommerce-mini-cart-item__quantity');
				if (quantityElement) {
					const quantityText = quantityElement.textContent.trim();
					const quantity = naturaParseNumber(quantityText) || 0;
					totalQuantity += quantity;
				}
			});

			const badgeCount = totalQuantity > 0 ? Math.max(1, Math.round(totalQuantity)) : 0;
			setCount(badgeCount);
		},
		error: function() {
			setCount(0);
		}
	});
}, 200);

// Глобальный обработчик события added_to_cart (работает на всех страницах)
// Регистрируем после загрузки DOM и jQuery
(function registerCartNotificationHandler() {
	const registerHandler = () => {
		if (typeof jQuery === 'undefined') {
			console.log('[registerCartNotificationHandler] jQuery еще не загружен, повторная попытка через 100ms');
			setTimeout(registerHandler, 100);
			return;
		}
		
		console.log('[registerCartNotificationHandler] Регистрируем обработчик added_to_cart');
		
		// Проверяем наличие элемента уведомления в DOM
		const notification = document.querySelector('[data-cart-notification]');
		console.log('[registerCartNotificationHandler] Элемент уведомления в DOM:', notification);
		
		// Удаляем старый обработчик, если он был
		jQuery(document.body).off('added_to_cart.cartNotification');
		
		// Регистрируем новый обработчик с namespace для избежания конфликтов
		jQuery(document.body).on('added_to_cart.cartNotification', function(event, fragments, cart_hash, button) {
			console.log('[added_to_cart.cartNotification] Событие сработало!', { button, fragments, cart_hash });
			
			// Обновляем счетчик корзины
			updateCartCountGlobal();
			
			// Получаем изображение товара из фрагментов или кнопки
			let productImage = '';
			const buttonEl = button ? (button.jquery ? button[0] : button) : null;
			if (buttonEl) {
				console.log('[added_to_cart.cartNotification] Ищем изображение для кнопки:', buttonEl);
				
				// Пробуем найти изображение в карточке товара
				const productCard = buttonEl.closest('.product-card, li.product, .product');
				if (productCard) {
					const img = productCard.querySelector('.product-card__image-wrapper img, .woocommerce-loop-product__link img, .product-card__image img, img.attachment-woocommerce_thumbnail, img.wp-post-image');
					if (img) {
						productImage = img.src || img.getAttribute('src') || img.getAttribute('data-src') || img.getAttribute('data-lazy-src');
						console.log('[added_to_cart.cartNotification] Найдено изображение в карточке:', productImage);
					}
				}
				
				// Если не нашли, пробуем найти по data-product-id
				if (!productImage && buttonEl.getAttribute('data-product_id')) {
					const productId = buttonEl.getAttribute('data-product_id');
					const productElement = document.querySelector(`[data-product-id="${productId}"], .product[data-product-id="${productId}"], li.product[data-product-id="${productId}"]`);
					if (productElement) {
						const img = productElement.querySelector('.product-card__image-wrapper img, .woocommerce-loop-product__link img, img.attachment-woocommerce_thumbnail, img.wp-post-image');
						if (img) {
							productImage = img.src || img.getAttribute('src') || img.getAttribute('data-src') || img.getAttribute('data-lazy-src');
							console.log('[added_to_cart.cartNotification] Найдено изображение по product-id:', productImage);
						}
					}
				}
				
				// Если все еще не нашли, пробуем найти в результатах поиска
				if (!productImage) {
					const searchResult = buttonEl.closest('.site-header__search-result-item');
					if (searchResult) {
						const img = searchResult.querySelector('.site-header__search-result-image');
						if (img) {
							productImage = img.src || img.getAttribute('src');
							console.log('[added_to_cart.cartNotification] Найдено изображение в результатах поиска:', productImage);
						}
					}
				}
			}
			
			// Показываем уведомление
			console.log('[added_to_cart.cartNotification] Вызываем showCartNotification с изображением:', productImage);
			showCartNotification(productImage);
		});
		
		jQuery(document.body).off('removed_from_cart.cartNotification');
		jQuery(document.body).on('removed_from_cart.cartNotification', function() {
			updateCartCountGlobal();
		});
		
		jQuery(document.body).off('updated_cart_totals.cartNotification');
		jQuery(document.body).on('updated_cart_totals.cartNotification', function() {
			updateCartCountGlobal();
		});
		
		console.log('[registerCartNotificationHandler] Обработчики зарегистрированы');
	};
	
	// Пробуем зарегистрировать сразу
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', registerHandler);
	} else {
		registerHandler();
	}
})();

// Проверка наличия элемента уведомления при загрузке страницы
(function checkNotificationElement() {
	const check = () => {
		const notification = document.querySelector('[data-cart-notification]');
		if (notification) {
			console.log('[checkNotificationElement] Элемент уведомления найден в DOM:', notification);
			console.log('[checkNotificationElement] Родительский элемент:', notification.parentElement);
			console.log('[checkNotificationElement] Computed styles:', {
				display: window.getComputedStyle(notification).display,
				position: window.getComputedStyle(notification).position,
				zIndex: window.getComputedStyle(notification).zIndex
			});
		} else {
			console.warn('[checkNotificationElement] Элемент уведомления НЕ найден в DOM!');
			console.warn('[checkNotificationElement] Это нормально, если вы на главной странице или /sales');
		}
	};
	
	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', check);
	} else {
		check();
	}
})();

// Быстро прячем "Переглянути кошик / View cart" (added_to_cart) и держим quantity-selector вместо кнопки
(function registerReplaceViewCartWithQuantity() {
	const registerHandler = () => {
		if (typeof jQuery === 'undefined') {
			setTimeout(registerHandler, 100);
			return;
		}

		jQuery(document.body)
			.off('added_to_cart.naturaReplaceViewCart')
			.on('added_to_cart.naturaReplaceViewCart', function(event, fragments, cart_hash, button) {
				const $button = button ? (button.jquery ? button : jQuery(button)) : null;
				if (!$button || !$button.length) {
					return;
				}

				const $wrapper = $button.closest('.product-card__button-wrapper');
				if (!$wrapper.length) {
					return;
				}

				const wrapperEl = $wrapper[0];
				const productId = String(
					$button.data('product_id') ||
					$button.attr('data-product_id') ||
					$wrapper.find('.product-card__quantity-wrapper').attr('data-product-id') ||
					''
				);

				// Даем WooCommerce отрисовать added_to_cart, затем сразу прячем
				requestAnimationFrame(() => {
					const viewCartLink = wrapperEl.querySelector('.added_to_cart.wc-forward, .added_to_cart');
					if (viewCartLink) {
						viewCartLink.style.display = 'none';
					}

					if (!productId) {
						return;
					}

					// Синхронизируем UI (кол-во берём из состояния, иначе 1)
					const qty = (CartManager.getQuantity(productId) || 1);
					CartManager.updateUI(productId, qty);
				});
			});
	};

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', registerHandler);
	} else {
		registerHandler();
	}
})();

(function () {
	'use strict';

	const initHeaderScroll = () => {
		const header = document.querySelector('[data-header]');
		if (!header) {
			return;
		}

		const stickyOffset = 1;

		const toggleState = () => {
			if (window.scrollY > stickyOffset) {
				header.classList.add('site-header--scrolled');
			} else {
				header.classList.remove('site-header--scrolled');
			}
		};

		toggleState();
		window.addEventListener('scroll', toggleState, { passive: true });
	};

	const initHamburgerMenu = () => {
		const hamburger = document.querySelector('[data-hamburger]');
		const mobileMenu = document.querySelector('[data-mobile-menu]');
		const header = document.querySelector('[data-header]');

		if (!hamburger || !mobileMenu || !header) {
			return;
		}

		const menuItems = mobileMenu.querySelectorAll('.site-header__menu-item');

		// Определение активного пункта меню
		const setActiveMenuItem = () => {
			const currentUrl = window.location.href;
			const currentPath = window.location.pathname;
			const currentHash = window.location.hash;

			// Сначала убираем все активные классы
			menuItems.forEach((item) => {
				item.classList.remove('is-active');
			});

			// Если есть хеш, ищем соответствующий пункт меню
			if (currentHash) {
				menuItems.forEach((item) => {
					const link = item.querySelector('a');
					if (!link) {
						return;
					}

					const linkUrl = link.getAttribute('href');
					const linkHash = new URL(linkUrl, window.location.origin).hash;

					if (linkHash && currentHash === linkHash) {
						item.classList.add('is-active');
					}
				});
				return;
			}

			// Если хеша нет, проверяем путь
			menuItems.forEach((item) => {
				const link = item.querySelector('a');
				if (!link) {
					return;
				}

				const linkUrl = link.getAttribute('href');
				const linkPath = new URL(linkUrl, window.location.origin).pathname;
				const linkHash = new URL(linkUrl, window.location.origin).hash;

				// Проверка для главной страницы (только если нет хеша)
				if (linkPath === '/' && currentPath === '/' && !linkHash) {
					item.classList.add('is-active');
					return;
				}

				// Проверка для обычных ссылок
				if (linkPath !== '/' && currentPath === linkPath && !linkHash) {
					item.classList.add('is-active');
					return;
				}
			});
		};

		// Устанавливаем активный пункт при загрузке
		setActiveMenuItem();

		// Обновляем при изменении хэша (для якорных ссылок)
		window.addEventListener('hashchange', setActiveMenuItem);

		// Обновляем при скролле (для определения видимой секции)
		let scrollTimeout;
		window.addEventListener('scroll', () => {
			clearTimeout(scrollTimeout);
			scrollTimeout = setTimeout(() => {
				// Обновляем только если меню открыто
				if (mobileMenu.classList.contains('is-active')) {
					setActiveMenuItem();
				}
			}, 100);
		}, { passive: true });

		let scrollPosition = 0;

		const toggleMenu = (e) => {
			if (e) {
				e.preventDefault();
				e.stopPropagation();
				e.stopImmediatePropagation();
			}

			const isActive = mobileMenu.classList.contains('is-active');

			if (isActive) {
				mobileMenu.classList.remove('is-active');
				header.classList.remove('site-header--menu-open');
				document.body.style.overflow = '';
				document.body.style.position = '';
				document.body.style.top = '';
				document.body.style.width = '';
				document.documentElement.style.overflow = '';
				// Восстанавливаем позицию скролла
				requestAnimationFrame(() => {
					window.scrollTo(0, scrollPosition);
				});
			} else {
				// Сохраняем текущую позицию скролла
				scrollPosition = window.pageYOffset || document.documentElement.scrollTop || window.scrollY;
				
				mobileMenu.classList.add('is-active');
				header.classList.add('site-header--menu-open');
				document.body.style.overflow = 'hidden';
				document.body.style.position = 'fixed';
				document.body.style.top = `-${scrollPosition}px`;
				document.body.style.width = '100%';
				document.documentElement.style.overflow = 'hidden';
			}

			return false;
		};

		hamburger.addEventListener('click', toggleMenu);
		hamburger.addEventListener('touchstart', toggleMenu);

		// Закрытие меню при клике на ссылку
		// Используем делегирование событий для правильной работы с модальными окнами
		mobileMenu.addEventListener('click', (e) => {
			const link = e.target.closest('.site-header__menu-item a');
			if (!link) {
				return;
			}
			
			const href = link.getAttribute('href');
			
			// Для модальных окон - закрываем меню после их открытия
			if (link.hasAttribute('data-payment-modal-open') || 
			    link.hasAttribute('data-collaboration-modal-open') || 
			    link.hasAttribute('data-feedback-modal-open')) {
				setTimeout(() => {
					mobileMenu.classList.remove('is-active');
					header.classList.remove('site-header--menu-open');
					document.body.style.overflow = '';
					document.body.style.position = '';
					document.body.style.top = '';
					document.body.style.width = '';
					document.documentElement.style.overflow = '';
					requestAnimationFrame(() => {
						window.scrollTo(0, scrollPosition);
					});
				}, 150);
				return; // Не блокируем открытие модального окна
			}
			
			// Функция закрытия меню
			const closeMenu = () => {
				mobileMenu.classList.remove('is-active');
				header.classList.remove('site-header--menu-open');
				document.body.style.overflow = '';
				document.body.style.position = '';
				document.body.style.top = '';
				document.body.style.width = '';
				document.documentElement.style.overflow = '';
				requestAnimationFrame(() => {
					window.scrollTo(0, scrollPosition);
				});
				setActiveMenuItem();
			};
			
			// Для якорных ссылок используем ту же логику, что и в десктопном меню
			if (href && href.startsWith('#')) {
				const targetId = href.substring(1);
				const targetElement = document.getElementById(targetId);
				
				if (targetElement) {
					e.preventDefault();
					e.stopPropagation();
					
					// Используем сохраненную позицию скролла (которая была сохранена при открытии меню)
					const savedScrollPosition = scrollPosition;
					
					// Закрываем меню
					mobileMenu.classList.remove('is-active');
					header.classList.remove('site-header--menu-open');
					document.body.style.overflow = '';
					document.body.style.position = '';
					document.body.style.top = '';
					document.body.style.width = '';
					document.documentElement.style.overflow = '';
					
					// Восстанавливаем позицию скролла и затем скроллим к цели
					// Используем двойной requestAnimationFrame для гарантированного восстановления
					requestAnimationFrame(() => {
						window.scrollTo(0, savedScrollPosition);
						
						requestAnimationFrame(() => {
							// Дополнительная задержка для полного восстановления скролла
							setTimeout(() => {
								// Теперь скроллим к цели
								// Проверяем, что Lenis доступен и работает
								if (window.lenisInstance && typeof window.lenisInstance.scrollTo === 'function') {
									try {
										window.lenisInstance.scrollTo(targetElement, {
											offset: -100,
											duration: 1.5,
											easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t))
										});
									} catch (err) {
										// Если Lenis не работает, используем fallback
										const headerHeight = header.offsetHeight || 0;
										const targetRect = targetElement.getBoundingClientRect();
										const targetPosition = targetRect.top + window.pageYOffset - headerHeight - 100;
										window.scrollTo({
											top: Math.max(0, targetPosition),
											behavior: 'smooth'
										});
									}
								} else {
									// Fallback для обычного скролла
									const headerHeight = header.offsetHeight || 0;
									const targetRect = targetElement.getBoundingClientRect();
									const targetPosition = targetRect.top + window.pageYOffset - headerHeight - 100;
									window.scrollTo({
										top: Math.max(0, targetPosition),
										behavior: 'smooth'
									});
								}
								
								// Обновляем URL
								if (history.pushState) {
									history.pushState(null, null, href);
								}
								
								setActiveMenuItem();
							}, 400);
						});
					});
				} else {
					// Если элемент не найден, просто закрываем меню
					closeMenu();
				}
			} else if (href && !href.startsWith('javascript:')) {
				// Для обычных ссылок закрываем сразу
				closeMenu();
			}
		});

		// Закрытие меню при нажатии ESC
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && mobileMenu.classList.contains('is-active')) {
				mobileMenu.classList.remove('is-active');
				header.classList.remove('site-header--menu-open');
				document.body.style.overflow = '';
				document.body.style.position = '';
				document.body.style.top = '';
				document.body.style.width = '';
				document.documentElement.style.overflow = '';
				// Восстанавливаем позицию скролла
				window.scrollTo(0, scrollPosition);
			}
		});
	};

	const initHeroAnimation = () => {
		const hero = document.querySelector('.hero');
		if (!hero) {
			return;
		}

		const reveal = entries => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					hero.classList.add('hero--visible');
				}
			});
		};

		const observer = new IntersectionObserver(reveal, {
			root: null,
			rootMargin: '-20% 0px',
			threshold: 0.1,
		});

		observer.observe(hero);
	};

	// Универсальная функция для разбиения текста на слова с сохранением всей HTML структуры
	const splitTextIntoWords = (element, wordClass = 'hero__title-word') => {
		const wordElements = [];
		
		// Используем TreeWalker для обхода только текстовых узлов
		const walker = document.createTreeWalker(
			element,
			NodeFilter.SHOW_TEXT,
			{
				acceptNode: function(node) {
					// Принимаем все текстовые узлы, включая те, что содержат только пробелы
					// Это важно для сохранения пробелов между словами
					return NodeFilter.FILTER_ACCEPT;
				}
			}
		);

		const textNodes = [];
		let node;
		
		// Собираем все текстовые узлы (собираем сначала, потом обрабатываем)
		while (node = walker.nextNode()) {
			textNodes.push(node);
		}

		// Обрабатываем каждый текстовый узел
		textNodes.forEach((textNode) => {
			const text = textNode.textContent;
			const parent = textNode.parentNode;
			
			// Разбиваем текст на слова с сохранением пробелов
			// Используем регулярное выражение, которое захватывает пробелы как отдельные элементы
			const parts = text.split(/(\s+)/);
			
			// Создаем фрагмент для новых узлов
			const fragment = document.createDocumentFragment();
			
			parts.forEach((part) => {
				if (part === '') {
					// Пустая строка - пропускаем
					return;
				} else if (/^\s+$/.test(part)) {
					// Только пробелы - создаем текстовый узел для сохранения пробелов
					fragment.appendChild(document.createTextNode(part));
				} else {
					// Слова (с возможными пробелами по краям) - оборачиваем в span
					const span = document.createElement('span');
					span.className = wordClass;
					span.textContent = part;
					span.setAttribute('aria-hidden', 'true');
					fragment.appendChild(span);
					wordElements.push(span);
				}
			});

			// Заменяем текстовый узел на фрагмент с новыми узлами
			parent.replaceChild(fragment, textNode);
		});

		return wordElements;
	};

	// Универсальная функция для анимации текста
	const animateTextTitle = (selector, containerSelector = null, wordClass = 'hero__title-word') => {
		const title = document.querySelector(selector);
		if (!title) {
			return;
		}

		// Определяем базовый класс для добавления --initialized
		const getBaseClass = (element) => {
			// Для span элементов внутри заголовков используем класс родителя
			if (element.tagName === 'SPAN' && element.parentElement) {
				const parentClasses = element.parentElement.className.split(' ').filter(c => c && !c.includes('--initialized'));
				for (const cls of parentClasses) {
					// Ищем основной класс заголовка (например, collaboration__title, но не collaboration__title-word)
					if (cls.includes('__title') && !cls.includes('__title-') && !cls.includes('__title-word')) {
						return cls;
					}
				}
				// Если не нашли в родителе, проверяем дедушку (для случаев вроде collaboration__title)
				if (element.parentElement.parentElement) {
					const grandparentClasses = element.parentElement.parentElement.className.split(' ').filter(c => c && !c.includes('--initialized'));
					for (const cls of grandparentClasses) {
						if (cls.includes('__title') && !cls.includes('__title-') && !cls.includes('__title-word')) {
							return cls;
						}
					}
				}
			}
			
			const classes = element.className.split(' ').filter(c => c && !c.includes('--initialized'));
			// Ищем класс, который соответствует паттерну заголовка
			for (const cls of classes) {
				if (cls.includes('__title') && !cls.includes('__title-') && !cls.includes('__title-word')) {
					return cls;
				}
			}
			// Если не нашли, берем первый класс
			return classes[0] || null;
		};

		const baseClass = getBaseClass(title);
		
		// Fallback: показываем заголовок через 3 секунды, если анимация не запустилась
		const fallbackTimeout = setTimeout(() => {
			if (baseClass) {
				// Для span элементов добавляем класс к родителю, для остальных - к самому элементу
				if (title.tagName === 'SPAN' && title.parentElement) {
					if (!title.parentElement.classList.contains(baseClass + '--initialized')) {
						title.parentElement.classList.add(baseClass + '--initialized');
					}
				} else {
					if (!title.classList.contains(baseClass + '--initialized')) {
						title.classList.add(baseClass + '--initialized');
					}
				}
			}
		}, 3000);

		// Fallback: если GSAP не загружен, показываем заголовок без анимации
		if (typeof gsap === 'undefined') {
			clearTimeout(fallbackTimeout);
			if (baseClass) {
				// Для span элементов добавляем класс к родителю, для остальных - к самому элементу
				if (title.tagName === 'SPAN' && title.parentElement) {
					title.parentElement.classList.add(baseClass + '--initialized');
				} else {
					title.classList.add(baseClass + '--initialized');
				}
			}
			return;
		}

		// Находим родительский блок для отслеживания видимости
		const parentBlock = title.closest('section') || title.parentElement;
		if (!parentBlock) {
			clearTimeout(fallbackTimeout);
			if (baseClass) {
				// Для span элементов добавляем класс к родителю, для остальных - к самому элементу
				if (title.tagName === 'SPAN' && title.parentElement) {
					title.parentElement.classList.add(baseClass + '--initialized');
				} else {
					title.classList.add(baseClass + '--initialized');
				}
			}
			return;
		}

		// Флаг, чтобы анимация запускалась только один раз
		let hasAnimated = false;

		// Функция запуска анимации
		const initAnimation = () => {
			if (hasAnimated) {
				return;
			}
			hasAnimated = true;
			clearTimeout(fallbackTimeout);

			try {
				// Убеждаемся что контейнер видим (если указан)
				if (containerSelector) {
					gsap.set(containerSelector, { opacity: 1 });
				}

				// Разбиваем текст на слова
				const wordElements = splitTextIntoWords(title, wordClass);

				// Проверяем что слова были созданы
				if (!wordElements || wordElements.length === 0) {
					// Если не удалось разбить на слова, показываем заголовок без анимации
					if (baseClass) {
						// Для span элементов добавляем класс к родителю, для остальных - к самому элементу
						if (title.tagName === 'SPAN' && title.parentElement) {
							title.parentElement.classList.add(baseClass + '--initialized');
						} else {
							title.classList.add(baseClass + '--initialized');
						}
					}
					return;
				}

				// Устанавливаем начальное состояние - слова невидимы
				gsap.set(wordElements, { opacity: 0 });
				// Показываем заголовок после установки начального состояния
				if (baseClass) {
					// Для span элементов добавляем класс к родителю, для остальных - к самому элементу
					if (title.tagName === 'SPAN' && title.parentElement) {
						title.parentElement.classList.add(baseClass + '--initialized');
					} else {
						title.classList.add(baseClass + '--initialized');
					}
				}
				// Небольшая задержка перед началом анимации для предотвращения мерцания
				requestAnimationFrame(() => {
					// Анимация появления слов
					gsap.to(wordElements, {
						opacity: 1,
						duration: 2,
						ease: "sine.out",
						stagger: 0.1,
					});
				});
			} catch (error) {
				// В случае ошибки показываем заголовок с простой анимацией
				if (baseClass) {
					// Для span элементов добавляем класс к родителю, для остальных - к самому элементу
					if (title.tagName === 'SPAN' && title.parentElement) {
						title.parentElement.classList.add(baseClass + '--initialized');
					} else {
						title.classList.add(baseClass + '--initialized');
					}
				}
				if (typeof gsap !== 'undefined') {
					gsap.from(title, {
						opacity: 0,
						duration: 1.5,
						ease: "power2.out",
					});
				}
			}
		};

		// Используем Intersection Observer для отслеживания видимости
		const observerOptions = {
			root: null,
			rootMargin: '0px',
			threshold: 0.1 // Запускаем когда видно хотя бы 10% элемента
		};

		const observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					initAnimation();
					// Отключаем observer после запуска анимации
					observer.unobserve(entry.target);
				}
			});
		}, observerOptions);

		// Начинаем наблюдение за родительским блоком
		observer.observe(parentBlock);
	};

	const initHeroTitleAnimation = () => {
		// Hero заголовок анимируется сразу, так как он виден при загрузке
		const title = document.querySelector('.hero__title');
		if (!title) {
			return;
		}

		// Fallback: показываем заголовок через 3 секунды, если анимация не запустилась
		const fallbackTimeout = setTimeout(() => {
			if (!title.classList.contains('hero__title--initialized')) {
				title.classList.add('hero__title--initialized');
			}
		}, 3000);

		if (typeof gsap === 'undefined') {
			// Если GSAP не загружен, показываем заголовок без анимации
			clearTimeout(fallbackTimeout);
			title.classList.add('hero__title--initialized');
			return;
		}

		const initAnimation = () => {
			try {
				clearTimeout(fallbackTimeout);
				gsap.set('.hero__top', { opacity: 1 });
				const wordElements = splitTextIntoWords(title, 'hero__title-word');
				if (!wordElements || wordElements.length === 0) {
					// Если не удалось разбить на слова, показываем заголовок без анимации
					title.classList.add('hero__title--initialized');
					return;
				}
				// Устанавливаем начальное состояние - слова невидимы
				gsap.set(wordElements, { opacity: 0 });
				// Показываем заголовок после установки начального состояния
				title.classList.add('hero__title--initialized');
				// Небольшая задержка перед началом анимации для предотвращения мерцания
				requestAnimationFrame(() => {
					// Анимация появления слов
					gsap.to(wordElements, {
						opacity: 1,
						duration: 2,
						ease: "sine.out",
						stagger: 0.1,
					});
				});
			} catch (error) {
				clearTimeout(fallbackTimeout);
				// В случае ошибки показываем заголовок с простой анимацией
				title.classList.add('hero__title--initialized');
				if (typeof gsap !== 'undefined') {
					gsap.from(title, {
						opacity: 0,
						duration: 1.5,
						ease: "power2.out",
					});
				}
			}
		};

		initAnimation();
	};

	const initCategoriesCarouselTitleAnimation = () => {
		animateTextTitle('.home-categories-carousel__title span[aria-hidden="true"]', null, 'home-categories-carousel__title-word');
	};

	const initInsightsTitleAnimation = () => {
		// Анимируем desktop версию
		animateTextTitle('.insights__title-desktop[aria-hidden="true"]', null, 'insights__title-word');
		// Анимируем mobile версию
		animateTextTitle('.insights__title-mobile[aria-hidden="true"]', null, 'insights__title-word');
	};

	const initCollaborationTitleAnimation = () => {
		animateTextTitle('.collaboration__title span[aria-hidden="true"]', null, 'collaboration__title-word');
	};

	const initAssortmentTitleAnimation = () => {
		animateTextTitle('.assortment__title span[aria-hidden="true"]', null, 'assortment__title-word');
	};

	const initTrustedTitleAnimation = () => {
		animateTextTitle('.trusted__title span[aria-hidden="true"]', null, 'trusted__title-word');
	};

	const initPaymentTitleAnimation = () => {
		animateTextTitle('.payment__title span[aria-hidden="true"]', null, 'payment__title-word');
	};

	const initFaqTitleAnimation = () => {
		animateTextTitle('.faq__title span[aria-hidden="true"]', null, 'faq__title-word');
	};

	const initCategoriesCarousel = () => {
		const sections = document.querySelectorAll('[data-module="categories-carousel"]');

		if (!sections.length || typeof Swiper === 'undefined') {
			return;
		}

		const updateArrowIcon = (button, mode = 'default') => {
			if (!button) {
				return;
			}

			const icon = button.querySelector('img');
			if (!icon) {
				return;
			}

			const defaultSrc = icon.dataset.iconDefault;
			const hoverSrc = icon.dataset.iconHover;

			if (mode === 'hover' && hoverSrc) {
				if (icon.src !== hoverSrc) {
					icon.src = hoverSrc;
				}
			} else if (defaultSrc && icon.src !== defaultSrc) {
				icon.src = defaultSrc;
			}
		};

		const handlePointerEnter = (event) => {
			const button = event.currentTarget;
			if (button.classList.contains('is-disabled')) {
				return;
			}
			updateArrowIcon(button, 'hover');
		};

		const handlePointerLeave = (event) => {
			updateArrowIcon(event.currentTarget, 'default');
		};

		sections.forEach((section) => {
			const swiperEl = section.querySelector('[data-swiper]');
			const prevButton = section.querySelector('[data-carousel-prev]');
			const nextButton = section.querySelector('[data-carousel-next]');

			if (!swiperEl) {
				return;
			}

			const swiper = new Swiper(swiperEl, {
				slidesPerView: 1,
				spaceBetween: 0,
				loop: false,
				speed: 450,
				watchOverflow: true,
				watchSlidesProgress: true,
				allowTouchMove: true,
				resistance: true,
				resistanceRatio: 0,
				preventClicks: true,
				preventClicksPropagation: true,
				navigation: {
					prevEl: prevButton,
					nextEl: nextButton,
					disabledClass: 'is-disabled',
				},
				pagination: {
					el: section.querySelector('.home-categories-carousel__pagination'),
					clickable: true,
					type: 'bullets',
				},
				breakpoints: {
					320: {
						slidesPerView: 1.2,
						spaceBetween: (window.innerWidth * 2.667) / 100,
					},
					1026: {
						slidesPerView: 4.1,
						spaceBetween: (window.innerWidth * 1.042) / 100,
						slidesOffsetBefore: 0,
					},
				},
				on: {
					init: function() {
						if (prevButton) {
							prevButton.addEventListener('mouseenter', handlePointerEnter);
							prevButton.addEventListener('mouseleave', handlePointerLeave);
							prevButton.addEventListener('focus', handlePointerEnter);
							prevButton.addEventListener('blur', handlePointerLeave);
						}
						if (nextButton) {
							nextButton.addEventListener('mouseenter', handlePointerEnter);
							nextButton.addEventListener('mouseleave', handlePointerLeave);
							nextButton.addEventListener('focus', handlePointerEnter);
							nextButton.addEventListener('blur', handlePointerLeave);
						}
					},
				},
			});
		});
	};

	const initScrollTopButton = () => {
		const buttons = document.querySelectorAll('[data-scroll-top]');

		if (!buttons.length) {
			return;
		}

		const scrollToTop = () => {
			window.scrollTo({
				top: 0,
				behavior: 'smooth',
			});
		};

		buttons.forEach((button) => {
			button.addEventListener('click', scrollToTop);
		});
	};

	const initTrustedCarousels = () => {
		// CSS анимация marquee - не требует JS инициализации
		// Анимация управляется через CSS, пауза при наведении тоже через CSS
		return;
	};

	const initPaymentModal = () => {
		const modal = document.querySelector('[data-payment-modal]');
		const openButtons = document.querySelectorAll('[data-payment-modal-open]');
		
		// Обработка ссылок с data-payment-modal-open
		openButtons.forEach((button) => {
			button.addEventListener('click', (e) => {
				if (button.tagName === 'A') {
					e.preventDefault();
				}
			});
		});
		const closeButtons = document.querySelectorAll('[data-payment-modal-close]');
		const accordionItems = document.querySelectorAll('[data-accordion-item]');
		const paymentSection = document.querySelector('.payment');

		if (!modal) {
			return;
		}

		// Open modal
		openButtons.forEach((button) => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				modal.classList.add('is-active');
				if (paymentSection) {
					paymentSection.classList.add('is-modal-open');
				}
			});
		});

		// Close modal
		closeButtons.forEach((button) => {
			button.addEventListener('click', () => {
				modal.classList.remove('is-active');
				if (paymentSection) {
					paymentSection.classList.remove('is-modal-open');
				}
			});
		});

		// Close on ESC
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && modal.classList.contains('is-active')) {
				modal.classList.remove('is-active');
				if (paymentSection) {
					paymentSection.classList.remove('is-modal-open');
				}
			}
		});

		// Accordion - работает точно так же, как FAQ аккордеон
		accordionItems.forEach((item) => {
			const header = item.querySelector('.payment-modal__accordion-header');
			const toggle = item.querySelector('.payment-modal__accordion-toggle');
			const arrow = item.querySelector('.payment-modal__accordion-arrow');

			if (!header || !toggle || !arrow) {
				return;
			}

			const handleClick = (e) => {
				e.preventDefault();
				e.stopPropagation();

				const isActive = item.classList.contains('is-active');

				// Закрываем все другие элементы
				accordionItems.forEach((accItem) => {
					if (accItem !== item && accItem.classList.contains('is-active')) {
						const accArrow = accItem.querySelector('.payment-modal__accordion-arrow');
						accItem.classList.remove('is-active');
						if (accArrow) {
							accArrow.src = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/1v.svg';
						}
					}
				});

				// Переключаем текущий элемент
				if (isActive) {
					item.classList.remove('is-active');
					arrow.src = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/1v.svg';
				} else {
					item.classList.add('is-active');
					arrow.src = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/2v.svg';
				}
			};

			// Обработчик для всего header - точно так же, как в FAQ
			header.addEventListener('click', handleClick);
			toggle.addEventListener('click', handleClick);
		});
	};

	const initCollaborationModal = () => {
		const modal = document.querySelector('[data-collaboration-modal]');
		const openButtons = document.querySelectorAll('[data-collaboration-modal-open]');
		const closeButtons = document.querySelectorAll('[data-collaboration-modal-close]');

		if (!modal) {
			return;
		}

		// Open modal
		openButtons.forEach((button) => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				modal.classList.add('is-active');
				document.body.style.overflow = 'hidden';
			});
		});

		// Close modal
		closeButtons.forEach((button) => {
			button.addEventListener('click', () => {
				modal.classList.remove('is-active');
				document.body.style.overflow = '';
			});
		});

		// Close on ESC
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && modal.classList.contains('is-active')) {
				modal.classList.remove('is-active');
				document.body.style.overflow = '';
			}
		});
	};

	const initFeedbackModal = () => {
		const modal = document.querySelector('[data-feedback-modal]');
		const openButtons = document.querySelectorAll('[data-feedback-modal-open]');
		const closeButtons = document.querySelectorAll('[data-feedback-modal-close]');

		if (!modal) {
			return;
		}

		// Open modal
		openButtons.forEach((button) => {
			button.addEventListener('click', (e) => {
				e.preventDefault();
				modal.classList.add('is-active');
				document.body.style.overflow = 'hidden';
			});
		});

		// Close modal
		closeButtons.forEach((button) => {
			button.addEventListener('click', () => {
				modal.classList.remove('is-active');
				document.body.style.overflow = '';
			});
		});

		// Close on ESC
		document.addEventListener('keydown', (e) => {
			if (e.key === 'Escape' && modal.classList.contains('is-active')) {
				modal.classList.remove('is-active');
				document.body.style.overflow = '';
			}
		});
	};

	/**
	 * Soft registration on Thank You page (guest -> create account with password)
	 */
	const initSoftAccountThankYou = () => {
		const form = document.querySelector('[data-soft-account-form]');
		if (!form) {
			return;
		}

		if (typeof naturaSoftAccount === 'undefined' || !naturaSoftAccount?.ajax_url || !naturaSoftAccount?.nonce) {
			return;
		}

		const messageEl = form.querySelector('[data-soft-account-message]');
		const submitBtn = form.querySelector('button[type="submit"]');
		const passwordInput = form.querySelector('input[name="password"]');
		const orderId = form.getAttribute('data-order-id');
		const orderKey = form.getAttribute('data-order-key');

		const setMessage = (text, type = '') => {
			if (!messageEl) return;
			messageEl.textContent = text || '';
			messageEl.classList.remove('thankyou-page__message--error', 'thankyou-page__message--success');
			if (type === 'error') {
				messageEl.classList.add('thankyou-page__message--error');
			}
			if (type === 'success') {
				messageEl.classList.add('thankyou-page__message--success');
			}
		};

		form.addEventListener('submit', async (e) => {
			e.preventDefault();

			const password = String(passwordInput?.value || '').trim();
			if (!orderId || !orderKey) {
				setMessage('Некоректні дані замовлення.', 'error');
				return;
			}
			if (!password || password.length < 6) {
				setMessage('Пароль має містити мінімум 6 символів.', 'error');
				return;
			}

			if (submitBtn) {
				submitBtn.disabled = true;
			}
			setMessage('');

			try {
				const body = new URLSearchParams();
				body.set('action', 'natura_create_account_from_order');
				body.set('nonce', naturaSoftAccount.nonce);
				body.set('order_id', orderId);
				body.set('order_key', orderKey);
				body.set('password', password);

				const res = await fetch(naturaSoftAccount.ajax_url, {
					method: 'POST',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
					},
					credentials: 'same-origin',
					body: body.toString(),
				});

				const json = await res.json().catch(() => null);
				if (json && json.success) {
					setMessage(json?.data?.message || 'Кабінет створено. Перенаправляємо…', 'success');
					const redirect = json?.data?.redirect;
					if (redirect) {
						window.location.href = redirect;
					}
					return;
				}

				setMessage(json?.data?.message || 'Помилка. Спробуйте пізніше.', 'error');
			} catch (err) {
				setMessage('Помилка. Спробуйте пізніше.', 'error');
			} finally {
				if (submitBtn) {
					submitBtn.disabled = false;
				}
			}
		});
	};

	const initFAQAccordion = () => {
		const accordionItems = document.querySelectorAll('[data-faq-item]');

		if (!accordionItems.length) {
			return;
		}

		accordionItems.forEach((item) => {
			const header = item.querySelector('.faq__accordion-header');
			const toggle = item.querySelector('.faq__accordion-toggle');
			const arrow = item.querySelector('.faq__accordion-arrow');

			if (!header || !toggle || !arrow) {
				return;
			}

			const handleClick = (e) => {
				e.preventDefault();
				e.stopPropagation();

				const isActive = item.classList.contains('is-active');

				// Закрываем все другие элементы
				accordionItems.forEach((accItem) => {
					if (accItem !== item && accItem.classList.contains('is-active')) {
						const accArrow = accItem.querySelector('.faq__accordion-arrow');
						accItem.classList.remove('is-active');
						if (accArrow) {
							accArrow.src = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/1v.svg';
						}
					}
				});

				// Переключаем текущий элемент
				if (isActive) {
					item.classList.remove('is-active');
					arrow.src = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/1v.svg';
				} else {
					item.classList.add('is-active');
					arrow.src = 'https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/11/2v.svg';
				}
			};

			// Обработчик для всего header
			header.addEventListener('click', handleClick);
			toggle.addEventListener('click', handleClick);
		});
	};

	let lenisInstance = null;

	const initLenis = () => {
		if (typeof Lenis === 'undefined') {
			return null;
		}

		lenisInstance = new Lenis({
			autoRaf: true,
			lerp: 0.08, // ↓ чем меньше, тем плавнее (0.05–0.1 = комфортно)
			smoothWheel: true, // колесо мыши тоже плавное
			smoothTouch: true  // плавность для тачскрола (на мобилке)
		});

		// Сохраняем в window для доступа из других функций
		window.lenisInstance = lenisInstance;

		return lenisInstance;
	};

	const initSmoothAnchorScroll = (lenis) => {
		if (!lenis) {
			return;
		}

		// Обработчик для всех якорных ссылок
		const handleAnchorClick = (e) => {
			const link = e.currentTarget;
			const href = link.getAttribute('href');
			
			// Проверяем, что это якорная ссылка на текущей странице
			if (!href || !href.startsWith('#')) {
				return;
			}

			// Исключаем ссылки с data-атрибутами для модалок
			if (link.hasAttribute('data-payment-modal-open') || 
			    link.hasAttribute('data-collaboration-modal-open') || 
			    link.hasAttribute('data-feedback-modal-open')) {
				return;
			}

			const targetId = href.substring(1);
			const targetElement = document.getElementById(targetId);
			
			if (!targetElement) {
				return;
			}

			// Предотвращаем стандартное поведение
			e.preventDefault();

			// Плавный скролл через Lenis
			lenis.scrollTo(targetElement, {
				offset: -100, // Отступ сверху (можно настроить)
				duration: 1.5, // Длительность анимации
				easing: (t) => Math.min(1, 1.001 - Math.pow(2, -10 * t)) // Плавная easing функция
			});

			// Обновляем URL без перезагрузки страницы
			if (history.pushState) {
				history.pushState(null, null, href);
			}
		};

		// Находим все якорные ссылки в меню (и десктопном, и мобильном)
		const menuLinks = document.querySelectorAll('.site-header__menu a[href^="#"], .site-footer__link[href^="#"]');
		menuLinks.forEach(link => {
			// Проверяем, не находится ли ссылка в мобильном меню
			const isMobileMenuLink = link.closest('[data-mobile-menu]');
			if (!isMobileMenuLink) {
				// Для десктопного меню используем стандартный обработчик
				link.addEventListener('click', handleAnchorClick);
			}
			// Для мобильного меню обработчик уже есть в initHamburgerMenu
		});
	};

	const initInsightsPreview = () => {
		// Disable animation on mobile devices
		if (window.innerWidth <= 1025) {
			return;
		}

		const insightsList = document.querySelector('.insights-list');
		if (!insightsList) {
			return;
		}

		// Ищем preview вне table (прямой дочерний элемент insights-list)
		let preview = insightsList.querySelector(':scope > .insights-list__preview');
		
		if (!preview) {
			// Если не нашли, берем последний
			const allPreviews = insightsList.querySelectorAll('.insights-list__preview');
			if (allPreviews.length === 0) {
				return;
			}
			preview = allPreviews[allPreviews.length - 1];
		}
		
		if (!preview) {
			return;
		}

		const rows = insightsList.querySelectorAll('.insights-list__row');
		if (!rows || rows.length === 0) {
			return;
		}

		const previewImgs = preview.querySelectorAll('img');
		if (!previewImgs || previewImgs.length === 0) {
			return;
		}

		// Инициализируем два img
		const img1 = previewImgs[0];
		const img2 = previewImgs.length > 1 ? previewImgs[1] : previewImgs[0];
		let currentImg = img1;
		let nextImg = img2;
		let isVisible = false;
		let mouseX = 0;
		let mouseY = 0;
		let currentX = 0;
		let currentY = 0;
		let rafId = null;
		let currentImageUrl = '';
		let isChanging = false; // Флаг для предотвращения множественных вызовов

		// Вычисляем размеры и offset (курсор по центру)
		const getOffset = () => {
			const width = parseFloat(getComputedStyle(preview).width);
			const height = width * 0.75;
			return {
				x: width / 2,
				y: height / 2
			};
		};

		let offset = getOffset();

		// Плавное следование за курсором
		const updatePosition = () => {
			if (!isVisible) {
				rafId = null;
				return;
			}

			// Быстрая и плавная реакция
			const targetX = mouseX - offset.x;
			const targetY = mouseY - offset.y;
			
			currentX += (targetX - currentX) * 0.25;
			currentY += (targetY - currentY) * 0.25;

			preview.style.transform = `translate3d(${Math.round(currentX)}px, ${Math.round(currentY)}px, 0)`;

			rafId = requestAnimationFrame(updatePosition);
		};

		// Плавная смена изображения через два img элемента
		const changeImage = (newUrl) => {
			if (!newUrl || currentImageUrl === newUrl || isChanging) {
				return;
			}

			isChanging = true;

			// Определяем какое изображение сейчас видимо
			let visibleImg, hiddenImg;
			if (currentImg.classList.contains('is-visible')) {
				visibleImg = currentImg;
				hiddenImg = nextImg;
			} else {
				visibleImg = nextImg;
				hiddenImg = currentImg;
			}

			// Если это уже то же изображение, которое сейчас загружено, просто переключаемся
			if (hiddenImg.src === newUrl && hiddenImg.complete && hiddenImg.naturalHeight !== 0) {
				doImageChange();
				return;
			}
			
			// Сначала устанавливаем src в скрытый элемент
			hiddenImg.src = '';
			hiddenImg.src = newUrl; // Перезагружаем для гарантии
			
			// Проверяем, загружено ли изображение
			if (hiddenImg.complete && hiddenImg.naturalHeight !== 0 && hiddenImg.src === newUrl) {
				// Изображение уже загружено
				doImageChange();
			} else {
				// Ждем загрузки изображения
				const onLoad = () => {
					hiddenImg.removeEventListener('load', onLoad);
					hiddenImg.removeEventListener('error', onError);
					doImageChange();
				};
				
				const onError = () => {
					hiddenImg.removeEventListener('load', onLoad);
					hiddenImg.removeEventListener('error', onError);
					doImageChange(); // Все равно показываем
				};
				
				hiddenImg.addEventListener('load', onLoad);
				hiddenImg.addEventListener('error', onError);
			}

			function doImageChange() {
				// Убеждаемся что новое изображение готово
				if (!hiddenImg.src || hiddenImg.src === '') {
					hiddenImg.src = newUrl;
				}
				
				// Скрываем текущее изображение (fade out)
				visibleImg.classList.remove('is-visible');
				
				// Принудительно перерисовываем для запуска анимации
				void visibleImg.offsetWidth;
				
				// После половины времени fade out начинаем fade in
				setTimeout(() => {
					// Убеждаемся что старое изображение скрыто
					visibleImg.classList.remove('is-visible');
					
					// Показываем новое изображение (fade in)
					hiddenImg.classList.add('is-visible');
					
					// Принудительно перерисовываем
					void hiddenImg.offsetWidth;
					
					currentImageUrl = newUrl;
					
					// Обновляем ссылки после переключения
					if (visibleImg === currentImg) {
						currentImg = nextImg;
						nextImg = visibleImg;
					} else {
						nextImg = currentImg;
						currentImg = visibleImg;
					}
					
					isChanging = false;
				}, 200); // Задержка для плавного перехода (половина времени transition)
			}
		};

		// Показ превью при ховере на ряд
		rows.forEach((row) => {
			const previewUrl = row.dataset.preview;
			if (!previewUrl) {
				return;
			}

			const handleMouseEnter = (e) => {
				// Обновляем offset на случай ресайза
				offset = getOffset();
				
				// Устанавливаем начальную позицию сразу
				mouseX = e.clientX;
				mouseY = e.clientY;
				currentX = mouseX - offset.x;
				currentY = mouseY - offset.y;
				
				preview.style.transform = `translate3d(${Math.round(currentX)}px, ${Math.round(currentY)}px, 0)`;

				// Показываем preview
				if (!isVisible) {
					isVisible = true;
					preview.classList.add('is-visible');
					
					// Загружаем первое изображение
					currentImg.src = previewUrl;
					currentImageUrl = previewUrl;
					
					// Показываем изображение сразу
					currentImg.classList.add('is-visible');
				} else {
					// Если уже видим, меняем изображение с анимацией
					if (previewUrl !== currentImageUrl && !isChanging) {
						changeImage(previewUrl);
					}
				}

				// Запускаем анимацию
				if (!rafId) {
					rafId = requestAnimationFrame(updatePosition);
				}
			};

			const handleMouseLeave = () => {
				isVisible = false;
				preview.classList.remove('is-visible');
				currentImg.classList.remove('is-visible');
				nextImg.classList.remove('is-visible');
				
				if (rafId) {
					cancelAnimationFrame(rafId);
					rafId = null;
				}
			};

			const handleMouseMove = (e) => {
				mouseX = e.clientX;
				mouseY = e.clientY;

				if (!rafId && isVisible) {
					rafId = requestAnimationFrame(updatePosition);
				}
			};

			row.addEventListener('mouseenter', handleMouseEnter);
			row.addEventListener('mouseleave', handleMouseLeave);
			row.addEventListener('mousemove', handleMouseMove);
		});
	};

	const initParallax = (lenis) => {
		const collaborationBackground = document.querySelector('.collaboration__background');
		const assortmentBackground = document.querySelector('.assortment__background');
		
		const parallaxElements = [];
		
		// Collaboration background - работает на всех устройствах
		if (collaborationBackground) {
			parallaxElements.push(collaborationBackground);
		}
		
		// Assortment background - только на десктопе (ширина > 768px)
		if (assortmentBackground) {
			if (window.innerWidth > 768) {
				parallaxElements.push(assortmentBackground);
			} else {
				// На мобильных сбрасываем transform
				assortmentBackground.style.transform = 'translate3d(0, 0, 0)';
			}
		}
		
		if (!parallaxElements.length) {
			return;
		}

		const updateParallax = (scroll) => {
			const scrolled = scroll !== undefined ? scroll : window.pageYOffset;
			const windowHeight = window.innerHeight;

			parallaxElements.forEach(element => {
				// Для assortment__background на мобильных не применяем parallax
				if (element.classList.contains('assortment__background') && window.innerWidth <= 768) {
					element.style.transform = 'translate3d(0, 0, 0)';
					return;
				}
				
				// Получаем контейнер изображения (родительский элемент)
				const container = element.parentElement;
				if (!container) return;

				const containerRect = container.getBoundingClientRect();
				const containerTop = containerRect.top + window.pageYOffset;
				const containerHeight = containerRect.height;
				const containerBottom = containerTop + containerHeight;

				// Проверяем, виден ли контейнер в viewport
				if (scrolled + windowHeight > containerTop && scrolled < containerBottom) {
					// Вычисляем прогресс скролла относительно контейнера (0 = контейнер вверху, 1 = контейнер внизу)
					const scrollProgress = (scrolled - containerTop + windowHeight) / (containerHeight + windowHeight);
					
					// Параллакс-коэффициент (скорость движения изображения внутри контейнера)
					// 0.11 = изображение движется медленнее скролла, создавая эффект глубины (еще слабее на 25%)
					const parallaxSpeed = 0.11;
					
					// Максимальное смещение (в процентах от высоты контейнера)
					// Изображение будет двигаться вверх/вниз внутри контейнера
					const maxOffset = containerHeight * parallaxSpeed;
					
					// Вычисляем смещение: от -maxOffset до +maxOffset
					// Когда контейнер в центре экрана, offset = 0
					const offset = (scrollProgress - 0.5) * maxOffset * 2;
					
					// Применяем transform только к изображению, контейнер остается на месте
					element.style.transform = `translate3d(0, ${offset}px, 0)`;
				} else {
					// Сбрасываем позицию для элементов вне видимой области
					element.style.transform = 'translate3d(0, 0, 0)';
				}
			});
		};

		// Если Lenis доступен, используем его событие scroll
		if (lenis) {
			lenis.on('scroll', (e) => {
				updateParallax(e.scroll);
			});
		} else {
			// Fallback на обычный scroll, если Lenis не загружен
			let ticking = false;
			const requestTick = () => {
				if (!ticking) {
					window.requestAnimationFrame(() => {
						updateParallax();
						ticking = false;
					});
					ticking = true;
				}
			};
			window.addEventListener('scroll', requestTick, { passive: true });
		}

		window.addEventListener('load', () => updateParallax());
		
		let resizeTimeout;
		window.addEventListener('resize', () => {
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(() => {
				// При изменении размера окна пересчитываем список элементов для parallax
				// (assortment__background должен быть только на десктопе)
				const collaborationBackground = document.querySelector('.collaboration__background');
				const assortmentBackground = document.querySelector('.assortment__background');
				
				// Очищаем массив и пересоздаем
				parallaxElements.length = 0;
				
				if (collaborationBackground) {
					parallaxElements.push(collaborationBackground);
				}
				
				if (assortmentBackground && window.innerWidth > 768) {
					parallaxElements.push(assortmentBackground);
				} else if (assortmentBackground && window.innerWidth <= 768) {
					// Сбрасываем transform для assortment__background на мобильных
					assortmentBackground.style.transform = 'translate3d(0, 0, 0)';
				}
				
				// Обновляем parallax после изменения размера
				updateParallax();
			}, 100);
			clearTimeout(resizeTimeout);
			resizeTimeout = setTimeout(() => updateParallax(), 100);
		}, { passive: true });

		// Первоначальная инициализация
		updateParallax();
	};

	// Инициализация анимации заголовка сразу при готовности DOM
	document.addEventListener('DOMContentLoaded', () => {
		initHeroTitleAnimation();
	});

	// Предотвращение layout shift при загрузке изображений в карточках товаров
	const preventProductCardLayoutShift = () => {
		const productCards = document.querySelectorAll('.product-card');
		if (productCards.length === 0) {
			return;
		}

		productCards.forEach((card) => {
			const imageWrapper = card.querySelector('.product-card__image-wrapper');
			if (!imageWrapper) {
				return;
			}

			const img = imageWrapper.querySelector('img');
			if (img) {
				// Если изображение уже загружено
				if (img.complete && img.naturalHeight !== 0) {
					imageWrapper.style.minHeight = imageWrapper.offsetHeight + 'px';
				} else {
					// Устанавливаем минимальную высоту до загрузки
					const computedStyle = window.getComputedStyle(imageWrapper);
					const height = computedStyle.height;
					if (height && height !== 'auto') {
						imageWrapper.style.minHeight = height;
					}

					// После загрузки изображения
					img.addEventListener('load', () => {
						imageWrapper.style.minHeight = '';
					}, { once: true });
				}
			}
		});
	};

	const initMenuDropdown = () => {
		const dropdownWrappers = document.querySelectorAll('.site-header__dropdown-wrapper');
		
		dropdownWrappers.forEach((wrapper) => {
			const dropdown = wrapper.querySelector('[data-menu-dropdown-content]');
			const trigger = wrapper.querySelector('[data-menu-dropdown]');
			
			if (!dropdown || !trigger) {
				return;
			}

			// Отключение кликов на кнопке
			trigger.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
			});

			// Управление через hover - открытие при наведении на wrapper
			wrapper.addEventListener('mouseenter', () => {
				dropdown.style.opacity = '1';
				dropdown.style.visibility = 'visible';
				dropdown.style.transform = 'translateY(0)';
			});

			// Закрытие при уводе мыши
			wrapper.addEventListener('mouseleave', () => {
				dropdown.style.opacity = '0';
				dropdown.style.visibility = 'hidden';
				dropdown.style.transform = 'translateY(-10px)';
			});
		});
	};

	const initSearch = () => {
		const searchInput = document.querySelector('.site-header__search-input');
		const searchResults = document.querySelector('[data-search-results]');
		const searchForm = document.querySelector('.site-header__search-form');
		const searchButton = document.querySelector('.site-header__search-button');
		const searchClear = document.querySelector('.site-header__search-clear');
		
		if (!searchInput || !searchResults || !searchForm) {
			return;
		}

		let currentRequest = null;

		// Функция для обновления видимости кнопок
		const updateSearchButtons = () => {
			const hasText = searchInput.value.trim().length > 0;
			if (searchButton) {
				if (hasText) {
					searchButton.classList.add('has-text');
				} else {
					searchButton.classList.remove('has-text');
				}
			}
			if (searchClear) {
				searchClear.style.display = hasText ? 'flex' : 'none';
			}
		};

		// Функция для возврата элемента результатов поиска в оригинальное место
		const returnSearchResultsToOriginal = () => {
			if (searchResults.hasAttribute('data-original-parent') && searchResults.parentElement === document.body) {
				const searchWrapper = document.querySelector('.site-header__search-wrapper');
				if (searchWrapper) {
					searchWrapper.appendChild(searchResults);
					searchResults.removeAttribute('data-original-parent');
					// Убираем класс когда элемент возвращен в оригинальное место
					searchResults.classList.remove('site-header__search-results--in-body');
				}
			}
		};

		// Обработчик очистки поля поиска
		if (searchClear) {
			searchClear.addEventListener('click', (e) => {
				e.preventDefault();
				e.stopPropagation();
				searchInput.value = '';
				searchInput.focus();
				updateSearchButtons();
				searchResults.classList.remove('is-visible');
				searchResults.innerHTML = '';
				returnSearchResultsToOriginal();
				if (currentRequest) {
					currentRequest.abort();
				}
			});
		}

		const performSearch = (query) => {
			if (currentRequest) {
				currentRequest.abort();
			}

			if (query.length < 2) {
				searchResults.classList.remove('is-visible');
				searchResults.innerHTML = '';
				returnSearchResultsToOriginal();
				return;
			}

			// AJAX запрос для поиска
			const ajaxUrl = (typeof naturaSearch !== 'undefined' && naturaSearch.ajax_url) ? naturaSearch.ajax_url : '/wp-admin/admin-ajax.php';
			const nonce = (typeof naturaSearch !== 'undefined' && naturaSearch.nonce) ? naturaSearch.nonce : '';
			
			currentRequest = jQuery.ajax({
				url: ajaxUrl,
				type: 'POST',
				data: {
					action: 'natura_product_search',
					query: query,
					nonce: nonce
				},
				success: function(response) {
					if (response.success && response.data) {
						displaySearchResults(response.data);
					} else {
						searchResults.classList.remove('is-visible');
						searchResults.innerHTML = '';
						returnSearchResultsToOriginal();
					}
				},
				error: function() {
					searchResults.classList.remove('is-visible');
					searchResults.innerHTML = '';
					returnSearchResultsToOriginal();
				}
			});
		};

		const positionSearchResults = () => {
			// Для мобильной версии перемещаем элемент в body чтобы выйти из stacking context хедера (для iOS Safari)
			if (window.innerWidth <= 1025 && searchForm && searchResults.classList.contains('is-visible')) {
				// Перемещаем в body если еще не там
				if (searchResults.parentElement !== document.body) {
					document.body.appendChild(searchResults);
					searchResults.setAttribute('data-original-parent', '');
					// Добавляем класс для сохранения стилей когда элемент в body
					searchResults.classList.add('site-header__search-results--in-body');
				}
				
				const searchFormRect = searchForm.getBoundingClientRect();
				// Позиционируем относительно viewport
				searchResults.style.position = 'fixed';
				searchResults.style.top = (searchFormRect.bottom + 1.282 * window.innerWidth / 100) + 'px';
				searchResults.style.left = searchFormRect.left + 'px';
				searchResults.style.right = 'auto';
				searchResults.style.bottom = 'auto';
				searchResults.style.width = searchFormRect.width + 'px';
				searchResults.style.zIndex = '1000007';
			} else {
				// Для десктопа возвращаем в оригинальное место если был перемещен
				returnSearchResultsToOriginal();
				// Для десктопа используем стандартное позиционирование
				searchResults.style.position = '';
				searchResults.style.top = '';
				searchResults.style.left = '';
				searchResults.style.right = '';
				searchResults.style.bottom = '';
				searchResults.style.width = '';
				searchResults.style.zIndex = '';
				searchResults.style.webkitTransform = '';
				searchResults.style.transform = '';
				searchResults.style.willChange = '';
			}
		};

		const displaySearchResults = (products) => {
			if (!products || products.length === 0) {
				// Показываем сообщение об отсутствии результатов
				const query = searchInput.value.trim();
				if (query.length >= 2) {
					searchResults.innerHTML = '<div class="site-header__search-result-empty">Товарів з такою назвою не знайдено</div>';
					searchResults.classList.add('is-visible');
					setTimeout(positionSearchResults, 0);
				} else {
					searchResults.classList.remove('is-visible');
					searchResults.innerHTML = '';
					returnSearchResultsToOriginal();
				}
				return;
			}

			let html = '';
			products.forEach((product) => {
				const image = product.image || '';
				const title = product.title || '';
				const unit = product.unit || 'шт';
				const price = product.price || '';
				const productId = product.id || '';
				const permalink = product.permalink || '#';

				// Обрезаем название до 2 слов
				const titleWords = title.split(' ');
				let displayTitle = title;
				if (titleWords.length > 2) {
					displayTitle = titleWords.slice(0, 2).join(' ') + '...';
				}

				// Для мобильной версии используем простую иконку корзины
				const isMobile = window.innerWidth <= 1025;
				const cartButtonHtml = isMobile 
					? `<button type="button" class="site-header__search-result-cart-button" data-product_id="${productId}" aria-label="Додати в кошик: ${title}">
							<div class="site-header__search-result-cart-icon-wrapper">
								<svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg" class="site-header__search-result-cart-icon">
									<path d="M7 18C5.9 18 5.01 18.9 5.01 20C5.01 21.1 5.9 22 7 22C8.1 22 9 21.1 9 20C9 18.9 8.1 18 7 18ZM1 2V4H3L6.6 11.59L5.25 14.04C5.09 14.32 5 14.65 5 15C5 16.1 5.9 17 7 17H19V15H7.42C7.28 15 7.17 14.89 7.17 14.75L7.2 14.66L8.1 13H15.55C16.3 13 16.96 12.59 17.3 11.97L20.88 5.5C20.96 5.34 21 5.17 21 5C21 4.45 20.55 4 20 4H5.21L4.27 2H1V2ZM17 18C15.9 18 15.01 18.9 15.01 20C15.01 21.1 15.9 22 17 22C18.1 22 19 21.1 19 20C19 18.9 18.1 18 17 18Z" fill="white"/>
								</svg>
							</div>
						</button>`
					: `<div class="site-header__search-result-actions">
							<div class="product-card__button-wrapper">
								<a href="${permalink}?add-to-cart=${productId}" class="button product_type_simple add_to_cart_button ajax_add_to_cart" data-product_id="${productId}" data-product_sku="" aria-label="Додати в кошик: ${title}" rel="nofollow" role="button">Додати в кошик</a>
								<div class="product-card__quantity-wrapper quantity-wrapper" data-product-id="${productId}" style="display: none;">
									<button type="button" class="quantity-button quantity-button--minus" aria-label="Зменшити кількість">
										<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/minus-bas.svg" alt="" class="quantity-button__icon quantity-button__icon--default">
										<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/minus-hover.svg" alt="" class="quantity-button__icon quantity-button__icon--hover">
									</button>
									<div class="quantity-input-wrapper">
										<input type="number" class="input-text qty text product-card__quantity-input" name="quantity" value="1" min="1" step="1" data-product-id="${productId}">
										<span class="quantity-unit">${unit}</span>
									</div>
									<button type="button" class="quantity-button quantity-button--plus" aria-label="Збільшити кількість">
										<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/plus-bas.svg" alt="" class="quantity-button__icon quantity-button__icon--default">
										<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/plus-hover.svg" alt="" class="quantity-button__icon quantity-button__icon--hover">
									</button>
								</div>
							</div>
						</div>`;

				html += `
					<div class="site-header__search-result-item">
						<img src="${image}" alt="${title}" class="site-header__search-result-image">
						<div class="site-header__search-result-content">
							<div class="site-header__search-result-title-wrapper">
								<span class="site-header__search-result-title">${displayTitle}</span><span class="site-header__search-result-unit">(${unit})</span>
							</div>
							<div class="site-header__search-result-price">${price}</div>
						</div>
						${cartButtonHtml}
					</div>
				`;
			});

			searchResults.innerHTML = html;
			searchResults.classList.add('is-visible');
			setTimeout(positionSearchResults, 0);

			// Проверяем корзину для товаров в результатах поиска
			if (typeof jQuery !== 'undefined' && typeof wc_add_to_cart_params !== 'undefined') {
				jQuery.ajax({
					type: 'POST',
					url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
					dataType: 'json',
					success: function(response) {
						if (!response || !response.fragments) {
							return;
						}
						
						// Парсим HTML фрагментов для получения списка товаров в корзине
						const cartContent = response.fragments['div.widget_shopping_cart_content'] || 
						                    response.fragments['.widget_shopping_cart_content'] ||
						                    Object.values(response.fragments).find(fragment => 
						                    	typeof fragment === 'string' && fragment.includes('cart_item')
						                    );
						
						if (!cartContent) {
							return;
						}
						
						// Создаем временный элемент для парсинга
						const tempDiv = document.createElement('div');
						tempDiv.innerHTML = typeof cartContent === 'string' ? cartContent : cartContent.outerHTML || '';
						
						// Находим все товары в корзине
						const cartItems = tempDiv.querySelectorAll('.mini-cart-item, .cart_item, [data-product-id]');
						const cartProductIds = new Set();
						
						cartItems.forEach(item => {
							const productId = item.getAttribute('data-product-id') || 
							                 item.querySelector('[data-product-id]')?.getAttribute('data-product-id') ||
							                 item.querySelector('[data-product_id]')?.getAttribute('data-product_id');
							if (productId) {
								cartProductIds.add(productId);
							}
						});
						
						// Показываем quantity-wrapper для товаров в корзине
						searchResults.querySelectorAll('.site-header__search-result-item').forEach(item => {
							const buttonWrapper = item.querySelector('.product-card__button-wrapper');
							if (!buttonWrapper) {
								return;
							}
							
							const addToCartButton = buttonWrapper.querySelector('.add_to_cart_button, .ajax_add_to_cart');
							const productId = addToCartButton?.getAttribute('data-product_id') || 
							                 addToCartButton?.getAttribute('data-product-id');
							
							if (productId && cartProductIds.has(productId)) {
								const quantityWrapper = buttonWrapper.querySelector('.product-card__quantity-wrapper[data-product-id="' + productId + '"]');
								if (quantityWrapper) {
									// Скрываем кнопку
									if (addToCartButton) {
										addToCartButton.style.display = 'none';
									}
									// Показываем quantity-wrapper
									quantityWrapper.style.display = 'flex';
									quantityWrapper.classList.add('show');
									
								}
							}
						});
					},
					error: function() {
						console.log('[initSearch] Ошибка при проверке корзины для результатов поиска');
					}
				});
			}

			
			// Инициализация обработчиков для кнопок корзины в мобильной версии
			const mobileCartButtons = searchResults.querySelectorAll('.site-header__search-result-cart-button');
			mobileCartButtons.forEach((button) => {
				// Убираем атрибут data-mini-cart-open чтобы избежать конфликта с обработчиком из initMiniCart
				button.removeAttribute('data-mini-cart-open');
				
				// Добавляем обработчик для добавления в корзину и открытия мини-корзины
				button.addEventListener('click', (e) => {
					e.preventDefault();
					e.stopPropagation();
					
					const productId = button.getAttribute('data-product_id');
					if (!productId || typeof jQuery === 'undefined' || typeof wc_add_to_cart_params === 'undefined') {
						return;
					}
					
					// Добавляем товар в корзину
					jQuery(document.body).trigger('adding_to_cart', [button, {}]);
					wcAjax('add_to_cart', {
						product_id: productId,
						quantity: 1,
					}, {
						success: function(response) {
							if (response.error && response.product_url) {
								window.location = response.product_url;
								return;
							}
							
							// Обновляем корзину
							if (response.fragments) {
								jQuery.each(response.fragments, function(key, value) {
									jQuery(key).replaceWith(value);
								});
							}
							
							// Получаем изображение товара для уведомления
							let productImage = '';
							const searchResultItem = button.closest('.site-header__search-result-item');
							if (searchResultItem) {
								const img = searchResultItem.querySelector('.site-header__search-result-image');
								if (img) {
									productImage = img.src || img.getAttribute('src');
								}
							}
							
							// Показываем уведомление
							if (typeof showCartNotification === 'function') {
								showCartNotification(productImage);
							}
							
								// Открываем корзину (через data-mini-cart-open)
								jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, jQuery(button)]);
								
								// Открываем мини-корзину после обновления фрагментов
								// Функция для открытия мини-корзины
								const openMiniCartNow = () => {
									const miniCart = document.getElementById('mini-cart-sidebar');
									if (miniCart) {
										miniCart.classList.add('is-open');
										document.body.classList.add('mini-cart-open');
										document.documentElement.classList.add('mini-cart-open');
										naturaLockPageScrollForMiniCart();
										requestAnimationFrame(() => {
											const scrollEl =
												miniCart.querySelector('.woocommerce-mini-cart') ||
												miniCart.querySelector('.mini-cart-sidebar__body');
											if (scrollEl) {
												if (!scrollEl.hasAttribute('tabindex')) {
													scrollEl.setAttribute('tabindex', '-1');
												}
												try {
													scrollEl.focus({ preventScroll: true });
												} catch (e) {
													try { scrollEl.focus(); } catch (_) {}
												}
											}
										});
										console.log('[search] Товар добавлен, мини-корзина открыта');
										return true;
									}
									return false;
								};
								
								// Пробуем открыть сразу
								openMiniCartNow();
								
								// Также пробуем через небольшие интервалы для надежности
								setTimeout(openMiniCartNow, 100);
								setTimeout(openMiniCartNow, 200);
								setTimeout(openMiniCartNow, 300);
								setTimeout(openMiniCartNow, 500);
						},
						error: function() {
							console.log('[initSearch] Ошибка при добавлении товара в корзину');
						}
					});
				});
			});

			// Инициализация обработчиков добавления в корзину (для десктопа)
			const addToCartButtons = searchResults.querySelectorAll('.add_to_cart_button');
			addToCartButtons.forEach((button) => {
				// Убираем старые обработчики
				const newButton = button.cloneNode(true);
				button.parentNode.replaceChild(newButton, button);
				
				newButton.addEventListener('click', (e) => {
					e.preventDefault();
					e.stopPropagation();
					const productId = newButton.getAttribute('data-product_id');
					if (productId && typeof jQuery !== 'undefined' && typeof wc_add_to_cart_params !== 'undefined') {
						jQuery(document.body).trigger('adding_to_cart', [newButton, {}]);
						wcAjax('add_to_cart', {
							product_id: productId,
							quantity: 1,
						}, {
							success: function(response) {
								if (response.error && response.product_url) {
									window.location = response.product_url;
									return;
								}
								
								// Обновляем корзину
								if (response.fragments) {
									jQuery.each(response.fragments, function(key, value) {
										jQuery(key).replaceWith(value);
									});
								}
								
								// Показываем quantity-wrapper вместо кнопки
								const buttonWrapper = newButton.closest('.product-card__button-wrapper');
								if (buttonWrapper) {
									const quantityWrapper = buttonWrapper.querySelector('.product-card__quantity-wrapper');
									if (quantityWrapper) {
										newButton.style.display = 'none';
										quantityWrapper.style.display = 'flex';
										quantityWrapper.classList.add('show');
									}
								}
								
								// Получаем изображение товара из результата поиска
								let productImage = '';
								const searchResultItem = newButton.closest('.site-header__search-result-item');
								if (searchResultItem) {
									const img = searchResultItem.querySelector('.site-header__search-result-image');
									if (img) {
										productImage = img.src || img.getAttribute('src');
									}
								}
								
								jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, jQuery(newButton)]);
							}
						});
					}
				});
			});
		};

		// Поиск при вводе с debounce для предотвращения множественных запросов
		const debouncedPerformSearch = debounce((query) => {
			performSearch(query);
		}, 300);

		addEventListenerDebounced(searchInput, 'input', (e) => {
			updateSearchButtons();
			const query = e.target.value.trim();
			debouncedPerformSearch(query);
		});

		// Инициализация видимости кнопок при загрузке
		updateSearchButtons();

		// Закрытие при клике вне
		document.addEventListener('click', (e) => {
			if (!searchForm.contains(e.target) && !searchResults.contains(e.target)) {
				searchResults.classList.remove('is-visible');
				returnSearchResultsToOriginal();
			}
		});

		// Предотвращение отправки формы
		searchForm.addEventListener('submit', (e) => {
			e.preventDefault();
			const query = searchInput.value.trim();
			if (query.length >= 2) {
				const homeUrl = window.location.origin;
				window.location.href = `${homeUrl}/?s=${encodeURIComponent(query)}&post_type=product`;
			}
		});

		// Закрываем выпадающий список при скролле для мобильной версии
		if (window.innerWidth <= 1025) {
			let lastScrollTop = window.pageYOffset || document.documentElement.scrollTop;
			
			const handleScroll = debounce(() => {
				if (searchResults.classList.contains('is-visible')) {
					searchResults.classList.remove('is-visible');
					returnSearchResultsToOriginal();
				}
			}, 100);

			addEventListenerDebounced(window, 'scroll', () => {
				if (searchResults.classList.contains('is-visible')) {
					const currentScrollTop = window.pageYOffset || document.documentElement.scrollTop;
					// Проверяем, что произошел реальный скролл (не просто событие)
					if (Math.abs(currentScrollTop - lastScrollTop) > 5) {
						handleScroll();
					}
					lastScrollTop = currentScrollTop;
				}
			}, { passive: true });
			
			// Обновляем позицию при изменении размера окна с debounce
			const debouncedPositionSearchResults = debounce(positionSearchResults, 10);
			addEventListenerDebounced(window, 'resize', () => {
				if (searchResults.classList.contains('is-visible')) {
					debouncedPositionSearchResults();
				}
			});
		}
	};

	document.addEventListener('DOMContentLoaded', () => {
		const lenis = initLenis();
		initHeaderScroll();
		initHamburgerMenu();
		initMenuDropdown();
		initSearch();
		initHeroAnimation();
		initCategoriesCarousel();
		initCategoriesCarouselTitleAnimation();
		initInsightsTitleAnimation();
		initCollaborationTitleAnimation();
		initAssortmentTitleAnimation();
		initTrustedTitleAnimation();
		initPaymentTitleAnimation();
		initFaqTitleAnimation();
		initScrollTopButton();
		initTrustedCarousels();
		initPaymentModal();
		initCollaborationModal();
		initFeedbackModal();
		initSoftAccountThankYou();
		initFAQAccordion();
		initInsightsPreview();
		initParallax(lenis);
		initPromoCodeCopy();
		initSmoothAnchorScroll(lenis);
		// Инициализируем CartManager первым
		CartManager.init();
		initQuantityButtons();
		initRelatedProductsCarousel();
		initProductCardAddToCart();
		initMiniCart();
		initShopFilterDrawer();
		initShopArchiveAjaxNavigation();
		preventProductCardLayoutShift();
	});
})();

/**
 * Менеджер состояния корзины - единый источник истины
 * Предотвращает конфликты и мигание интерфейса
 */
const CartManager = {
	// Текущее состояние корзины (кэш)
	cartState: new Map(),
	// Флаг: состояние корзины загружено хотя бы 1 раз
	hasLoaded: false,
	// Флаг активного обновления
	isUpdating: false,
	// Очередь обновлений (по productId)
	updateQueue: [],
	// Набор productId, которые уже стоят в очереди (чтобы не дублировать)
	queuedProducts: new Set(),
	// Последнее желаемое количество по productId (для coalescing)
	desiredQuantities: new Map(),
	// Резолверы промисов по productId (чтобы отдавать результат на финальное состояние)
	pendingResolvers: new Map(),
	// Таймер синхронизации
	syncTimer: null,
	
	/**
	 * Инициализация - загружаем состояние корзины
	 */
	init() {
		this.bindWooEvents();
		
		const loadPromise = this.loadCartState();
		if (loadPromise && typeof loadPromise.finally === 'function') {
			loadPromise.finally(() => {
				this.syncUI();
				if (typeof updateCartCountGlobal === 'function') {
					updateCartCountGlobal();
				}
			});
		} else {
			this.syncUI();
			if (typeof updateCartCountGlobal === 'function') {
				updateCartCountGlobal();
			}
		}
	},
	
	/**
	 * Загрузка состояния корзины с сервера
	 */
	loadCartState() {
		if (typeof jQuery === 'undefined' || typeof wc_add_to_cart_params === 'undefined') {
			this.cartState.clear();
			this.hasLoaded = true;
			return Promise.resolve();
		}
		
		return wcAjax('get_refreshed_fragments', {}, {
			success: (response) => {
				if (!response || !response.fragments) {
					this.cartState.clear();
					this.hasLoaded = true;
					return;
				}
				
				this.updateStateFromFragments(response.fragments);
			},
			error: () => {
				this.cartState.clear();
				this.hasLoaded = true;
			}
		});
	},
	
	/**
	 * Подписка на события WooCommerce, чтобы держать состояние без лишних запросов
	 */
	bindWooEvents() {
		if (typeof jQuery === 'undefined') {
			return;
		}
		
		const handleFragments = (fragments) => {
			if (!fragments) return;
			this.updateStateFromFragments(fragments);
			this.syncUI();
			if (typeof updateCartCountGlobal === 'function') {
				updateCartCountGlobal();
			}
		};
		
		jQuery(document.body)
			.off('added_to_cart.cartManager removed_from_cart.cartManager')
			.on('added_to_cart.cartManager', (event, fragments) => {
				handleFragments(fragments);
			})
			.on('removed_from_cart.cartManager', (event, fragments) => {
				handleFragments(fragments);
			});
		
		// Когда фрагменты обновились, но fragments не передали — читаем из DOM
		jQuery(document.body)
			.off('wc_fragments_refreshed.cartManager natura_fragments_refreshed.cartManager')
			.on('wc_fragments_refreshed.cartManager natura_fragments_refreshed.cartManager', () => {
				this.updateStateFromDOM();
				this.syncUI();
				if (typeof updateCartCountGlobal === 'function') {
					updateCartCountGlobal();
				}
			});
	},
	
	/**
	 * Извлекаем HTML мини-корзины из fragments
	 */
	extractCartContentFromFragments(fragments) {
		if (!fragments) return null;
		
		return fragments['div.widget_shopping_cart_content'] || 
		       fragments['.widget_shopping_cart_content'] ||
		       Object.values(fragments).find(frag => 
		       	typeof frag === 'string' && (
		       		frag.includes('woocommerce-mini-cart') ||
		       		frag.includes('mini-cart-item') ||
		       		frag.includes('cart_item')
		       	)
		       );
	},
	
	/**
	 * Парсим состояние корзины из DOM-дерева
	 */
	parseCartStateFromRoot(root) {
		const nextState = new Map();
		if (!root) return nextState;
		
		const items = root.querySelectorAll('.woocommerce-mini-cart-item, .mini-cart-item, .mini_cart_item, .cart_item');
		items.forEach((item) => {
			const productId = item.getAttribute('data-product-id') ||
			                 item.querySelector('[data-product-id]')?.getAttribute('data-product-id') ||
			                 item.querySelector('[data-product_id]')?.getAttribute('data-product_id');
			
			if (!productId) return;
			
			let quantity = 0;
			const qtyValue = item.querySelector('.mini-cart-item__quantity-value');
			if (qtyValue) {
				quantity = naturaParseNumber(qtyValue.textContent) || 0;
			} else {
				const qtyEl = item.querySelector('.quantity, .woocommerce-mini-cart-item__quantity');
				if (qtyEl) {
					const match = qtyEl.textContent.match(/(\d+(?:[.,]\d+)?)/);
					quantity = match ? (naturaParseNumber(match[1]) || 0) : 0;
				}
			}
			
			const cartItemKey = item.getAttribute('data-cart-item-key') ||
			                    item.querySelector('[data-cart-item-key]')?.getAttribute('data-cart-item-key') ||
			                    item.getAttribute('data-cart_item_key') ||
			                    item.querySelector('[data-cart_item_key]')?.getAttribute('data-cart_item_key') ||
			                    item.querySelector('.mini-cart-item__remove')?.getAttribute('data-cart_item_key') ||
			                    item.querySelector('.remove_from_cart_button, .remove')?.getAttribute('data-cart_item_key') ||
			                    item.querySelector('.mini-cart-item__remove')?.getAttribute('href')?.match(/remove_item=([^&]+)/)?.[1];
			
			if (quantity > 0) {
				nextState.set(String(productId), {
					quantity,
					cartItemKey: cartItemKey || null
				});
			}
		});
		
		return nextState;
	},
	
	/**
	 * Обновляем состояние из fragments (без дополнительного запроса)
	 */
	updateStateFromFragments(fragments) {
		const cartContent = this.extractCartContentFromFragments(fragments);
		if (!cartContent) {
			this.cartState.clear();
			this.hasLoaded = true;
			return;
		}
		
		const tempDiv = document.createElement('div');
		tempDiv.innerHTML = typeof cartContent === 'string' ? cartContent : cartContent.outerHTML || '';
		
		const nextState = this.parseCartStateFromRoot(tempDiv);
		this.cartState.clear();
		nextState.forEach((value, key) => {
			this.cartState.set(key, value);
		});
		
		this.hasLoaded = true;
	},
	
	/**
	 * Обновляем состояние из текущего DOM (когда fragments не передали)
	 */
	updateStateFromDOM() {
		const containers = document.querySelectorAll('.widget_shopping_cart_content');
		if (!containers || containers.length === 0) {
			this.cartState.clear();
			this.hasLoaded = true;
			return;
		}
		
		const merged = new Map();
		containers.forEach((container) => {
			const partial = this.parseCartStateFromRoot(container);
			partial.forEach((value, key) => {
				merged.set(key, value);
			});
		});
		
		this.cartState.clear();
		merged.forEach((value, key) => {
			this.cartState.set(key, value);
		});
		
		this.hasLoaded = true;
	},
	
	/**
	 * Применяем фрагменты в DOM и синхронизируем состояние
	 */
	applyFragments(fragments) {
		if (!fragments || typeof jQuery === 'undefined') {
			return;
		}
		
		jQuery.each(fragments, function(key, value) {
			jQuery(key).replaceWith(value);
		});
		
		this.updateStateFromFragments(fragments);
		this.syncUI();
		
		if (typeof updateCartCountGlobal === 'function') {
			updateCartCountGlobal();
		}
		
		jQuery(document.body).trigger('wc_fragments_refreshed');
		jQuery(document.body).trigger('natura_fragments_refreshed');
		jQuery(document.body).trigger('updated_cart_totals');
	},
	
	/**
	 * Применяем ответ сервера (если есть fragments)
	 */
	applyResponse(response) {
		if (response && response.fragments) {
			this.applyFragments(response.fragments);
			return true;
		}
		return false;
	},
	
	/**
	 * Резолвим все ожидания по продукту (коалесинг)
	 */
	resolvePending(productId) {
		const resolvers = this.pendingResolvers.get(productId);
		if (resolvers && resolvers.length) {
			resolvers.forEach((resolve) => resolve());
		}
		this.pendingResolvers.delete(productId);
	},
	
	/**
	 * Получить количество товара в корзине
	 */
	getQuantity(productId) {
		const item = this.cartState.get(String(productId));
		return item ? item.quantity : 0;
	},
	
	/**
	 * Получить cart_item_key товара
	 */
	getCartItemKey(productId) {
		const item = this.cartState.get(String(productId));
		return item ? item.cartItemKey : null;
	},
	
	/**
	 * Проверить, есть ли товар в корзине
	 */
	hasProduct(productId) {
		return this.cartState.has(String(productId));
	},
	
	/**
	 * Обновить количество товара в корзине
	 */
	updateQuantity(productId, quantity, cartItemKey = null) {
		// Валидация
		let nextQuantity = naturaParseNumber(quantity);
		if (!Number.isFinite(nextQuantity)) nextQuantity = 1;
		if (nextQuantity <= 0) nextQuantity = 1;
		
		const pid = String(productId);
		
		// Если пришел cart_item_key (например, из мини-корзины) — сохраняем его,
		// чтобы не допустить ошибочного add_to_cart вместо update_cart.
		if (cartItemKey) {
			const current = this.cartState.get(pid) || {};
			this.cartState.set(pid, {
				...current,
				cartItemKey: cartItemKey
			});
			this.hasLoaded = true;
		}
		
		// Запоминаем желаемое количество (коалесинг)
		this.desiredQuantities.set(pid, nextQuantity);

		// Оптимистично обновляем состояние (иначе UI может откатываться на старое значение)
		const currentState = this.cartState.get(pid) || {};
		this.cartState.set(pid, {
			...currentState,
			quantity: nextQuantity,
			cartItemKey: cartItemKey || currentState.cartItemKey || null,
		});
		this.hasLoaded = true;
		
		// Оптимистичное обновление UI
		this.updateUI(pid, nextQuantity);
		
		// Добавляем в очередь (без дублей)
		return new Promise((resolve) => {
			const list = this.pendingResolvers.get(pid) || [];
			list.push(resolve);
			this.pendingResolvers.set(pid, list);
			
			if (!this.queuedProducts.has(pid)) {
				this.updateQueue.push(pid);
				this.queuedProducts.add(pid);
			}
			this.processQueue();
		});
	},
	
	/**
	 * Обработка очереди обновлений
	 */
	async processQueue() {
		if (this.isUpdating || this.updateQueue.length === 0) {
			return;
		}
		
		this.isUpdating = true;
		const productId = this.updateQueue.shift();
		this.queuedProducts.delete(productId);
		const quantity = this.desiredQuantities.get(productId);
		
		try {
			// Если quantity почему-то не определен — просто завершаем
			if (typeof quantity === 'undefined') {
				return;
			}
			
			let cartItemKey = this.getCartItemKey(productId);
			
			// Если товар есть в состоянии, но ключа нет — пробуем обновить состояние из DOM
			if (!cartItemKey && this.hasProduct(productId)) {
				this.updateStateFromDOM();
				cartItemKey = this.getCartItemKey(productId);
			}
			
			if (cartItemKey) {
				// Товар есть в корзине - обновляем
				await this.updateCartItem(cartItemKey, quantity);
			} else {
				// Товара нет - добавляем
				await this.addToCart(productId, quantity);
			}
		} catch (error) {
			// Откатываем оптимистичное обновление
			this.syncFromServer();
		} finally {
			this.isUpdating = false;
			this.resolvePending(productId);
			// Обрабатываем следующий элемент очереди
			if (this.updateQueue.length > 0) {
				setTimeout(() => this.processQueue(), 50);
			}
		}
	},
	
	/**
	 * Обновление товара в корзине
	 */
	updateCartItem(cartItemKey, quantity) {
		return new Promise((resolve, reject) => {
			const applyOrFallback = (response) => {
				const applied = this.applyResponse(response);
				if (!applied) {
					refreshCartFragments(() => resolve(response));
					return;
				}
				resolve(response);
			};

			// Основной путь: наш WP AJAX (надежнее, чем wc-ajax=update_cart)
			if (
				typeof jQuery !== 'undefined' &&
				typeof naturaCart !== 'undefined' &&
				naturaCart &&
				naturaCart.ajax_url &&
				naturaCart.nonce
			) {
				jQuery.ajax({
					type: 'POST',
					url: naturaCart.ajax_url,
					dataType: 'json',
					data: {
						action: 'natura_update_cart_item_quantity',
						nonce: naturaCart.nonce,
						cart_item_key: cartItemKey,
						quantity: quantity,
					},
					success: (response) => {
						// wp_send_json_error возвращает { success:false, data:{...} }
						if (response && response.success === false) {
							reject(new Error(response?.data?.message || 'Cart update failed'));
							return;
						}
						applyOrFallback(response);
					},
					error: () => {
						// Fallback: пробуем wc-ajax=update_cart (если доступен)
						const data = {
							['cart[' + cartItemKey + '][qty]']: quantity,
							update_cart: 'Update Cart',
						};
						
						wcAjax('update_cart', data, {
							success: (response) => applyOrFallback(response),
							error: reject
						});
					}
				});
				return;
			}

			// Fallback: wc-ajax=update_cart
			const data = {
				['cart[' + cartItemKey + '][qty]']: quantity,
				update_cart: 'Update Cart',
			};
			
			wcAjax('update_cart', data, {
				success: (response) => applyOrFallback(response),
				error: reject
			});
		});
	},
	
	/**
	 * Добавление товара в корзину
	 */
	addToCart(productId, quantity, sourceButton = null) {
		return new Promise((resolve, reject) => {
			wcAjax('add_to_cart', {
				product_id: productId,
				quantity: quantity,
			}, {
				success: (response) => {
					if (response.error && response.product_url) {
						reject(new Error('Product error'));
						return;
					}
					
					// Применяем фрагменты без лишних запросов
					this.applyResponse(response);
					
					// Триггерим стандартное событие WooCommerce (для уведомлений и совместимости)
					if (typeof jQuery !== 'undefined') {
						const $button = sourceButton
							? (sourceButton.jquery ? sourceButton : jQuery(sourceButton))
							: null;
						jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
					}
					
					resolve(response);
				},
				error: reject
			});
		});
	},
	
	/**
	 * Оптимистичное обновление UI (без запроса к серверу)
	 */
	updateUI(productId, quantity) {
		// Обновляем каталог
		const productCards = document.querySelectorAll('.product-card');
		productCards.forEach(card => {
			const buttonWrapper = card.querySelector('.product-card__button-wrapper');
			if (!buttonWrapper) return;
			
			const addToCartButton = buttonWrapper.querySelector('.add_to_cart_button, .ajax_add_to_cart');
			const quantityWrapper =
				buttonWrapper.querySelector(`.product-card__quantity-wrapper[data-product-id="${productId}"]`) ||
				buttonWrapper.querySelector('.product-card__quantity-wrapper[data-product-id]');
			
			const cardProductId = String(
				quantityWrapper?.getAttribute('data-product-id') ||
				addToCartButton?.getAttribute('data-product_id') ||
				addToCartButton?.getAttribute('data-product-id') ||
				''
			);
			
			if (!cardProductId || String(cardProductId) !== String(productId)) return;

			const addedToCartLink = buttonWrapper.querySelector('.added_to_cart.wc-forward, .added_to_cart');
			const cartItemKey = this.getCartItemKey(productId);
			
			if (quantity > 0) {
				// Показываем quantity-wrapper
				if (quantityWrapper) {
					const input = quantityWrapper.querySelector('input.qty, input[type="number"], .product-card__quantity-input');
					if (input) {
						const step = naturaParseNumber(input.getAttribute('step')) || 1;
						const formattedQuantity = naturaFormatQuantity(quantity, step);
						input.value = formattedQuantity;
						input.setAttribute('value', formattedQuantity);
					}
					if (addToCartButton) {
						addToCartButton.style.display = 'none';
					}
					// Скрываем "Переглянути кошик / View cart" (WooCommerce добавляет .added_to_cart.wc-forward)
					if (addedToCartLink) {
						addedToCartLink.style.display = 'none';
					}
					if (cartItemKey) {
						quantityWrapper.setAttribute('data-cart-item-key', cartItemKey);
					}
					quantityWrapper.style.display = 'flex';
					quantityWrapper.classList.add('show');
				}
			} else {
				// Скрываем quantity-wrapper
				if (quantityWrapper) {
					quantityWrapper.style.display = 'none';
					quantityWrapper.classList.remove('show');
					quantityWrapper.removeAttribute('data-cart-item-key');
					const input = quantityWrapper.querySelector('input.qty, input[type="number"], .product-card__quantity-input');
					if (input) {
						input.value = 1;
						input.setAttribute('value', 1);
					}
				}
				if (addToCartButton) {
					addToCartButton.style.display = '';
				}
				// Убираем/скрываем "View cart" ссылку, чтобы не оставалась после удаления
				if (addedToCartLink) {
					addedToCartLink.remove();
				}
			}
		});
		
		// Обновляем мини-корзину
		const miniCart = document.getElementById('mini-cart-sidebar');
		if (miniCart) {
			const cartItem = miniCart.querySelector(`.woocommerce-mini-cart-item[data-product-id="${productId}"], .mini-cart-item[data-product-id="${productId}"]`);
			if (cartItem) {
				const quantityValue = cartItem.querySelector('.mini-cart-item__quantity-value');
				if (quantityValue) {
					const unit = quantityValue.textContent.trim().split(' ').slice(1).join(' ') || 'шт';
					const step = naturaGetStepByUnit(unit);
					const formattedQuantity = naturaFormatQuantity(quantity, step);
					quantityValue.textContent = `${formattedQuantity} ${unit}`;
				}
			}
		}

		// Обновляем страницу товара (single product)
		const forms = document.querySelectorAll('form.cart');
		forms.forEach((form) => {
			const submitBtn = form.querySelector('button.single_add_to_cart_button, button[name="add-to-cart"]');
			const pid = submitBtn?.value || form.querySelector('input[name="add-to-cart"]')?.value;
			if (!pid || String(pid) !== String(productId)) {
				return;
			}

			const qtyWrapper = form.querySelector('.quantity-wrapper[data-product-id]');
			const viewCartLink = form.querySelector('.added_to_cart.wc-forward, .added_to_cart');

			if (viewCartLink) {
				viewCartLink.style.display = 'none';
			}

			if (!qtyWrapper) {
				return;
			}

			const input = qtyWrapper.querySelector('input.qty, input[type="number"]');
			if (input) {
				const step = naturaParseNumber(input.getAttribute('step')) || 1;
				const formattedQuantity = naturaFormatQuantity(quantity, step);
				input.value = formattedQuantity;
				input.setAttribute('value', formattedQuantity);
			}

			const cartItemKey = this.getCartItemKey(productId);
			if (cartItemKey) {
				qtyWrapper.setAttribute('data-cart-item-key', cartItemKey);
			}

			// На странице товара кнопку "Додати в кошик" не прячем — пусть остается доступной всегда.
			if (submitBtn) {
				submitBtn.style.display = '';
			}
		});
	},
	
	/**
	 * Синхронизация с сервером (с debounce)
	 */
	syncFromServer() {
		if (this.syncTimer) {
			clearTimeout(this.syncTimer);
		}
		
		this.syncTimer = setTimeout(() => {
			const loadPromise = this.loadCartState();
			if (loadPromise && typeof loadPromise.finally === 'function') {
				loadPromise.finally(() => {
					this.syncUI();
					if (typeof updateCartCountGlobal === 'function') {
						updateCartCountGlobal();
					}
				});
			} else {
				this.syncUI();
				if (typeof updateCartCountGlobal === 'function') {
					updateCartCountGlobal();
				}
			}
		}, 400);
	},
	
	/**
	 * Синхронизация UI с состоянием корзины
	 */
	syncUI() {
		// Обновляем каталог
		const productCards = document.querySelectorAll('.product-card');
		productCards.forEach(card => {
			const buttonWrapper = card.querySelector('.product-card__button-wrapper');
			if (!buttonWrapper) return;
			
			const quantityWrapper = buttonWrapper.querySelector('.product-card__quantity-wrapper[data-product-id]');
			const addToCartButton = buttonWrapper.querySelector('.add_to_cart_button, .ajax_add_to_cart');
			const productId = String(
				quantityWrapper?.getAttribute('data-product-id') ||
				addToCartButton?.getAttribute('data-product_id') || 
				addToCartButton?.getAttribute('data-product-id') ||
				''
			);
			
			if (!productId) return;

			const addedToCartLink = buttonWrapper.querySelector('.added_to_cart.wc-forward, .added_to_cart');
			const quantity = this.getQuantity(productId);
			const cartItemKey = this.getCartItemKey(productId);
			
			if (quantity > 0) {
				// Показываем quantity-wrapper
				if (quantityWrapper) {
					const input = quantityWrapper.querySelector('input.qty, input[type="number"], .product-card__quantity-input');
					if (input) {
						const currentValue = naturaParseNumber(input.value);
						if (!Number.isFinite(currentValue) || Math.abs(currentValue - quantity) > 1e-6) {
							const step = naturaParseNumber(input.getAttribute('step')) || 1;
							const formattedQuantity = naturaFormatQuantity(quantity, step);
							input.value = formattedQuantity;
							input.setAttribute('value', formattedQuantity);
						}
					}
					if (addToCartButton) {
						addToCartButton.style.display = 'none';
					}
					if (addedToCartLink) {
						addedToCartLink.style.display = 'none';
					}
					if (cartItemKey) {
						quantityWrapper.setAttribute('data-cart-item-key', cartItemKey);
					}
					quantityWrapper.style.display = 'flex';
					quantityWrapper.classList.add('show');
				}
			} else {
				// Скрываем quantity-wrapper
				if (quantityWrapper) {
					quantityWrapper.style.display = 'none';
					quantityWrapper.classList.remove('show');
					quantityWrapper.removeAttribute('data-cart-item-key');
					const input = quantityWrapper.querySelector('input.qty, input[type="number"], .product-card__quantity-input');
					if (input) {
						input.value = 1;
						input.setAttribute('value', 1);
					}
				}
				if (addToCartButton) {
					addToCartButton.style.display = '';
				}
				if (addedToCartLink) {
					addedToCartLink.remove();
				}
			}
		});

		// Обновляем страницу товара (single product)
		const forms = document.querySelectorAll('form.cart');
		forms.forEach((form) => {
			const submitBtn = form.querySelector('button.single_add_to_cart_button, button[name="add-to-cart"]');
			const productId = submitBtn?.value || form.querySelector('input[name="add-to-cart"]')?.value;
			if (!productId) {
				return;
			}

			const qtyWrapper = form.querySelector('.quantity-wrapper[data-product-id]');
			const viewCartLink = form.querySelector('.added_to_cart.wc-forward, .added_to_cart');
			const quantity = this.getQuantity(productId);
			const cartItemKey = this.getCartItemKey(productId);

			if (viewCartLink) {
				viewCartLink.style.display = 'none';
			}

			if (qtyWrapper) {
				const input = qtyWrapper.querySelector('input.qty, input[type="number"]');
				// Только если товар в корзине — синхронизируем значение, иначе не мешаем выбору перед добавлением
				if (quantity > 0 && input) {
					const currentValue = naturaParseNumber(input.value);
					if (!Number.isFinite(currentValue) || Math.abs(currentValue - quantity) > 1e-6) {
						const step = naturaParseNumber(input.getAttribute('step')) || 1;
						const formattedQuantity = naturaFormatQuantity(quantity, step);
						input.value = formattedQuantity;
						input.setAttribute('value', formattedQuantity);
					}
				}

				if (quantity > 0 && cartItemKey) {
					qtyWrapper.setAttribute('data-cart-item-key', cartItemKey);
				} else {
					qtyWrapper.removeAttribute('data-cart-item-key');
				}
			}

			// На странице товара кнопку "Додати в кошик" не прячем — пусть остается доступной всегда.
			if (submitBtn) {
				submitBtn.style.display = '';
			}
		});
	}
};

/**
 * Инициализация кнопок количества товара в карточках каталога
 */
const initQuantityButtons = () => {
	// Обработчик клика на кнопки +/-
	document.addEventListener('click', function(e) {
		const button = e.target.closest('.quantity-button--minus, .quantity-button--plus');
		if (!button) return;
		
		const wrapper = button.closest('.product-card__quantity-wrapper, .quantity-wrapper');
		if (!wrapper) return;
		
		e.preventDefault();
		e.stopPropagation();
		
		const input = wrapper.querySelector('input.qty, input[type="number"], .product-card__quantity-input');
		if (!input) return;
		
		const productId = wrapper.getAttribute('data-product-id') || input.getAttribute('data-product-id');
		if (!productId) return;
		
		const minParsed = naturaParseNumber(input.getAttribute('min'));
		const min = Number.isFinite(minParsed) ? minParsed : 1;
		const maxAttr = input.getAttribute('max');
		const maxParsed = maxAttr ? naturaParseNumber(maxAttr) : NaN;
		const max = Number.isFinite(maxParsed) ? maxParsed : null;
		const step = naturaParseNumber(input.getAttribute('step')) || 1;
		const currentParsed = naturaParseNumber(input.value);
		const currentVal = Number.isFinite(currentParsed) ? currentParsed : min;
		
		let newVal;
		if (button.classList.contains('quantity-button--minus')) {
			newVal = Math.max(min, currentVal - step);
		} else {
			newVal = max ? Math.min(max, currentVal + step) : currentVal + step;
		}
		newVal = naturaRoundToStep(newVal, step);
		
		// Обновляем значение в input
		const formattedQuantity = naturaFormatQuantity(newVal, step);
		input.value = formattedQuantity;
		input.setAttribute('value', formattedQuantity);
		
		// Обновляем корзину через менеджер:
		// - в каталоге (product-card__quantity-wrapper) всегда
		// - на странице товара (quantity-wrapper) только если товар уже в корзине
		if (typeof jQuery !== 'undefined' && typeof wc_add_to_cart_params !== 'undefined') {
			const isProductCard = wrapper.classList.contains('product-card__quantity-wrapper');
			const cartItemKey =
				wrapper.getAttribute('data-cart-item-key') ||
				CartManager.getCartItemKey(productId);
			const inCart = Boolean(cartItemKey) || CartManager.getQuantity(productId) > 0;
			
			if (isProductCard || inCart) {
				CartManager.updateQuantity(productId, newVal, cartItemKey);
			}
		}
	}, true);
	
	// Обработчик изменения input вручную (с debounce)
	const debouncedUpdate = debounce(function(input, productId) {
		const minParsed = naturaParseNumber(input.getAttribute('min'));
		const min = Number.isFinite(minParsed) ? minParsed : 1;
		const maxAttr = input.getAttribute('max');
		const maxParsed = maxAttr ? naturaParseNumber(maxAttr) : NaN;
		const max = Number.isFinite(maxParsed) ? maxParsed : null;
		const step = naturaParseNumber(input.getAttribute('step')) || 1;
		let value = naturaParseNumber(input.value);
		if (!Number.isFinite(value)) value = min;
		
		if (value < min) value = min;
		if (max && value > max) value = max;
		value = naturaRoundToStep(value, step);
		
		const formattedQuantity = naturaFormatQuantity(value, step);
		input.value = formattedQuantity;
		input.setAttribute('value', formattedQuantity);
		
		if (typeof jQuery !== 'undefined' && typeof wc_add_to_cart_params !== 'undefined') {
			const wrapper = input.closest('.product-card__quantity-wrapper, .quantity-wrapper');
			const isProductCard = wrapper?.classList?.contains('product-card__quantity-wrapper');
			const cartItemKey =
				wrapper?.getAttribute('data-cart-item-key') ||
				CartManager.getCartItemKey(productId);
			const inCart = Boolean(cartItemKey) || CartManager.getQuantity(productId) > 0;
			
			if (isProductCard || inCart) {
				CartManager.updateQuantity(productId, value, cartItemKey);
			}
		}
	}, 500);
	
	document.addEventListener('change', function(e) {
		const input = e.target;
		if (!input.matches('input.qty, input[type="number"], .product-card__quantity-input')) return;
		
		const wrapper = input.closest('.product-card__quantity-wrapper, .quantity-wrapper');
		if (!wrapper) return;
		
		const productId = wrapper.getAttribute('data-product-id');
		if (!productId) return;
		
		debouncedUpdate(input, productId);
	});
};

const initPromoCodeCopy = () => {
	const copyIcons = document.querySelectorAll('.sales-card__copy-icon');
	
	copyIcons.forEach((icon) => {
		icon.addEventListener('click', async (e) => {
			e.preventDefault();
			e.stopPropagation();
			
			// Ищем ближайший элемент с промокодом
			const promoCodeElement = icon.closest('.sales-card__promo')?.querySelector('.sales-card__promo-code');
			if (!promoCodeElement || !promoCodeElement.dataset.promoCode) {
				return;
			}
			
			const promoCode = promoCodeElement.dataset.promoCode;
			const originalText = promoCodeElement.textContent;
			
			try {
				await navigator.clipboard.writeText(promoCode);
				
				// Плавно скрываем текущий текст
				promoCodeElement.style.opacity = '0';
				promoCodeElement.style.transform = 'translateY(-5px)';
				
				// После завершения анимации скрытия меняем текст
				setTimeout(() => {
					promoCodeElement.textContent = 'Скопійовано';
					promoCodeElement.style.opacity = '1';
					promoCodeElement.style.transform = 'translateY(0)';
					
					// Возвращаем обратно через 2 секунды с плавной анимацией
					setTimeout(() => {
						promoCodeElement.style.opacity = '0';
						promoCodeElement.style.transform = 'translateY(-5px)';
						
						setTimeout(() => {
							promoCodeElement.textContent = originalText;
							promoCodeElement.style.opacity = '1';
							promoCodeElement.style.transform = 'translateY(0)';
						}, 300);
					}, 2000);
				}, 300);
			} catch (err) {
				console.error('Помилка копіювання:', err);
			}
		});
		
		// Добавляем курсор pointer при наведении
		icon.style.cursor = 'pointer';
	});
};


/**
 * Инициализация функционала добавления в корзину для карточек товаров
 */
const initProductCardAddToCart = () => {
	// Делегирование — чтобы работало и после AJAX-подмены списка товаров
	if (document._productCardAddToCartHandler) {
		document.removeEventListener('click', document._productCardAddToCartHandler, true);
	}

	document._productCardAddToCartHandler = async function(e) {
		// только левый клик без модификаторов
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
			return;
		}

		const button = e.target.closest(
			'.product-card__button-wrapper .add_to_cart_button, .product-card__button-wrapper .ajax_add_to_cart'
		);
		if (!button) return;

		// Не перехватываем add-to-cart в поиске (там своя логика)
		if (button.closest('.site-header__search-results, .site-header__search-result-item')) {
			return;
		}

		const productId = button.getAttribute('data-product_id') || button.getAttribute('data-product-id');
		if (!productId) {
			return; // пусть работает стандартный WooCommerce
		}

		const addToCartUrl = button.href || button.getAttribute('href');
		if (!addToCartUrl) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();
		e.stopImmediatePropagation();

		try {
			if (typeof jQuery !== 'undefined' && typeof wc_add_to_cart_params !== 'undefined') {
				// Количество из quantity-wrapper (если уже отображается)
				const buttonWrapper = button.closest('.product-card__button-wrapper');
				let quantity = 1;
				if (buttonWrapper) {
					const quantityWrapper = buttonWrapper.querySelector(
						'.product-card__quantity-wrapper[data-product-id="' + productId + '"]'
					);
					if (quantityWrapper) {
						const isVisible =
							quantityWrapper.classList.contains('show') ||
							quantityWrapper.style.display === 'flex' ||
							(window.getComputedStyle(quantityWrapper).display !== 'none');

						if (isVisible) {
							const quantityInput = quantityWrapper.querySelector(
								'input.qty, input[type="number"], .product-card__quantity-input'
							);
							if (quantityInput) {
								const step = naturaParseNumber(quantityInput.getAttribute('step')) || 1;
								const parsed = naturaParseNumber(quantityInput.value);
								quantity = Number.isFinite(parsed) ? naturaRoundToStep(parsed, step) : 1;
							}
						}
					}
				}

				// Событие Woo (совместимость)
				jQuery(document.body).trigger('adding_to_cart', [jQuery(button), {}]);

				// Оптимистично показываем quantity selector
				if (typeof CartManager !== 'undefined' && CartManager) {
					CartManager.updateUI(productId, quantity);
					await CartManager.addToCart(productId, quantity, button);
				} else {
					window.location.href = addToCartUrl;
				}
			} else {
				window.location.href = addToCartUrl;
			}
		} catch (error) {
			console.error('Помилка додавання товару в кошик:', error);
			window.location.href = addToCartUrl;
		}
	};

	document.addEventListener('click', document._productCardAddToCartHandler, true);
};


/**
 * Проверка корзины при загрузке страницы - показываем quantity-wrapper для товаров в корзине
 */
const checkCartForProducts = () => {
	if (typeof jQuery === 'undefined' || typeof wc_add_to_cart_params === 'undefined') {
		return;
	}
	
	// Получаем все карточки товаров
	const productCards = document.querySelectorAll('.product-card');
	if (productCards.length === 0) {
		return;
	}
	
	// Получаем список товаров в корзине через AJAX
	jQuery.ajax({
		type: 'POST',
		url: wc_add_to_cart_params.wc_ajax_url.toString().replace('%%endpoint%%', 'get_refreshed_fragments'),
		dataType: 'json',
		success: function(response) {
			if (!response || !response.fragments) {
				return;
			}
			
			// Парсим HTML фрагментов для получения списка товаров в корзине
			const cartContent = response.fragments['div.widget_shopping_cart_content'] || 
			                    response.fragments['.widget_shopping_cart_content'] ||
			                    Object.values(response.fragments).find(fragment => 
			                    	typeof fragment === 'string' && fragment.includes('cart_item')
			                    );
			
			if (!cartContent) {
				return;
			}
			
			// Создаем временный элемент для парсинга
			const tempDiv = document.createElement('div');
			tempDiv.innerHTML = typeof cartContent === 'string' ? cartContent : cartContent.outerHTML || '';
			
			// Находим все товары в корзине
			const cartItems = tempDiv.querySelectorAll('.mini-cart-item, .cart_item, [data-product-id]');
			const cartProductIds = new Set();
			
			cartItems.forEach(item => {
				const productId = item.getAttribute('data-product-id') || 
				                 item.querySelector('[data-product-id]')?.getAttribute('data-product-id');
				if (productId) {
					cartProductIds.add(productId);
				}
			});
			
			// Показываем quantity-wrapper для товаров в корзине
			productCards.forEach(card => {
				const buttonWrapper = card.querySelector('.product-card__button-wrapper');
				if (!buttonWrapper) {
					return;
				}
				
				const addToCartButton = buttonWrapper.querySelector('.add_to_cart_button, .ajax_add_to_cart');
				const productId = addToCartButton?.getAttribute('data-product_id') || 
				                 addToCartButton?.getAttribute('data-product-id');
				
				if (productId && cartProductIds.has(productId)) {
					const quantityWrapper = buttonWrapper.querySelector('.product-card__quantity-wrapper[data-product-id="' + productId + '"]');
					if (quantityWrapper) {
						// Скрываем кнопку
						if (addToCartButton) {
							addToCartButton.style.display = 'none';
						}
						// Показываем quantity-wrapper
						quantityWrapper.style.display = 'flex';
						quantityWrapper.classList.add('show');
						
					}
				}
			});
		},
		error: function() {
			console.log('[checkCartForProducts] Ошибка при проверке корзины');
		}
	});
};



/**
 * Инициализация мини-корзины и перехват формы добавления в корзину
 */
const initMiniCart = () => {
	const miniCart = document.getElementById('mini-cart-sidebar');
	if (!miniCart) {
		console.log('[initMiniCart] Мини-корзина не найдена');
		return;
	}

	console.log('[initMiniCart] Мини-корзина найдена, инициализация...');

	const getMiniCartScrollContainer = () => {
		// Main scroll container for mini-cart items
		return (
			miniCart.querySelector('.woocommerce-mini-cart') ||
			miniCart.querySelector('.mini-cart-sidebar__body')
		);
	};

	const focusMiniCartScrollContainer = () => {
		const scrollEl = getMiniCartScrollContainer();
		if (!scrollEl) return;

		// Make focusable for keyboard users (wheel uses pointer, but this still improves UX)
		if (!scrollEl.hasAttribute('tabindex')) {
			scrollEl.setAttribute('tabindex', '-1');
		}

		try {
			scrollEl.focus({ preventScroll: true });
		} catch (e) {
			try {
				scrollEl.focus();
			} catch (_) {}
		}
	};

	// When mini-cart is open, forward mouse wheel scrolling to the cart list
	// so user doesn't need to "aim" the cursor at the scrollable list.
	if (!document._naturaMiniCartWheelRedirect) {
		document._naturaMiniCartWheelRedirect = (e) => {
			const miniCartEl = document.getElementById('mini-cart-sidebar');
			if (!miniCartEl || !miniCartEl.classList.contains('is-open')) {
				return;
			}

			// Allow pinch-to-zoom (trackpad) even when mini-cart is open
			if (e.ctrlKey) {
				return;
			}

			const scrollEl =
				miniCartEl.querySelector('.woocommerce-mini-cart') ||
				miniCartEl.querySelector('.mini-cart-sidebar__body');

			// Always prevent default page scrolling while cart is open
			e.preventDefault();

			if (!scrollEl) {
				return;
			}

			let deltaY = e.deltaY || 0;
			if (e.deltaMode === 1) {
				// lines -> pixels (approx)
				deltaY *= 16;
			} else if (e.deltaMode === 2) {
				// pages -> pixels
				deltaY *= scrollEl.clientHeight || 0;
			}

			if (!deltaY) {
				return;
			}

			scrollEl.scrollTop += deltaY;
		};

		document.addEventListener('wheel', document._naturaMiniCartWheelRedirect, {
			passive: false,
			capture: true,
		});
	}

	const openCart = () => {
		console.log('[initMiniCart] Открытие корзины');
		miniCart.classList.add('is-open');
		document.body.classList.add('mini-cart-open');
		document.documentElement.classList.add('mini-cart-open');
		naturaLockPageScrollForMiniCart();
		requestAnimationFrame(focusMiniCartScrollContainer);
	};

	const updateCartCount = () => {
		updateCartCountGlobal();
	};

	// Обработчик для открытия мини-корзины из хедера
	const cartButtons = document.querySelectorAll('[data-mini-cart-open]');
	cartButtons.forEach((button) => {
		button.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			openCart();
		});
	});

	// Обновление счетчика корзины
	updateCartCount();

	const closeCart = () => {
		console.log('[initMiniCart] Закрытие корзины');
		miniCart.classList.remove('is-open');
		document.body.classList.remove('mini-cart-open');
		document.documentElement.classList.remove('mini-cart-open');
		naturaUnlockPageScrollForMiniCart();
	};

	// Обработчики закрытия
	const closeButtons = miniCart.querySelectorAll('[data-mini-cart-close]');
	closeButtons.forEach(button => {
		button.addEventListener('click', closeCart);
	});

	// Закрытие по клику на overlay
	const overlay = miniCart.querySelector('.mini-cart-sidebar__overlay');
	if (overlay) {
		overlay.addEventListener('click', closeCart);
	}

	// Закрытие по Escape
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && miniCart.classList.contains('is-open')) {
			closeCart();
		}
	});

	// Перехватываем отправку формы добавления в корзину и используем AJAX
	if (typeof jQuery !== 'undefined') {
		// Перехватываем форму на странице товара
		jQuery(document).on('submit', 'form.cart', function(e) {
			e.preventDefault();
			
			const $form = jQuery(this);
			const $button = $form.find('button[type="submit"]');
			const productId = $button.val() || $form.find('input[name="add-to-cart"]').val();
			const quantity = $form.find('input[name="quantity"]').val() || 1;
			
			console.log('[initMiniCart] Перехвачена форма, productId:', productId, 'quantity:', quantity);
			
			if (!productId) {
				console.log('[initMiniCart] ProductId не найден, отправка формы обычным способом');
				return true; // Разрешаем обычную отправку
			}
			
			// Блокируем кнопку
			$button.prop('disabled', true).text('Додавання...');
			
			// Отправляем через AJAX
			if (typeof wc_add_to_cart_params !== 'undefined') {
				wcAjax('add_to_cart', {
					product_id: productId,
					quantity: quantity,
				}, {
					success: function(response) {
						console.log('[initMiniCart] AJAX успех:', response);
						
						$button.prop('disabled', false).text('Додати в кошик');
						
						if (response.error && response.product_url) {
							window.location = response.product_url;
							return;
						}
						
						// Обновляем фрагменты
						if (response.fragments) {
							jQuery.each(response.fragments, function(key, value) {
								jQuery(key).replaceWith(value);
							});
						}
						
						// Обновляем мини-корзину
						if (response.fragments) {
							const cartContent = miniCart.querySelector('.widget_shopping_cart_content');
							if (cartContent) {
								// Ищем фрагмент мини-корзины
								jQuery.each(response.fragments, function(key, value) {
									if (key.includes('widget_shopping_cart_content') || key.includes('mini-cart')) {
										jQuery(cartContent).html(value);
									}
								});
							}
						}
						
						// Не открываем корзину автоматически - показываем только уведомление
						
						// WooCommerce ожидает jQuery-объект кнопки, иначе add-to-cart.min.js падает
						jQuery(document.body).trigger('added_to_cart', [response.fragments, response.cart_hash, $button]);
					},
					error: function() {
						console.log('[initMiniCart] AJAX ошибка');
						$button.prop('disabled', false).text('Додати в кошик');
					}
				});
			} else {
				// Fallback: обычная отправка формы
				$form.off('submit').submit();
			}
			
			return false;
		});

		// Перехватываем клики на кнопки удаления товара из мини-корзины с использованием нативного addEventListener для раннего перехвата
		document.addEventListener('click', function(e) {
			const target = e.target;
			const $target = jQuery(target);
			
			// Проверяем, что это кнопка удаления в мини-корзине (может быть ссылка или изображение внутри)
			const removeButton = target.closest('.mini-cart-item__remove, .remove_from_cart_button, .remove');
			if (!removeButton || 
			    !$target.closest('.mini-cart-sidebar, .widget_shopping_cart').length) {
				return; // Не наша кнопка, пропускаем
			}
			
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			
			const $button = jQuery(removeButton);
			const cartItemKey = $button.data('cart_item_key') || $button.attr('data-cart_item_key') || removeButton.getAttribute('data-cart_item_key');
			const $cartItem = $button.closest('.woocommerce-mini-cart-item, .mini_cart_item');
			
			if (!cartItemKey) {
				console.log('[initMiniCart] cart_item_key не найден');
				return;
			}
			
			console.log('[initMiniCart] Удаление товара через AJAX, cart_item_key:', cartItemKey);
			
			// Блокируем элемент
			$cartItem.css('opacity', '0.5').css('pointer-events', 'none');
			
			// Отправляем AJAX запрос на удаление
			if (typeof wc_add_to_cart_params !== 'undefined') {
				const removeData = {
					cart_item_key: cartItemKey
				};
				
				// Добавляем nonce если доступен
				if (wc_add_to_cart_params.wc_cart_nonce) {
					removeData._wpnonce = wc_add_to_cart_params.wc_cart_nonce;
				}
				
				wcAjax('remove_from_cart', removeData, {
					success: function(response) {
						console.log('[initMiniCart] Товар удален, ответ:', response);
						
						if (!response || !response.fragments) {
							console.log('[initMiniCart] Нет фрагментов в ответе, обновляем через get_refreshed_fragments');
							// Если нет фрагментов, получаем их отдельно
							refreshCartFragments(function(fragmentResponse) {
								if (fragmentResponse && fragmentResponse.fragments) {
									const cartContent = miniCart.querySelector('.widget_shopping_cart_content');
									if (cartContent) {
										jQuery.each(fragmentResponse.fragments, function(key, value) {
											if (key.includes('widget_shopping_cart_content') || key.includes('mini-cart')) {
												jQuery(cartContent).html(value);
												setTimeout(() => {
													initMiniCartQuantityButtons();
												}, 100);
											} else {
												jQuery(key).replaceWith(value);
											}
										});
									}
									jQuery(document.body).trigger('removed_from_cart', [fragmentResponse.fragments, fragmentResponse.cart_hash, $button]);
								}
							});
							return;
						}
						
						// Обновляем содержимое корзины
						const cartContent = miniCart.querySelector('.widget_shopping_cart_content');
						if (cartContent) {
							jQuery.each(response.fragments, function(key, value) {
								if (key.includes('widget_shopping_cart_content') || key.includes('mini-cart')) {
									jQuery(cartContent).html(value);
									// Переинициализируем кнопки количества после обновления
									setTimeout(() => {
										initMiniCartQuantityButtons();
									}, 100);
								} else {
									jQuery(key).replaceWith(value);
								}
							});
						}
						
						// Триггерим событие WooCommerce
						jQuery(document.body).trigger('removed_from_cart', [response.fragments, response.cart_hash, $button]);
					},
					error: function() {
						console.log('[initMiniCart] Ошибка при удалении товара');
						// Восстанавливаем элемент
						$cartItem.css('opacity', '1').css('pointer-events', 'auto');
					}
				});
			} else {
				console.log('[initMiniCart] wc_add_to_cart_params не определен');
			}
		}, true); // Используем capture phase для раннего перехвата

		// Предотвращаем закрытие корзины при удалении товара
		jQuery(document.body).on('removed_from_cart', function(event, fragments, cart_hash, $button) {
			// Не закрываем корзину - просто обновляем содержимое
			if (fragments) {
				const cartContent = miniCart.querySelector('.widget_shopping_cart_content');
				if (cartContent) {
					jQuery.each(fragments, function(key, value) {
						if (key.includes('widget_shopping_cart_content') || key.includes('mini-cart')) {
							jQuery(cartContent).html(value);
							// Переинициализируем кнопки количества после обновления
							setTimeout(() => {
								initMiniCartQuantityButtons();
								CartManager.updateStateFromDOM();
								CartManager.syncUI();
								if (typeof updateCartCountGlobal === 'function') {
									updateCartCountGlobal();
								}
							}, 100);
						} else {
							jQuery(key).replaceWith(value);
						}
					});
				}
			} else {
				// Если нет фрагментов, обновляем состояние из DOM (без запроса)
				CartManager.updateStateFromDOM();
				CartManager.syncUI();
				if (typeof updateCartCountGlobal === 'function') {
					updateCartCountGlobal();
				}
			}
		});

		// Открытие корзины при добавлении товара через другие методы
	// Debounce для обработки добавления в корзину
	let addedToCartTimeout = null;
	
	// Обработчик для синхронизации после добавления в корзину
	// Используем namespace для избежания конфликтов
	jQuery(document.body).off('added_to_cart.miniCart').on('added_to_cart.miniCart', function(event, fragments, cart_hash, $button) {
		// Очищаем предыдущий таймер
		if (addedToCartTimeout) {
			clearTimeout(addedToCartTimeout);
		}
		
		// Устанавливаем новый таймер для debounce (300ms)
		addedToCartTimeout = setTimeout(() => {
			// Обновляем состояние без доп. запроса
			if (fragments) {
				CartManager.updateStateFromFragments(fragments);
			} else {
				CartManager.updateStateFromDOM();
			}
			CartManager.syncUI();
			if (typeof updateCartCountGlobal === 'function') {
				updateCartCountGlobal();
			}
		}, 300);
	});

	} else {
		console.log('[initMiniCart] jQuery не загружен');
	}

	// Обработчики кнопок количества в мини-корзине
	const handleQuantityChange = (button, delta) => {
		const cartItemKey = button.getAttribute('data-cart-item-key') || 
		                    button.getAttribute('data-cart_item_key') ||
		                    button.closest('[data-cart-item-key]')?.getAttribute('data-cart-item-key') ||
		                    button.closest('[data-cart_item_key]')?.getAttribute('data-cart_item_key');
		if (!cartItemKey) {
			return;
		}

		const quantityWrapper = button.closest('.mini-cart-item__quantity-wrapper');
		const quantityValue = quantityWrapper?.querySelector('.mini-cart-item__quantity-value');
		const productId = button.getAttribute('data-product-id') ||
		                  button.closest('[data-product-id]')?.getAttribute('data-product-id') ||
		                  button.closest('[data-product_id]')?.getAttribute('data-product_id');
		
		if (!productId || !quantityValue) {
			return;
		}

		const unit = quantityValue.textContent.trim().split(' ').slice(1).join(' ') || 'шт';
		const step = naturaGetStepByUnit(unit);
		const min = step; // для кг: 0.1, для шт: 1
		const currentQuantity = naturaParseNumber(quantityValue.textContent) || min;
		let newQuantity = Math.max(min, currentQuantity + delta * step);
		newQuantity = naturaRoundToStep(newQuantity, step);

		// Обновляем через CartManager (коалесинг + без лишних запросов)
		CartManager.updateQuantity(productId, newQuantity, cartItemKey).catch(() => {
			// Откатываем при ошибке
			quantityValue.textContent = `${naturaFormatQuantity(currentQuantity, step)} ${unit}`;
			CartManager.updateStateFromDOM();
			CartManager.syncUI();
			if (typeof updateCartCountGlobal === 'function') {
				updateCartCountGlobal();
			}
		});
	};

	const initMiniCartQuantityButtons = () => {
		// Используем делегирование событий на document для надежности
		// Удаляем старый обработчик если он есть
		if (document._miniCartQuantityHandler) {
			document.removeEventListener('click', document._miniCartQuantityHandler, true);
		}
		
		// Создаем новый обработчик с делегированием
		document._miniCartQuantityHandler = function(e) {
			// Проверяем, что клик произошел внутри миникорзины
			if (!miniCart.contains(e.target)) {
				return;
			}
			
			const button = e.target.closest('.mini-cart-item__quantity-button--minus, .mini-cart-item__quantity-button--plus');
			if (!button) return;
			
			e.preventDefault();
			e.stopPropagation();
			e.stopImmediatePropagation();
			
			const isMinus = button.classList.contains('mini-cart-item__quantity-button--minus');
			const delta = isMinus ? -1 : 1;
			
			handleQuantityChange(button, delta);
		};
		
		// Добавляем обработчик на document с capture phase для раннего перехвата
		document.addEventListener('click', document._miniCartQuantityHandler, true);
	};

	// Инициализируем кнопки при загрузке
	initMiniCartQuantityButtons();

	// Debounce для переинициализации обработчиков
	let reinitTimeout = null;
	
		// Переинициализируем обработчики после обновления корзины через события WooCommerce
		// Используем namespace для избежания конфликтов
		jQuery(document.body).off('updated_wc_div.miniCart updated_cart_totals.miniCart wc_fragments_refreshed.miniCart natura_fragments_refreshed.miniCart')
			.on('updated_wc_div.miniCart updated_cart_totals.miniCart wc_fragments_refreshed.miniCart natura_fragments_refreshed.miniCart', function() {
				// Очищаем предыдущий таймер
				if (reinitTimeout) {
					clearTimeout(reinitTimeout);
				}
				
				// Устанавливаем новый таймер для debounce (150ms)
				reinitTimeout = setTimeout(() => {
					initMiniCartQuantityButtons();
					CartManager.updateStateFromDOM();
					CartManager.syncUI();
					if (typeof updateCartCountGlobal === 'function') {
						updateCartCountGlobal();
					}
				}, 150);
			});
};

/**
 * Инициализация панели фильтров для мобильных устройств
 */
const initShopFilterDrawer = () => {
	const filterDrawer = document.querySelector('[data-shop-filter-drawer]');
	if (!filterDrawer) {
		return;
	}

	const getShopUrl = () => {
		const el = document.querySelector('[data-shop-url]');
		const url = el ? el.getAttribute('data-shop-url') : '';
		return url || '';
	};

	const openFilter = () => {
		filterDrawer.classList.add('is-open');
		document.body.style.overflow = 'hidden';
	};

	const closeFilter = () => {
		filterDrawer.classList.remove('is-open');
		document.body.style.overflow = '';
	};

	// Открытие панели
	const openButtons = document.querySelectorAll('[data-shop-filter-open]');
	openButtons.forEach(button => {
		button.addEventListener('click', (e) => {
			e.preventDefault();
			e.stopPropagation();
			openFilter();
		});
	});

	// Закрытие панели
	const closeButtons = filterDrawer.querySelectorAll('[data-shop-filter-close]');
	closeButtons.forEach(button => {
		button.addEventListener('click', closeFilter);
	});

	// Закрытие по Escape
	document.addEventListener('keydown', (e) => {
		if (e.key === 'Escape' && filterDrawer.classList.contains('is-open')) {
			closeFilter();
		}
	});

	// Сброс фильтров
	const resetButton = filterDrawer.querySelector('[data-filter-reset]');
	if (resetButton) {
		resetButton.addEventListener('click', () => {
			// Полный сброс: возвращаемся на страницу магазина + убираем price params
			const shopUrl = getShopUrl();
			try {
				const base = shopUrl ? new URL(shopUrl, window.location.href) : new URL(window.location.href);
				base.searchParams.delete('min_price');
				base.searchParams.delete('max_price');
				base.searchParams.delete('paged');
				base.searchParams.delete('product-page');
				base.searchParams.delete('page');
				window.location.href = base.toString();
			} catch {
				window.location.href = shopUrl || window.location.href;
			}
		});
	}

	// Применить все фильтры
	const applyButton = filterDrawer.querySelector('[data-filter-apply]');
	if (applyButton) {
		applyButton.addEventListener('click', () => {
			// Просто закрываем панель, фильтры применяются через клики по категориям
			closeFilter();
		});
	}
};

/**
 * AJAX навигация по каталогу: категории и пагинация без перезагрузки страницы.
 * Обновляет список товаров, активные категории, хлебные крошки, title и URL (history).
 */
const initShopArchiveAjaxNavigation = () => {
	const productsContainer = document.querySelector('.shop-archive-products');
	if (!productsContainer || typeof window.fetch === 'undefined') {
		return;
	}

	let controller = null;

	// Ensure parent categories stay expanded when a subcategory is active (robust for AJAX swaps).
	const ensureActiveCategoriesExpanded = () => {
		// Desktop sidebar
		const sidebarList = document.querySelector('.shop-archive-filters');
		if (sidebarList) {
			let activeLi =
				sidebarList.querySelector('.shop-archive-filters__subitem--active') ||
				sidebarList.querySelector('.shop-archive-filters__item--active');

			// Fallback: determine active item by current URL (in case server didn't mark active classes)
			if (!activeLi) {
				const currentPath = String(window.location.pathname || '').replace(/\/$/, '');
				const links = sidebarList.querySelectorAll('a[href]');
				for (const a of links) {
					try {
						const u = new URL(a.href, window.location.href);
						if (u.pathname.replace(/\/$/, '') === currentPath) {
							activeLi = a.closest('li');
							break;
						}
					} catch {
						// ignore
					}
				}
			}

			let li = activeLi ? activeLi.closest('li') : null;
			while (li) {
				if (li.classList.contains('shop-archive-filters__item')) {
					li.classList.add('shop-archive-filters__item--expanded');
				} else if (li.classList.contains('shop-archive-filters__subitem')) {
					li.classList.add('shop-archive-filters__subitem--expanded');
				}
				li = li.parentElement ? li.parentElement.closest('li') : null;
			}
		}

		// Mobile drawer
		const mobileList = document.querySelector('.shop-filter-list');
		if (mobileList) {
			let activeLi = mobileList.querySelector('.shop-filter-list__item--active');

			// Fallback: determine active by current URL
			if (!activeLi) {
				const currentPath = String(window.location.pathname || '').replace(/\/$/, '');
				const links = mobileList.querySelectorAll('a[href]');
				for (const a of links) {
					try {
						const u = new URL(a.href, window.location.href);
						if (u.pathname.replace(/\/$/, '') === currentPath) {
							activeLi = a.closest('li');
							break;
						}
					} catch {
						// ignore
					}
				}
			}

			let li = activeLi ? activeLi.closest('li') : null;
			while (li) {
				if (li.classList.contains('shop-filter-list__item')) {
					li.classList.add('shop-filter-list__item--expanded');
				}
				li = li.parentElement ? li.parentElement.closest('li') : null;
			}
		}
	};

	const getShopUrl = () => {
		const el = document.querySelector('[data-shop-url]');
		const url = el ? el.getAttribute('data-shop-url') : '';
		return url || '';
	};

	const buildShopUrlWithCurrentParams = () => {
		const shopUrl = getShopUrl();
		if (!shopUrl) return '';
		try {
			const base = new URL(shopUrl, window.location.href);
			const current = new URL(window.location.href);
			current.searchParams.forEach((value, key) => {
				// не тащим пагинацию
				if (key === 'paged' || key === 'product-page' || key === 'page') return;
				base.searchParams.set(key, value);
			});
			return base.toString();
		} catch {
			return shopUrl;
		}
	};

	const setLoading = (isLoading) => {
		productsContainer.classList.toggle('is-loading', isLoading);
		productsContainer.setAttribute('aria-busy', isLoading ? 'true' : 'false');
	};

	const closeFilterDrawerIfOpen = () => {
		const filterDrawer = document.querySelector('[data-shop-filter-drawer]');
		if (filterDrawer && filterDrawer.classList.contains('is-open')) {
			filterDrawer.classList.remove('is-open');
			document.body.style.overflow = '';
		}
	};

	const isSameOrigin = (url) => {
		try {
			const u = new URL(url, window.location.href);
			return u.origin === window.location.origin;
		} catch {
			return false;
		}
	};

	const looksLikeShopUrl = (url) => {
		try {
			const u = new URL(url, window.location.href);
			// Категории: /product-category/...
			if (u.pathname.includes('/product-category/')) return true;
			// Архив товаров: ?post_type=product
			if (u.searchParams.get('post_type') === 'product') return true;
			// Страница магазина (shop) — в любом виде, если совпадает с текущим шаблоном каталога
			// (на практике это просто link внутри фильтров)
			return true;
		} catch {
			return false;
		}
	};

	const swapFromHtml = (html, urlToSet, pushHistory) => {
		const parser = new DOMParser();
		const doc = parser.parseFromString(html, 'text/html');

		const nextProducts = doc.querySelector('.shop-archive-products');
		if (!nextProducts) {
			return false;
		}

		// Обновляем товары
		productsContainer.innerHTML = nextProducts.innerHTML;

		// Обновляем списки категорий (сайдбар и моб. drawer), чтобы активные классы совпали с сервером
		const nextSidebarFilters = doc.querySelector('.shop-archive-filters');
		const currentSidebarFilters = document.querySelector('.shop-archive-filters');
		if (nextSidebarFilters && currentSidebarFilters) {
			currentSidebarFilters.innerHTML = nextSidebarFilters.innerHTML;
		}

		const nextMobileFilters = doc.querySelector('.shop-filter-list');
		const currentMobileFilters = document.querySelector('.shop-filter-list');
		if (nextMobileFilters && currentMobileFilters) {
			currentMobileFilters.innerHTML = nextMobileFilters.innerHTML;
		}

		// Keep the active category tree expanded after DOM swap
		ensureActiveCategoriesExpanded();

		// Обновляем breadcrumb
		const nextBreadcrumb = doc.querySelector('.shop-archive-hero__breadcrumb');
		const currentBreadcrumb = document.querySelector('.shop-archive-hero__breadcrumb');
		if (nextBreadcrumb && currentBreadcrumb) {
			currentBreadcrumb.innerHTML = nextBreadcrumb.innerHTML;
		}

		// Обновляем title
		if (doc.title) {
			document.title = doc.title;
		}

		// Меняем URL (pushState)
		if (pushHistory && urlToSet) {
			window.history.pushState({ naturaShopAjax: true }, '', urlToSet);
		}

		// Синхронизируем UI корзины на новых карточках
		if (typeof CartManager !== 'undefined' && CartManager) {
			CartManager.updateStateFromDOM();
			CartManager.syncUI();
		}

		// Фиксим возможные скачки высоты карточек
		if (typeof preventProductCardLayoutShift === 'function') {
			preventProductCardLayoutShift();
		}

		// Скроллим к товарам (мягко)
		const scrollTarget = document.querySelector('.shop-archive-content') || productsContainer;
		if (scrollTarget && typeof scrollTarget.scrollIntoView === 'function') {
			scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'start' });
		}

		return true;
	};

	// Initial state: make sure active branch is expanded on first load
	ensureActiveCategoriesExpanded();

	const loadUrl = async (url, { pushHistory = true } = {}) => {
		if (!url || !isSameOrigin(url) || !looksLikeShopUrl(url)) {
			window.location.href = url;
			return;
		}

		if (controller) {
			controller.abort();
		}
		controller = new AbortController();

		setLoading(true);
		try {
			const res = await fetch(url, {
				method: 'GET',
				signal: controller.signal,
				headers: {
					'X-Requested-With': 'XMLHttpRequest',
				},
			});

			if (!res.ok) {
				window.location.href = url;
				return;
			}

			const html = await res.text();
			const swapped = swapFromHtml(html, url, pushHistory);
			if (!swapped) {
				window.location.href = url;
			}
		} catch (err) {
			if (err && err.name === 'AbortError') {
				return;
			}
			window.location.href = url;
		} finally {
			setLoading(false);
		}
	};

	// Делегирование кликов по категориям и пагинации
	document._shopArchiveAjaxNavHandler = function(e) {
		// только левый клик без модификаторов
		if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
			return;
		}

		const link = e.target.closest(
			'.shop-archive-filters a, .shop-filter-list__link, .woocommerce-pagination a.page-numbers'
		);
		if (!link || !link.href) return;

		// Не перехватываем ссылки из поиска в хедере
		if (link.closest('.site-header__search-results')) return;

		if (!isSameOrigin(link.href) || !looksLikeShopUrl(link.href)) {
			return;
		}

		// Повторный клик по активной категории = снять фильтр (вернуться на shop)
		const isCategoryLink = !!(link.closest('.shop-archive-filters') || link.closest('.shop-filter-list'));
		// ВАЖНО: не считаем "активной" категорией клик по подкатегории внутри активного родителя.
		// Для этого смотрим на ближайший <li> к ссылке, а не на любого предка.
		const activeLi = link.closest('li');
		const isActiveCategory = !!(
			activeLi &&
			(
				activeLi.classList.contains('shop-archive-filters__item--active') ||
				activeLi.classList.contains('shop-archive-filters__subitem--active') ||
				activeLi.classList.contains('shop-filter-list__item--active')
			)
		);

		let targetUrl = link.href;
		if (isCategoryLink && isActiveCategory) {
			const shopUrl = buildShopUrlWithCurrentParams();
			if (shopUrl) {
				targetUrl = shopUrl;
			}
		}

		e.preventDefault();
		e.stopPropagation();

		closeFilterDrawerIfOpen();
		loadUrl(targetUrl, { pushHistory: true });
	};

	// Снимаем старый обработчик, если был
	if (document._shopArchiveAjaxNavHandlerBound) {
		document.removeEventListener('click', document._shopArchiveAjaxNavHandlerBound, true);
	}
	document._shopArchiveAjaxNavHandlerBound = document._shopArchiveAjaxNavHandler;
	document.addEventListener('click', document._shopArchiveAjaxNavHandlerBound, true);

	// Back/forward
	window.addEventListener('popstate', function() {
		// если мы всё ещё на странице с каталогом — подгружаем текущее location.href
		if (document.querySelector('.shop-archive-products')) {
			loadUrl(window.location.href, { pushHistory: false });
		}
	});
};

/**
 * Инициализация карусели связанных товаров
 */
const initRelatedProductsCarousel = () => {
	const section = document.querySelector('.single-product__related-section');
	if (!section || typeof Swiper === 'undefined') {
		return;
	}

	// Отключаем swiper на мобильной версии (ширина экрана <= 768px)
	if (window.innerWidth <= 768) {
		return;
	}

	const swiperEl = section.querySelector('[data-swiper]');
	if (!swiperEl) {
		return;
	}

	const container = swiperEl.closest('.container');
	const containerWidth = container ? container.offsetWidth : window.innerWidth;
	const slideWidth = (containerWidth - (3 * parseFloat(getComputedStyle(document.documentElement).fontSize) * 1.042)) / 4;
	
	const swiper = new Swiper(swiperEl, {
		slidesPerView: 'auto',
		spaceBetween: '1.042vw',
		loop: false,
		speed: 450,
		watchOverflow: true,
		allowTouchMove: true,
		resistance: true,
		resistanceRatio: 0,
		preventClicks: true,
		preventClicksPropagation: true,
		breakpoints: {
			320: {
				slidesPerView: 1.2,
				spaceBetween: (window.innerWidth * 2.667) / 100,
				loop: false,
			},
			768: {
				slidesPerView: 2.5,
				spaceBetween: (window.innerWidth * 2.667) / 100,
				loop: false,
			},
			1024: {
				slidesPerView: 4,
				spaceBetween: '1.042vw',
				loop: false,
			},
		},
	});
};

