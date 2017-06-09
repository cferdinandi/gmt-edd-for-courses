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
		$access = (array) get_post_meta( $post->ID, 'gmt_edd_for_courses_downloads', true );
		$downloads = gmt_edd_for_courses_get_products();

		?>

			<p><?php _e( 'The downloads that have access to this course.', 'gmt_edd_for_courses' ); ?></p>

			<fieldset>

				<?php foreach ($downloads['products'] as $download) : ?>

					<p><strong><?php echo esc_html( $download['title'] ); ?></strong></p>

					<?php foreach ($download['pricing'] as $label => $price) : ?>
						<label>
							<input type="checkbox" name="gmt_edd_for_courses_downloads[<?php echo esc_attr( $download['id'] ); ?>][<?php echo esc_attr( $price['index'] ); ?>]" value="on" <?php if ( array_key_exists($download['id'], $access) && array_key_exists($price['index'], $access[$download['id']]) && $access[$download['id']][$price['index']] === 'on' ) { echo 'checked="checked"'; } ?>>
							<?php if ( $label === 'amount' ) : ?>
								<?php echo esc_html( $download['title'] ); ?>
							<?php else: ?>
								<?php echo esc_html( $label ); ?>
							<?php endif; ?>
						</label>
						<br>
					<?php endforeach; ?>

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
				foreach($download as $price_key => $price) {
					if ( $price_key === 'bundles' ) {
						$sanitized[$download_key]['bundles'] = $price;
						continue;
					}
					$sanitized[$download_key][$price_key] = 'on';
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