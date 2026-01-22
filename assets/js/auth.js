/**
 * Natura Auth JS
 * Обробка форм входу та реєстрації
 */
(function () {
	'use strict';

	/**
	 * Debounce функция для предотвращения множественных запросов
	 */
	const debounce = (func, wait = 300) => {
		let timeout;
		return function executedFunction(...args) {
			const later = () => {
				timeout = null;
				func.apply(this, args);
			};
			clearTimeout(timeout);
			timeout = setTimeout(later, wait);
		};
	};

	// Перемикання табів
	const tabs = document.querySelectorAll('[data-tab]');
	const forms = document.querySelectorAll('[data-form]');
	const switchLinks = document.querySelectorAll('[data-switch-tab]');

	function switchTab(tabName) {
		tabs.forEach(tab => {
			tab.classList.toggle('auth-page__tab--active', tab.dataset.tab === tabName);
		});
		forms.forEach(form => {
			form.classList.toggle('auth-page__form--active', form.dataset.form === tabName);
		});

		// Оновлюємо URL без перезавантаження
		const url = new URL(window.location);
		if (tabName === 'register') {
			url.searchParams.set('tab', 'register');
		} else {
			url.searchParams.delete('tab');
		}
		window.history.replaceState({}, '', url);
	}

	tabs.forEach(tab => {
		tab.addEventListener('click', () => switchTab(tab.dataset.tab));
	});

	switchLinks.forEach(link => {
		link.addEventListener('click', (e) => {
			e.preventDefault();
			switchTab(link.dataset.switchTab);
		});
	});

	// Обробка форми входу
	const loginForm = document.getElementById('login-form');
	if (loginForm) {
		loginForm.addEventListener('submit', async (e) => {
			e.preventDefault();
			await handleSubmit(loginForm, 'natura_login');
		});
	}

	// Обробка форми реєстрації
	const registerForm = document.getElementById('register-form');
	if (registerForm) {
		registerForm.addEventListener('submit', async (e) => {
			e.preventDefault();
			await handleSubmit(registerForm, 'natura_register');
		});
	}

	// Обробка форми відновлення пароля
	const lostPasswordForm = document.getElementById('lost-password-form');
	if (lostPasswordForm) {
		lostPasswordForm.addEventListener('submit', async (e) => {
			e.preventDefault();
			await handleLostPassword(lostPasswordForm);
		});
	}

	// Флаг для предотвращения множественных отправок формы
	const submittingForms = new Set();

	const handleSubmit = debounce(async (form, action) => {
		// Проверяем, не отправляется ли уже форма
		if (submittingForms.has(form)) {
			return;
		}

		const submitBtn = form.querySelector('.auth-page__submit');
		const submitText = form.querySelector('.auth-page__submit-text');
		const submitLoader = form.querySelector('.auth-page__submit-loader');
		const errorDiv = form.querySelector('[data-error]');

		// Disable button, show loader
		submittingForms.add(form);
		submitBtn.disabled = true;
		submitText.style.display = 'none';
		submitLoader.style.display = 'inline-block';
		errorDiv.classList.remove('auth-page__error--visible');
		errorDiv.textContent = '';

		const formData = new FormData(form);
		formData.append('action', action);
		formData.append('nonce', form.querySelector('[name="auth_nonce"]').value);

		try {
			const response = await fetch(naturaAuth.ajaxUrl, {
				method: 'POST',
				body: formData,
				credentials: 'same-origin',
				headers: {
					'X-Requested-With': 'XMLHttpRequest'
				}
			});

			const data = await response.json();

			if (data.success) {
				// Redirect on success
				window.location.href = data.data.redirect;
			} else {
				// Show error
				errorDiv.textContent = data.data.message;
				errorDiv.classList.add('auth-page__error--visible');
			}
		} catch (error) {
			errorDiv.textContent = 'Сталася помилка. Спробуйте пізніше.';
			errorDiv.classList.add('auth-page__error--visible');
		} finally {
			// Re-enable button
			submittingForms.delete(form);
			submitBtn.disabled = false;
			submitText.style.display = 'inline';
			submitLoader.style.display = 'none';
		}
	}, 500);

	// Флаг для предотвращения множественных запросов восстановления пароля
	let isSubmittingPassword = false;

	const handleLostPassword = debounce(async (form) => {
		// Проверяем, не отправляется ли уже запрос
		if (isSubmittingPassword) {
			return;
		}

		const submitBtn = form.querySelector('.auth-page__submit');
		const errorDiv = form.querySelector('[data-error]');
		const formTop = form.querySelector('.auth-page__form-top');
		const formBottom = form.querySelector('.auth-page__form-bottom');

		// Disable button
		isSubmittingPassword = true;
		submitBtn.disabled = true;
		submitBtn.textContent = 'Відправка...';
		errorDiv.classList.remove('auth-page__error--visible');
		errorDiv.textContent = '';

		const userLogin = form.querySelector('[name="user_login"]').value;
		const nonceField = form.querySelector('[name="woocommerce-lost-password-nonce"]');
		const nonce = nonceField ? nonceField.value : '';
		
		console.log('[handleLostPassword] user_login:', userLogin);
		console.log('[handleLostPassword] nonce:', nonce);
		
		// Перевіряємо чи naturaAuth визначено
		if (typeof naturaAuth === 'undefined') {
			errorDiv.textContent = 'Помилка конфігурації. Оновіть сторінку.';
			errorDiv.classList.add('auth-page__error--visible');
			submitBtn.disabled = false;
			submitBtn.textContent = 'Скинути пароль';
			return;
		}

		// Використовуємо URLSearchParams для кращої сумісності
		const params = new URLSearchParams();
		params.append('action', 'natura_lost_password');
		params.append('user_login', userLogin);
		params.append('nonce', nonce);

		try {
			console.log('[handleLostPassword] Sending to:', naturaAuth.ajaxUrl);
			console.log('[handleLostPassword] Params:', params.toString());
			
			const response = await fetch(naturaAuth.ajaxUrl, {
				method: 'POST',
				body: params,
				credentials: 'same-origin',
				headers: {
					'Content-Type': 'application/x-www-form-urlencoded',
					'X-Requested-With': 'XMLHttpRequest'
				}
			});

			console.log('[handleLostPassword] Response status:', response.status);
			const responseText = await response.text();
			console.log('[handleLostPassword] Response text:', responseText.substring(0, 500));
			
			let data;
			try {
				data = JSON.parse(responseText);
			} catch (e) {
				console.error('[handleLostPassword] JSON parse error, raw response:', responseText.substring(0, 1000));
				throw new Error('Server returned invalid response');
			}
			console.log('[handleLostPassword] Response:', data);

			if (data.success) {
				// Показуємо повідомлення про успіх
				formTop.classList.add('auth-page__form-top--success');
				formTop.innerHTML = `
					<h2 class="auth-page__success-title">Лист для відновлення паролю надіслано.</h2>
					<p class="auth-page__success-text">Лист з посиланням для відновлення паролю було надіслано на адресу електронної пошти, прив'язану до вашого облікового запису, доставка повідомлення може зайняти кілька хвилин. Будь ласка, зачекайте не менше 10 хвилин, перш ніж ініціювати ще один запит.</p>
				`;
				formBottom.innerHTML = `
					<a href="${naturaAuth.shopUrl || '/'}" class="auth-page__submit auth-page__submit--link">Повернутись до каталогу</a>
				`;
			} else {
				// Show error
				errorDiv.textContent = data.data.message;
				errorDiv.classList.add('auth-page__error--visible');
				submitBtn.disabled = false;
				submitBtn.textContent = 'Скинути пароль';
			}
		} catch (error) {
			console.error('[handleLostPassword] Error:', error);
			errorDiv.textContent = 'Сталася помилка. Спробуйте пізніше.';
			errorDiv.classList.add('auth-page__error--visible');
			submitBtn.disabled = false;
			submitBtn.textContent = 'Скинути пароль';
		} finally {
			isSubmittingPassword = false;
		}
	}, 1000);

	// Обработка кнопок Google OAuth
	const googleButtons = document.querySelectorAll('.auth-page__google[data-google-action]');
	
	googleButtons.forEach(button => {
		button.addEventListener('click', async (e) => {
			e.preventDefault();
			
			const actionType = button.getAttribute('data-google-action'); // 'login' или 'register'
			
			if (!actionType) {
				console.error('Google button: action type not specified');
				return;
			}
			
			// Проверяем, что naturaAuth определен
			if (typeof naturaAuth === 'undefined') {
				console.error('naturaAuth is not defined');
				alert('Помилка конфігурації. Оновіть сторінку.');
				return;
			}
			
			// Отключаем кнопку
			button.disabled = true;
			const originalText = button.querySelector('span').textContent;
			button.querySelector('span').textContent = 'Завантаження...';
			
			try {
				// Получаем URL для авторизации через Google
				const params = new URLSearchParams();
				params.append('action', 'natura_google_oauth_url');
				params.append('action_type', actionType);
				
				const response = await fetch(naturaAuth.ajaxUrl, {
					method: 'POST',
					body: params,
					credentials: 'same-origin',
					headers: {
						'Content-Type': 'application/x-www-form-urlencoded',
						'X-Requested-With': 'XMLHttpRequest'
					}
				});
				
				const data = await response.json();
				
				if (data.success && data.data.auth_url) {
					// Прямой редирект на страницу авторизации Google в текущем окне
					window.location.href = data.data.auth_url;
				} else {
					throw new Error(data.data?.message || 'Помилка при отриманні URL авторизації');
				}
			} catch (error) {
				console.error('Google OAuth error:', error);
				alert('Помилка при авторизації через Google. Спробуйте пізніше.');
				// Восстанавливаем кнопку
				button.disabled = false;
				button.querySelector('span').textContent = originalText;
			}
		});
	});
})();



