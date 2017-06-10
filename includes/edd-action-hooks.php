<?php


	/**
	 * Flush a users cached products when the buy something new
	 * @param  integer $payment_id The payment ID
	 */
	function gmt_edd_for_courses_flush_purchases_on_purchase( $payment_id ) {

		// Variables
		$payment = edd_get_payment_meta( $payment_id );
		$email = ( is_array($payment) && array_key_exists('email', $payment) ? sanitize_email( $payment['email'] ) : null );
		$options = edd_for_courses_get_theme_options();

		if (empty($email)) return;

		// Flush data
		$flush = wp_remote_request(
			rtrim( $options['wp_api_url'], '/' ) . '/wp-json/gmt-edd-for-courses/v1/users/' . $email,
			array(
				'method'    => 'PUT',
				'headers'   => array(
					'Authorization' => 'Basic ' . base64_encode( $options['wp_api_username'] . ':' . $options['wp_api_password'] ),
				),
			)
		);

		// Emit action hook
		do_action( 'gmt_edd_for_courses_flush_user_purchases_after', sanitize_email( $payment['email'] ), $flush );

	}
	add_action( 'edd_complete_purchase', 'gmt_edd_for_courses_flush_purchases_on_purchase', 20 );
	add_action( 'save_post_edd_payment', 'gmt_edd_for_courses_flush_purchases_on_purchase', 20 );



	/**
	 * Flush the product list when something is updated
	 * @return [type] [description]
	 */
	function gmt_edd_for_courses_flush_products_on_update() {

		// Get options
	    $options = edd_for_courses_get_theme_options();

	    // Flush data
	    $flush = wp_remote_request(
	    	rtrim( $options['wp_api_url'], '/' ) . '/wp-json/gmt-edd-for-courses/v1/products',
	    	array(
	    		'method'    => 'PUT',
	    		'headers'   => array(
	    			'Authorization' => 'Basic ' . base64_encode( $options['wp_api_username'] . ':' . $options['wp_api_password'] ),
	    		),
	    	)
	    );

	    // Emit action hook
	    do_action( 'gmt_edd_for_courses_flush_products_after', $flush );

	};
	add_action( 'save_post_download', 'gmt_edd_for_courses_flush_products_on_update' );