<?php
/**
 * Template Name: Авторизація
 * Сторінка входу та реєстрації
 */

get_header();

$active_tab = 'login';
if (isset($_GET['tab'])) {
	if ($_GET['tab'] === 'register') {
		$active_tab = 'register';
	} elseif ($_GET['tab'] === 'lost-password') {
		$active_tab = 'lost-password';
	}
}
?>

<main class="auth-page">
	<div class="auth-page__container">
		<div class="auth-page__image">
			<img src="https://bisque-parrot-207888.hostingersite.com/wp-content/uploads/2025/12/rectangle-107_result.webp" alt="Natura Market">
		</div>
		<div class="auth-page__form-wrapper">
			<div class="auth-page__tabs <?php echo $active_tab === 'lost-password' ? 'auth-page__tabs--hidden' : ''; ?>">
				<button type="button" class="auth-page__tab <?php echo $active_tab === 'login' ? 'auth-page__tab--active' : ''; ?>" data-tab="login">
					Вхід
				</button>
				<button type="button" class="auth-page__tab <?php echo $active_tab === 'register' ? 'auth-page__tab--active' : ''; ?>" data-tab="register">
					Реєстрація
				</button>
			</div>

			<!-- Форма входу -->
			<form class="auth-page__form <?php echo $active_tab === 'login' ? 'auth-page__form--active' : ''; ?>" id="login-form" data-form="login">
				<div class="auth-page__field">
					<label class="auth-page__label" for="login-email">Email *</label>
					<input type="email" class="auth-page__input" id="login-email" name="email" required autocomplete="email">
				</div>
				<div class="auth-page__field">
					<label class="auth-page__label" for="login-password">Пароль *</label>
					<input type="password" class="auth-page__input" id="login-password" name="password" required autocomplete="current-password">
				</div>
				<div class="auth-page__error" data-error></div>
				<div class="auth-page__buttons">
					<button type="submit" class="auth-page__submit">
						<span class="auth-page__submit-text">Увійти</span>
						<span class="auth-page__submit-loader" style="display: none;"></span>
					</button>
					<button type="button" class="auth-page__google" disabled>
						<img src="<?php echo esc_url(get_theme_file_uri('assets/img/auth/google-icon.svg')); ?>" alt="Google" class="auth-page__google-icon">
						<span>Вхід через Google</span>
					</button>
				</div>
				<a href="<?php echo esc_url(get_permalink() . '?tab=lost-password'); ?>" class="auth-page__forgot">Забули пароль?</a>
				<?php wp_nonce_field('natura_auth_nonce', 'auth_nonce', false); ?>
			</form>

			<!-- Форма реєстрації -->
			<form class="auth-page__form <?php echo $active_tab === 'register' ? 'auth-page__form--active' : ''; ?>" id="register-form" data-form="register">
				<div class="auth-page__field">
					<label class="auth-page__label" for="register-email">Email *</label>
					<input type="email" class="auth-page__input" id="register-email" name="email" required autocomplete="email">
				</div>
				<div class="auth-page__field">
					<label class="auth-page__label" for="register-password">Пароль *</label>
					<input type="password" class="auth-page__input" id="register-password" name="password" required autocomplete="new-password">
				</div>
				<div class="auth-page__error" data-error></div>
				<div class="auth-page__buttons">
					<button type="submit" class="auth-page__submit">
						<span class="auth-page__submit-text">Зареєструватись</span>
						<span class="auth-page__submit-loader" style="display: none;"></span>
					</button>
					<button type="button" class="auth-page__google" disabled>
						<img src="<?php echo esc_url(get_theme_file_uri('assets/img/auth/google-icon.svg')); ?>" alt="Google" class="auth-page__google-icon">
						<span>Зареєструватись через Google</span>
					</button>
				</div>
				<p class="auth-page__login-link">Вже маєте профіль? <a href="<?php echo esc_url(get_permalink()); ?>?tab=login">Увійти</a></p>
				<?php wp_nonce_field('natura_auth_nonce', 'auth_nonce', false); ?>
			</form>

			<!-- Форма відновлення пароля -->
			<form class="auth-page__form auth-page__form--lost <?php echo $active_tab === 'lost-password' ? 'auth-page__form--active' : ''; ?>" id="lost-password-form" data-form="lost-password" method="post">
				<div class="auth-page__form-top">
					<p class="auth-page__lost-password-text">Забули пароль? Будь ласка, введіть ваш<br>логін або адресу електронної пошти. Ви<br>отримаєте електронною поштою посилання<br>для створення нового паролю.</p>
					<div class="auth-page__field">
						<label class="auth-page__label" for="user_login">Email *</label>
						<input type="text" class="auth-page__input" id="user_login" name="user_login" required autocomplete="username">
					</div>
					<div class="auth-page__error" data-error></div>
				</div>
				<div class="auth-page__form-bottom">
					<input type="hidden" name="wc_reset_password" value="true" />
					<button type="submit" class="auth-page__submit">Скинути пароль</button>
				</div>
				<?php wp_nonce_field('lost_password', 'woocommerce-lost-password-nonce'); ?>
			</form>
		</div>
	</div>
</main>

<?php get_footer(); ?>
