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
		'name'               => 'Заявки з сайту',
		'singular_name'      => 'Заявка',
		'menu_name'          => 'Заявки',
		'add_new'            => 'Додати нову',
		'add_new_item'       => 'Додати нову заявку',
		'edit_item'          => 'Редагувати заявку',
		'new_item'           => 'Нова заявка',
		'view_item'          => 'Переглянути заявку',
		'search_items'       => 'Шукати заявки',
		'not_found'          => 'Заявки не знайдено',
		'not_found_in_trash' => 'Заявки не знайдено в кошику',
		'all_items'          => 'Всі заявки',
	);

	$args = array(
		'labels'             => $labels,
		'public'             => false,
		'publicly_queryable' => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'menu_position'      => 26,
		'menu_icon'          => 'dashicons-email-alt',
		'capability_type'    => 'post',
		'hierarchical'       => false,
		'supports'           => array( 'title' ),
		'has_archive'        => false,
		'rewrite'            => false,
		'query_var'          => false,
		'can_export'         => true,
		'exclude_from_search'=> true,
	);

	register_post_type( 'natura_submission', $args );
}
add_action( 'init', 'natura_register_form_submissions_cpt', 0 );

/**
 * Add custom columns to form submissions list
 */
function natura_add_form_submission_columns( $columns ) {
	$new_columns = array();
	$new_columns['cb'] = $columns['cb'];
	$new_columns['title'] = __( 'Заявка', 'natura' );
	$new_columns['form_type'] = __( 'Тип форми', 'natura' );
	$new_columns['submission_name'] = __( 'Ім\'я', 'natura' );
	$new_columns['submission_phone'] = __( 'Телефон', 'natura' );
	$new_columns['date'] = $columns['date'];
	return $new_columns;
}
add_filter( 'manage_natura_submission_posts_columns', 'natura_add_form_submission_columns' );

/**
 * Populate custom columns
 */
function natura_populate_form_submission_columns( $column, $post_id ) {
	switch ( $column ) {
		case 'form_type':
			$form_type = get_post_meta( $post_id, '_form_type', true );
			if ( $form_type === 'collaboration' ) {
				echo '<span style="color: #2271b1;">' . esc_html__( 'Співпраця', 'natura' ) . '</span>';
			} elseif ( $form_type === 'feedback' ) {
				echo '<span style="color: #00a32a;">' . esc_html__( 'Зворотний зв\'язок', 'natura' ) . '</span>';
			} else {
				echo '—';
			}
			break;
		case 'submission_name':
			$name = get_post_meta( $post_id, '_submission_name', true );
			echo $name ? esc_html( $name ) : '—';
			break;
		case 'submission_phone':
			$phone = get_post_meta( $post_id, '_submission_phone', true );
			if ( $phone ) {
				echo '<a href="tel:' . esc_attr( $phone ) . '">' . esc_html( $phone ) . '</a>';
			} else {
				echo '—';
			}
			break;
	}
}
add_action( 'manage_natura_submission_posts_custom_column', 'natura_populate_form_submission_columns', 10, 2 );

/**
 * Make columns sortable
 */
function natura_make_form_submission_columns_sortable( $columns ) {
	$columns['form_type'] = 'form_type';
	$columns['submission_name'] = 'submission_name';
	return $columns;
}
add_filter( 'manage_edit-natura_submission_sortable_columns', 'natura_make_form_submission_columns_sortable' );

/**
 * Add filter dropdown for form types
 */
function natura_add_form_type_filter() {
	global $typenow;
	if ( $typenow === 'natura_submission' ) {
		$selected = isset( $_GET['form_type_filter'] ) ? $_GET['form_type_filter'] : '';
		?>
		<select name="form_type_filter">
			<option value=""><?php esc_html_e( 'Всі типи форм', 'natura' ); ?></option>
			<option value="collaboration" <?php selected( $selected, 'collaboration' ); ?>><?php esc_html_e( 'Співпраця', 'natura' ); ?></option>
			<option value="feedback" <?php selected( $selected, 'feedback' ); ?>><?php esc_html_e( 'Зворотний зв\'язок', 'natura' ); ?></option>
		</select>
		<?php
	}
}
add_action( 'restrict_manage_posts', 'natura_add_form_type_filter' );

/**
 * Filter posts by form type
 */
function natura_filter_form_submissions_by_type( $query ) {
	global $pagenow, $typenow;
	
	if ( $pagenow === 'edit.php' && $typenow === 'natura_submission' && isset( $_GET['form_type_filter'] ) && $_GET['form_type_filter'] !== '' ) {
		$query->set( 'meta_key', '_form_type' );
		$query->set( 'meta_value', sanitize_text_field( $_GET['form_type_filter'] ) );
	}
}
add_action( 'parse_query', 'natura_filter_form_submissions_by_type' );

/**
 * Add meta boxes for form submissions
 */
function natura_add_form_submission_meta_boxes() {
	add_meta_box(
		'natura_submission_details',
		__( 'Деталі заявки', 'natura' ),
		'natura_render_form_submission_meta_box',
		'natura_submission',
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

	wp_nonce_field( 'natura_submission_meta_box', 'natura_submission_meta_box_nonce' );
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

	if ( ! isset( $_POST['natura_submission_meta_box_nonce'] ) ||
		 ! wp_verify_nonce( $_POST['natura_submission_meta_box_nonce'], 'natura_submission_meta_box' ) ) {
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
add_action( 'save_post_natura_submission', 'natura_save_form_submission_meta' );

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
			'post_type'    => 'natura_submission',
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
		'Reply-To: ' . $admin_email,
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
			'post_type'    => 'natura_submission',
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
		'Reply-To: ' . $admin_email,
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
