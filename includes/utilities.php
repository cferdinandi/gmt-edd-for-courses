<?php


	/**
	 * Get all available downloads
	 * @param  boolean $any If true, get all downloads, including unpublished
	 * @return array        The downloads
	 */
	function gmt_edd_for_courses_get_downloads( $any = true ) {
		return get_posts(array(
			'posts_per_page'   => -1,
			'post_type'        => 'download',
			'post_status'      => ( $any ? 'any' : 'publish' ),
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
		));
	}



	/**
	 * Get all available courses
	 * @param  boolean $any If true, get all courses, including unpublished
	 * @return array        The courses
	 */
	function gmt_edd_for_courses_get_courses( $any = true ) {
		return get_posts(array(
			'posts_per_page'   => -1,
			'post_type'        => 'gmt_courses',
			'post_status'      => ( $any ? 'any' : 'publish' ),
			'orderby'          => 'menu_order',
			'order'            => 'ASC',
		));
	}



	/**
	 * Get the downloads a user has purchased
	 * @param  string $email The user's email address
	 * @return array         The user's downloads
	 */
	function gmt_edd_for_courses_get_user_downloads( $email = null ) {

		if ( empty( $email ) ) return;

		// Get the customer
		$customer = new EDD_Customer( $email );
		if ( $customer->id === 0 ) return;

		// Get customer payment IDs
		$payment_ids = explode( ',', $customer->payment_ids );

		// Get the downloads
		$downloads = array();
		foreach( $payment_ids as $payment_id ) {

			// Get the payment
			$payment = edd_get_payment_meta( $payment_id );

			// Create an array of downloads for this payment
			foreach( $payment['downloads'] as $download ) {
				$downloads[$download['id']] = array(
					'id' => $download['id'],
					'price' => ( array_key_exists( 'price_id', $download['options'] ) ? $download['options']['price_id'] : 0 ),
					'payment' => $payment_id,
				);
			}

		}

		return $downloads;

	}



	/**
	 * Get the courses that the user has access to
	 * @param  string $email The user's email address
	 * @return array         The courses that the user has access to
	 */
	function gmt_edd_for_courses_get_purchased_courses( $email = null ) {

		if ( empty( $email ) ) return;

		// Variables
		$purchases = gmt_edd_for_courses_get_user_downloads( $email );
		$courses = gmt_edd_for_courses_get_courses();
		$purchased = array();

		// Check each course to see if the user has access
		foreach( $courses as $course ) {

			// Get the downloads that have access to this course
			$downloads = (array) get_post_meta( $course->ID, 'gmt_edd_for_courses_downloads', true );

			// Check the course against purchased downloads
			foreach( $downloads as $download_key => $download  ) {

				// If access is based on tiered pricing
				if ( is_array( $download ) ) {
					if ( array_key_exists( $purchases[$download_key]['price'], $download ) ) {
						$purchased[$course->ID] = array(
							'id' => $course->ID,
							'download' => $download_key,
							'payment' => $purchases[$download_key]['payment'],
							'price' => $purchases[$download_key]['price'],
						);
					}
					continue;
				}

				// If access is based on single price
				if ( is_array( $purchases ) && array_key_exists( $download_key, $purchases ) ) {
					$purchased[$course->ID] = array(
						'id' => $course->ID,
						'download' => $download_key,
						'payment' => $purchases[$download_key]['payment'],
						'price' => $purchases[$download_key]['price'],
					);
				}

			}

		}

		return $purchased;

	}



	/**
	 * Check if user can access a course
	 * @param  number $course_id The course ID
	 * @param  string $email     The user's email address
	 * @return boolean           If true, the user can access the course
	 */
	function gmt_edd_for_courses_user_has_access( $course_id = null, $email = null ) {

		if ( empty( $course_id ) || empty( $email ) ) return;

		// Variables
		$purchases = gmt_edd_for_courses_get_user_downloads( $email );
		$course = get_post( $course_id );
		$downloads = (array) get_post_meta( $course_id, 'gmt_edd_for_courses_downloads', true );

		// Check the course against purchased downloads
		foreach( $downloads as $download_key => $download  ) {

			// If access is based on tiered pricing
			if ( is_array( $download ) ) {
				if ( array_key_exists( $purchases[$download_key]['price'], $download ) ) return true;
				continue;
			}

			// If access is based on single price
			if ( array_key_exists( $download_key, $purchases ) ) return true;

		}

		return false;

	}



	/**
	 * Get the download links that the user has access to for a course
	 * @param  number $course_id The course ID
	 * @param  string $email     The user's email address
	 * @return array             The course downloads
	 */
	function gmt_edd_for_courses_get_download_links( $course_id = null, $email = null ) {

		if ( empty( $course_id ) || empty( $email ) ) return;

		// @todo

		// Get courses user has purchased
		$downloads = gmt_edd_for_courses_get_purchased_courses( $email );

		// If course doesn't exist, bail
		if ( !array_key_exists( $course_id, $downloads ) ) return;

		// Variables
		$files = edd_get_download_files( $downloads[$course_id]['download'], $downloads[$course_id]['price'] );
		$key = edd_get_payment_key( $downloads[$course_id]['payment'] );
		$links = array();

		// Get the download URLs
		foreach( $files as $file_key => $file ) {
			$links[] = array(
				'name' => $file['name'],
				'file' => $file['file'],
				'url' => edd_get_download_file_url( $key, $email, $file_key, $downloads[$course_id]['download'], $downloads[$course_id]['price'] ),
			);
		}

		return $links;

	}



	/**
	 * Create dynamic "Buy Now" links for courses
	 * @param  array $atts The shortcode arguments
	 * @return string      The link
	 */
	function gmt_edd_for_courses_dynamic_buy_now_links( $atts ) {

		// Get shortcode atts
		$link = shortcode_atts( array(
			'id' => null,
			'checkout' => false,
			'gateway' => false,
			'price' => null,
			'discount' => null,
			'class' => '',
			'buy' => 'Buy Now',
			'owned' => 'You already own this',
		), $atts );

		// Make sure an ID is provided
		if ( empty( $link['id'] ) ) return;

		// Create the URL
		global $post;
		$base = $link['checkout'] ? edd_get_checkout_uri() : get_permalink( $post->ID );
		$action = $link['gateway'] ? '?edd_action=straight_to_gateway' : '?edd_action=add_to_cart';
		$price = is_null( $link['price'] ) ? '' : '&edd_options[price]=' . $link['price'];
		$discount = is_null( $link['discount'] ) ? '' : '&discount=' . $link['discount'];
		$url = $base . $action . '&download=' . $link['id'] . $price . $discount;

		// If user is not logged in, show the buy now link
		if ( !is_user_logged_in() ) {
			return '<a class="' . $link['class'] . '" href="' . $url . '">' . $link['buy'] . '</a>';
		}

		// Get courses that the user has purchased
		$current_user = wp_get_current_user();
		$downloads = (array) gmt_edd_for_courses_get_user_downloads( $current_user->user_email );

		// If the user already owns the download, disable the buttton
		if ( array_key_exists( $link['id'], $downloads ) ) {
			return '<span class="' . $link['class'] . '" href="#" disabled>' . $link['owned'] . '</span>';
		}

		return '<a class="' . $link['class'] . '" href="' . $url . '">' . $link['buy'] . '</a>';

	}
	add_shortcode( 'edd_for_courses_buy_now', 'gmt_edd_for_courses_dynamic_buy_now_links' );