<?php


	/**
	 * Create the metaboxes
	 */
	function gmt_edd_for_courses_create_metaboxes() {

		// Downloads that can access course
		add_meta_box( 'gmt_edd_for_courses_metabox_downloads', __( 'Downloads', 'gmt_edd_for_courses' ), 'gmt_edd_for_courses_render_metabox_downloads', 'gmt_courses', 'side', 'default');

		// Where to redirect users without access
		add_meta_box( 'gmt_edd_for_courses_metabox_redirects', __( 'Redirects', 'gmt_edd_for_courses' ), 'gmt_edd_for_courses_render_metabox_redirects', 'gmt_courses', 'side', 'default');

	}
	add_action( 'add_meta_boxes', 'gmt_edd_for_courses_create_metaboxes' );



	/**
	 * Render the downloads metabox
	 */
	function gmt_edd_for_courses_render_metabox_downloads() {

		// Variables
		global $post;
		$downloads = gmt_edd_for_courses_get_downloads();
		$access = (array) get_post_meta( $post->ID, 'gmt_edd_for_courses_downloads', true );

		?>

			<p><?php _e( 'The downloads that have access to this course.', 'gmt_edd_for_courses' ); ?></p>

			<fieldset>

				<?php foreach( $downloads as $download ) : ?>
					<label>
						<input type="checkbox" name="gmt_edd_for_courses_downloads[<?php echo esc_attr( $download->ID ); ?>]" value="<?php echo esc_attr( $download->ID ); ?>" <?php if ( array_key_exists( $download->ID, $access ) ) { echo 'checked'; } ?>>
						<?php echo $download->post_title; ?>
					</label>
					<br>
					<?php if ( edd_has_variable_prices( $download->ID ) ) :	?>
						<label style="margin-left: 25px;">
							<input type="checkbox" name="gmt_edd_for_courses_downloads[<?php echo esc_attr( $download->ID ); ?>][0]" value="0" <?php if ( array_key_exists( $download->ID, $access ) && is_array( $access[$download->ID] ) && array_key_exists( 0, $access[$download->ID] ) ) { echo 'checked'; } ?>>
							<?php _e( 'Single-price purchase', 'gmt_edd_for_courses' ); ?>
						</label>
						<br>
						<?php
							$prices = edd_get_variable_prices( $download->ID );
							foreach( $prices as $price_key => $price ) :
						?>
							<label style="margin-left: 25px;">
								<input type="checkbox" name="gmt_edd_for_courses_downloads[<?php echo esc_attr( $download->ID ); ?>][<?php echo esc_attr( $price_key ); ?>]" value="<?php echo esc_attr( $price_key ); ?>" <?php if ( array_key_exists( $download->ID, $access ) && is_array( $access[$download->ID] ) && array_key_exists( $price_key, $access[$download->ID] ) ) { echo 'checked'; } ?>>
								<?php echo esc_html( $price['name'] ); ?>
							</label>
							<br>
						<?php endforeach; ?>
					<?php endif; ?>
				<?php endforeach; ?>

			</fieldset>

		<?php

		wp_nonce_field( 'gmt_edd_for_courses_metabox_downloads_nonce', 'gmt_edd_for_courses_metabox_downloads_process' );
	}



	/**
	 * Render the redirects metabox
	 */
	function gmt_edd_for_courses_render_metabox_redirects() {

		// Variables
		global $post;
		$redirect = get_post_meta( $post->ID, 'gmt_edd_for_courses_redirects', true );
		$pages = get_pages(
			array(
				'sort_order' => 'asc',
				'sort_column' => 'post_title',
				'post_type' => 'page',
				'post_status' => 'publish'
			)
		);

		?>

			<fieldset>

				<p><?php _e( 'Where to redirect users who don\'t have access to this course.', 'gmt_edd_for_courses' ); ?></p>

				<select name="gmt_edd_for_courses_redirects">
					<option value="" <?php selected( $redirect, '' ); ?>><?php _e( 'Don\'t Redirect', 'gmt_edd_for_courses' ); ?></option>
					<option value="0" <?php selected( $redirect, '0' ); ?>><?php _e( 'Home', 'gmt_edd_for_courses' ); ?></option>

					<?php foreach( $pages as $page ) : ?>
						<option value="<?php echo $page->ID; ?>" <?php selected( $redirect, $page->ID ); ?>><?php echo $page->post_title; ?></option>
					<?php endforeach; ?>
				</select>

			</fieldset>

		<?php

		wp_nonce_field( 'gmt_edd_for_courses_metabox_redirects_nonce', 'gmt_edd_for_courses_metabox_redirects_process' );
	}





	/**
	 * Save the lesson video metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function gmt_edd_for_courses_save_metabox_downloads( $post_id, $post ) {

		if ( !isset( $_POST['gmt_edd_for_courses_metabox_downloads_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['gmt_edd_for_courses_metabox_downloads_process'], 'gmt_edd_for_courses_metabox_downloads_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Sanitize and save details
		$sanitized = array();
		if ( isset( $_POST['gmt_edd_for_courses_downloads'] ) ) {
			foreach( $_POST['gmt_edd_for_courses_downloads'] as $download_key => $download ) {
				if ( is_array( $download ) ) {
					$prices = array();
					foreach( $download as $price_key => $price ) {
						$prices[$price_key] = 'on';
					}
					$sanitized[$download_key] = $prices;
				} else {
					$sanitized[$download_key] = 'on';
				}
			}
		}
		update_post_meta( $post->ID, 'gmt_edd_for_courses_downloads', $sanitized );

	}
	add_action( 'save_post', 'gmt_edd_for_courses_save_metabox_downloads', 10, 2 );



	/**
	 * Save the redirects metabox
	 * @param  Number $post_id The post ID
	 * @param  Array  $post    The post data
	 */
	function gmt_edd_for_courses_save_metabox_redirects( $post_id, $post ) {

		if ( !isset( $_POST['gmt_edd_for_courses_metabox_redirects_process'] ) ) return;

		// Verify data came from edit screen
		if ( !wp_verify_nonce( $_POST['gmt_edd_for_courses_metabox_redirects_process'], 'gmt_edd_for_courses_metabox_redirects_nonce' ) ) {
			return $post->ID;
		}

		// Verify user has permission to edit post
		if ( !current_user_can( 'edit_post', $post->ID )) {
			return $post->ID;
		}

		// Sanitize and save details
		if ( !isset( $_POST['gmt_edd_for_courses_redirects'] ) ) return;
		update_post_meta( $post->ID, 'gmt_edd_for_courses_redirects', $_POST['gmt_edd_for_courses_redirects'] );

	}
	add_action( 'save_post', 'gmt_edd_for_courses_save_metabox_redirects', 10, 2 );