<?php
/**
 * Form handlers for collaboration and feedback forms.
 *
 * @package Natura
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Register Custom Post Type for form submissions
 */
function natura_register_form_submissions_cpt() {
	$labels = array(
		'name'               => __( 'Заявки', 'natura' ),
		'singular_name'      => __( 'Заявка', 'natura' ),
		'menu_name'          => __( 'Заявки', 'natura' ),
		'add_new'            => __( 'Додати нову', 'natura' ),
		'add_new_item'       => __( 'Додати нову заявку', 'natura' ),
		'edit_item'          => __( 'Редагувати заявку', 'natura' ),
		'new_item'           => __( 'Нова заявка', 'natura' ),
		'view_item'          => __( 'Переглянути заявку', 'natura' ),
		'search_items'       => __( 'Шукати заявки', 'natura' ),
		'not_found'          => __( 'Заявки не знайдено', 'natura' ),
		'not_found_in_trash' => __( 'Заявки не знайдено в кошику', 'natura' ),
	);

	$args = array(
		'labels'              => $labels,
		'public'              => false,
		'show_ui'             => true,
		'show_in_menu'        => true,
		'menu_icon'           => 'dashicons-email-alt',
		'capability_type'     => 'post',
		'hierarchical'       => false,
		'supports'            => array( 'title', 'editor' ),
		'has_archive'         => false,
		'rewrite'             => false,
		'query_var'           => false,
		'can_export'          => true,
		'show_in_rest'        => false,
	);

	register_post_type( 'natura_form_submission', $args );
}
add_action( 'init', 'natura_register_form_submissions_cpt' );

/**
 * Add meta boxes for form submissions
 */
function natura_add_form_submission_meta_boxes() {
	add_meta_box(
		'natura_form_submission_details',
		__( 'Деталі заявки', 'natura' ),
		'natura_render_form_submission_meta_box',
		'natura_form_submission',
		'normal',
		'high'
	);
}
add_action( 'add_meta_boxes', 'natura_add_form_submission_meta_boxes' );

/**
 * Render meta box for form submission details
 */
function natura_render_form_submission_meta_box( $post ) {
	$form_type = get_post_meta( $post->ID, '_form_type', true );
	$name      = get_post_meta( $post->ID, '_submission_name', true );
	$phone     = get_post_meta( $post->ID, '_submission_phone', true );
	$message   = get_post_meta( $post->ID, '_submission_message', true );
	$email     = get_post_meta( $post->ID, '_submission_email', true );

	wp_nonce_field( 'natura_form_submission_meta_box', 'natura_form_submission_meta_box_nonce' );
	?>
	<table class="form-table">
		<tr>
			<th><label><?php esc_html_e( 'Тип форми', 'natura' ); ?></label></th>
			<td>
				<strong><?php echo esc_html( $form_type === 'collaboration' ? __( 'Співпраця', 'natura' ) : __( 'Зворотний зв\'язок', 'natura' ) ); ?></strong>
			</td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Ім\'я', 'natura' ); ?></label></th>
			<td><?php echo esc_html( $name ); ?></td>
		</tr>
		<tr>
			<th><label><?php esc_html_e( 'Телефон', 'natura' ); ?></label></th>
			<td><a href="tel:<?php echo esc_attr( $phone ); ?>"><?php echo esc_html( $phone ); ?></a></td>
		</tr>
		<?php if ( $email ) : ?>
		<tr>
			<th><label><?php esc_html_e( 'Email', 'natura' ); ?></label></th>
			<td><a href="mailto:<?php echo esc_attr( $email ); ?>"><?php echo esc_html( $email ); ?></a></td>
		</tr>
		<?php endif; ?>
		<tr>
			<th><label><?php esc_html_e( 'Повідомлення', 'natura' ); ?></label></th>
			<td><?php echo nl2br( esc_html( $message ) ); ?></td>
		</tr>
	</table>
	<?php
}

/**
 * Save form submission meta data
 */
function natura_save_form_submission_meta( $post_id ) {
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
		return;
	}

	if ( ! isset( $_POST['natura_form_submission_meta_box_nonce'] ) ||
		 ! wp_verify_nonce( $_POST['natura_form_submission_meta_box_nonce'], 'natura_form_submission_meta_box' ) ) {
		return;
	}

	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return;
	}

	if ( isset( $_POST['_form_type'] ) ) {
		update_post_meta( $post_id, '_form_type', sanitize_text_field( $_POST['_form_type'] ) );
	}
	if ( isset( $_POST['_submission_name'] ) ) {
		update_post_meta( $post_id, '_submission_name', sanitize_text_field( $_POST['_submission_name'] ) );
	}
	if ( isset( $_POST['_submission_phone'] ) ) {
		update_post_meta( $post_id, '_submission_phone', sanitize_text_field( $_POST['_submission_phone'] ) );
	}
	if ( isset( $_POST['_submission_message'] ) ) {
		update_post_meta( $post_id, '_submission_message', sanitize_textarea_field( $_POST['_submission_message'] ) );
	}
	if ( isset( $_POST['_submission_email'] ) ) {
		update_post_meta( $post_id, '_submission_email', sanitize_email( $_POST['_submission_email'] ) );
	}
}
add_action( 'save_post_natura_form_submission', 'natura_save_form_submission_meta' );

/**
 * AJAX handler for collaboration form
 */
