<?php

if (!function_exists('natura_setup')) {
	function natura_setup(): void {
		load_theme_textdomain('natura', get_template_directory() . '/languages');
		add_theme_support('title-tag');
		add_theme_support('post-thumbnails');
		add_theme_support(
			'html5',
			array(
				'comment-form',
				'comment-list',
				'gallery',
				'caption',
				'style',
				'script',
			)
		);
		add_theme_support('customize-selective-refresh-widgets');
		add_theme_support('editor-styles');
		add_editor_style('assets/css/editor.css');
		register_nav_menus(
			array(
				'primary' => __('Главное меню', 'natura'),
				'footer'  => __('Меню в подвале', 'natura'),
			)
		);
	}
}
add_action('after_setup_theme', 'natura_setup');

function natura_set_content_width(): void {
	$GLOBALS['content_width'] = 1200;
}
add_action('after_setup_theme', 'natura_set_content_width', 0);

function natura_widgets_init(): void {
	register_sidebar(
		array(
			'name'          => __('Сайдбар', 'natura'),
			'id'            => 'sidebar-1',
			'description'   => __('Добавьте виджеты для отображения в сайдбаре.', 'natura'),
			'before_widget' => '<section id="%1$s" class="widget %2$s">',
			'after_widget'  => '</section>',
			'before_title'  => '<h2 class="widget-title">',
			'after_title'   => '</h2>',
		)
	);
}
add_action('widgets_init', 'natura_widgets_init');

if (!function_exists('wp_body_open')) {
	function wp_body_open(): void {
		do_action('wp_body_open');
	}
}

/**
 * Disable caching while we develop the theme.
 * ВИМКНЕНО ДЛЯ ПРОДАКШЕНУ - розкоментуйте для розробки
 */
/*
if (defined('WP_DEBUG') && WP_DEBUG) {
	function natura_dev_disable_caching_flags(): void {
		if (!defined('DONOTCACHEPAGE')) {
			define('DONOTCACHEPAGE', true);
		}
		if (!defined('DONOTCACHEOBJECT')) {
			define('DONOTCACHEOBJECT', true);
		}
		if (!defined('DONOTCACHEDB')) {
			define('DONOTCACHEDB', true);
		}
	}
	add_action('init', 'natura_dev_disable_caching_flags');

	function natura_dev_send_nocache_headers(): void {
		nocache_headers();
	}
	add_action('send_headers', 'natura_dev_send_nocache_headers');
}
*/