function natura_ajax_collaboration_form() {
	check_ajax_referer( 'natura_forms_nonce', 'nonce' );

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
	$comment = isset( $_POST['comment'] ) ? sanitize_textarea_field( $_POST['comment'] ) : '';

	if ( empty( $name ) || empty( $phone ) || empty( $comment ) ) {
		wp_send_json_error( array( 'message' => __( 'Будь ласка, заповніть всі обов\'язкові поля.', 'natura' ) ) );
		return;
	}

	// Validate phone
	if ( ! preg_match( '/^[\d\s\-\+\(\)]+$/', $phone ) ) {
		wp_send_json_error( array( 'message' => __( 'Невірний формат телефону.', 'natura' ) ) );
		return;
	}

	// Save to database
	$post_id = wp_insert_post(
		array(
			'post_title'   => sprintf( __( 'Заявка на співпрацю від %s', 'natura' ), $name ),
			'post_content' => $comment,
			'post_status'  => 'publish',
			'post_type'    => 'natura_form_submission',
		)
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Помилка збереження заявки.', 'natura' ) ) );
		return;
	}

	// Save meta data
	update_post_meta( $post_id, '_form_type', 'collaboration' );
	update_post_meta( $post_id, '_submission_name', $name );
	update_post_meta( $post_id, '_submission_phone', $phone );
	update_post_meta( $post_id, '_submission_message', $comment );

	// Get admin email
	$admin_email = get_option( 'admin_email' );

	// Prepare email
	$subject = sprintf( __( 'Нова заявка на співпрацю від %s', 'natura' ), $name );
	$message = sprintf(
		__( "Нова заявка на співпрацю\n\nІм'я: %s\nТелефон: %s\n\nПовідомлення:\n%s\n\n---\nЦе повідомлення надіслано з сайту %s", 'natura' ),
		$name,
		$phone,
		$comment,
		get_bloginfo( 'name' )
	);

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>',
		'Reply-To: ' . $name . ' <noreply@' . parse_url( home_url(), PHP_URL_HOST ) . '>',
	);

	// Send email
	$email_sent = wp_mail( $admin_email, $subject, $message, $headers );

	if ( ! $email_sent ) {
		// Log error but don't fail the request
		error_log( 'Failed to send collaboration form email' );
	}

	wp_send_json_success(
		array(
			'message' => __( 'Дякуємо! Ваша заявка прийнята. Ми зв\'яжемося з вами найближчим часом.', 'natura' ),
		)
	);
}
add_action( 'wp_ajax_natura_collaboration_form', 'natura_ajax_collaboration_form' );
add_action( 'wp_ajax_nopriv_natura_collaboration_form', 'natura_ajax_collaboration_form' );

/**
 * AJAX handler for feedback form
 */
function natura_ajax_feedback_form() {
	check_ajax_referer( 'natura_forms_nonce', 'nonce' );

	$name    = isset( $_POST['name'] ) ? sanitize_text_field( $_POST['name'] ) : '';
	$phone   = isset( $_POST['phone'] ) ? sanitize_text_field( $_POST['phone'] ) : '';
	$message = isset( $_POST['message'] ) ? sanitize_textarea_field( $_POST['message'] ) : '';

	if ( empty( $name ) || empty( $phone ) || empty( $message ) ) {
		wp_send_json_error( array( 'message' => __( 'Будь ласка, заповніть всі обов\'язкові поля.', 'natura' ) ) );
		return;
	}

	// Validate phone
	if ( ! preg_match( '/^[\d\s\-\+\(\)]+$/', $phone ) ) {
		wp_send_json_error( array( 'message' => __( 'Невірний формат телефону.', 'natura' ) ) );
		return;
	}

	// Save to database
	$post_id = wp_insert_post(
		array(
			'post_title'   => sprintf( __( 'Відгук від %s', 'natura' ), $name ),
			'post_content' => $message,
			'post_status'  => 'publish',
			'post_type'    => 'natura_form_submission',
		)
	);

	if ( is_wp_error( $post_id ) ) {
		wp_send_json_error( array( 'message' => __( 'Помилка збереження відгуку.', 'natura' ) ) );
		return;
	}

	// Save meta data
	update_post_meta( $post_id, '_form_type', 'feedback' );
	update_post_meta( $post_id, '_submission_name', $name );
	update_post_meta( $post_id, '_submission_phone', $phone );
	update_post_meta( $post_id, '_submission_message', $message );

	// Get admin email
	$admin_email = get_option( 'admin_email' );

	// Prepare email
	$subject = sprintf( __( 'Новий відгук від %s', 'natura' ), $name );
	$message_body = sprintf(
		__( "Новий відгук\n\nІм'я: %s\nТелефон: %s\n\nВідгук:\n%s\n\n---\nЦе повідомлення надіслано з сайту %s", 'natura' ),
		$name,
		$phone,
		$message,
		get_bloginfo( 'name' )
	);

	$headers = array(
		'Content-Type: text/plain; charset=UTF-8',
		'From: ' . get_bloginfo( 'name' ) . ' <' . $admin_email . '>',
		'Reply-To: ' . $name . ' <noreply@' . parse_url( home_url(), PHP_URL_HOST ) . '>',
	);

	// Send email
	$email_sent = wp_mail( $admin_email, $subject, $message_body, $headers );

	if ( ! $email_sent ) {
		// Log error but don't fail the request
		error_log( 'Failed to send feedback form email' );
	}

	wp_send_json_success(
		array(
			'message' => __( 'Дякуємо за ваш відгук! Ми цінуємо вашу думку.', 'natura' ),
		)
	);
}
add_action( 'wp_ajax_natura_feedback_form', 'natura_ajax_feedback_form' );
add_action( 'wp_ajax_nopriv_natura_feedback_form', 'natura_ajax_feedback_form' );
