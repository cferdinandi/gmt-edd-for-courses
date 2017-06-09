<?php


	/**
	 * Get data from the EDD API
	 * @param  string $type The type of data to get from the EDD API
	 * @param  array  $args Any API arguments you want to add to the EDD API query
	 * @return array        The downloads
	 */
	function gmt_edd_for_courses_get_from_api( $type = 'products', $args = array() ) {
		$options = edd_for_courses_get_theme_options();
		$url = rtrim($options['url'], '/') . '/edd-api/v2/' . $type . '/';
		$url = add_query_arg(array(
			'key' => $options['public_key'],
			'token' => $options['token'],
			'number' => '-1',
		), $url);
		foreach ($args as $key => $value) {
			$url = add_query_arg($key, $value, $url);
		}
		$request = wp_remote_post( $url );
		$response = wp_remote_retrieve_body( $request );
		return json_decode( $response, true );
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

		// Variables
		$purchases = gmt_edd_for_courses_get_from_api( $type = 'sales', array('email' => $email) );
		$downloads = array();

		// Get download and price ID
		foreach($purchases['sales'] as $purchase) {
			foreach($purchase['products'] as $product) {
				$downloads[$product['id']] = ( empty( $product['price_id'] ) ? 0 : $product['price_id'] );
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
		$courses = gmt_edd_for_courses_get_courses( false );
		$purchased = array();

		// Check each course to see if the user has access
		foreach( $courses as $course ) {

			// Get the downloads that have access to this course
			$downloads = (array) get_post_meta( $course->ID, 'gmt_edd_for_courses_downloads', true );

			// Check the course against purchased downloads
			foreach( $downloads as $download_id => $prices ) {

				// If user hasn't purchased this download, skip to the next one
				if ( !array_key_exists( $download_id, $purchases ) ) continue;

				// If the allowed price index for the download doesn't match, skip to the next one
				if ( !array_key_exists( $purchases[$download_id], $prices ) ) continue;

				// Add purchased course data
				$purchased[$course->ID] = array(
					'course_id' => $course->ID,
					'download_id' => $download_id,
					'price' => $purchases[$download_id],
				);

				// If a bundle, add the bundled download to use for files
				if ( array_key_exists('bundles', $prices) ) {
					$bundle_data = explode('_', $prices['bundles']);
					$purchased[$course->ID]['bundle_id'] = $bundle_data[0];
					$purchased[$course->ID]['bundle_price'] = $bundle_data[1];
				}

			}

		}

		return $purchased;

	}



	/**
	 * Get the downloads that the user has purchased within a category
	 * @param  string $email    The user's email address
	 * @param  string $category The category to filter purchases against
	 * @return array            The downloads the user has purchased in the category
	 */
	function gmt_edd_for_courses_get_purchases_by_category( $email = null, $category = null ) {

		if ( empty( $email ) || empty( $category ) ) return;

		// Variables
		$purchases = gmt_edd_for_courses_get_user_downloads( $email );
		$filtered = array();

		// Loop through each purchase and filter out products that match the category
		foreach( $purchases as $purchase_id => $price ) {

			// Get this purchase's products
			$products = gmt_edd_for_courses_get_from_api( 'products', array('product' => $purchase_id ) );

			// Find products in the category
			foreach( $products['products'] as $product ) {

				// If there are no categories, bail
				if ( empty($product['info']['category'] ) ) continue;

				// Loop through each category
				foreach( $product['info']['category'] as $category_data ) {
					if ( $category_data['name'] === $category ) {

						// Add the product to our filtered array
						$filtered[$product['info']['id']] = array(
							'id' => $product['info']['id'],
							'title' => $product['info']['title'],
							'price' => ( array_key_exists('pricing_extended', $product) && array_key_exists('amount', $product['pricing_extended']) ? $product['pricing_extended']['amount']['index'] : 0 ),
						);

						// If files, add them
						if ( array_key_exists('files', $product) ) {
							$filtered[$product['info']['id']]['files'] = $product['files'];
						}

						// If a bundle, add the bundled download to use for files
						if ( array_key_exists('bundled_products', $product) ) {
							$bundles = array();
							foreach( $product['bundled_products'] as $bundle ) {
								$bundles[$bundle['id']] = $bundle;
							}
							$filtered[$product['info']['id']]['bundles'] = $bundles;
						}
						break;
					}
				}

			}
		}

		return $filtered;

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
		$downloads = (array) get_post_meta( $course_id, 'gmt_edd_for_courses_downloads', true );

		// Check the course against purchased downloads
		foreach( $downloads as $download_id => $prices ) {

			// If user hasn't purchased this download, skip to the next one
			if ( !array_key_exists( $download_id, $purchases ) ) continue;

			// If there's just a single price
			if ( empty( array_search( reset( $prices ), $prices ) ) ) return true;

			// If there are multiple prices
			if ( array_key_exists( $purchases[$download_id], $prices ) ) return true;

		}

		return false;

	}



	/**
	 * Get the download links that the user has access to for a course
	 * @param  number $course_id The course ID
	 * @param  string $email     The user's email address
	 * @return array             The course downloads
	 * @todo   Figure out a way to restrict files by access levels
	 */
	function gmt_edd_for_courses_get_download_links( $course_id = null, $email = null ) {

		if ( empty( $course_id ) || empty( $email ) ) return;

		// Get courses user has purchased
		$downloads = gmt_edd_for_courses_get_purchased_courses( $email );

		// If course doesn't exist, bail
		if ( !array_key_exists( $course_id, $downloads ) ) return;

		// Setup our links placeholder
		$links = array();

		// Get the links for each download
		foreach($downloads as $download) {

			// Get the product
			$no_bundle = empty($download['bundle_id']);
			$product = $no_bundle ? gmt_edd_for_courses_get_from_api( 'products', array('product' => $download['download_id'] ) ) : gmt_edd_for_courses_get_from_api( 'products', array('product' => $download['bundle_id'] ) );

			// Make sure the product has files
			if ( !is_array($product) || !array_key_exists('products', $product) || !array_key_exists('files', $product['products'][0]) ) continue;

			// Get the link for each product file
			foreach ( $product['products'][0]['files'] as $file ) {

				// Check if file is available for purchased price
				if ( array_key_exists( 'condition', $file ) && !in_array( $file['condition'], array( 'all', $download['price'] ) ) && ( !$no_bundle && !in_array( $file['condition'], array( 'all', $download['bundle_price'] ) ) ) ) continue;

				// Push file to links
				$links[] = array(
					'name' => $file['name'],
					'url' => $file['file'],
				);
			}
		}

		return $links;

	}



	/**
	 * Generate the links array
	 * @param  Number $id    The product ID
	 * @param  String $title The product title
	 * @param  Array  $files The product files
	 * @return Array         The product links array
	 */
	function gmt_edd_for_courses_generate_links_array($id = null, $title = null, $files = null) {

		if (empty($id) || empty($title) || empty($files) ) return;

		// Setup the product links
		$links = array(
			'id' => $id,
			'title' => $title,
			'links' => array(),
		);

		// Add the product links
		foreach( $files as $file_key => $file ) {
			$links['links'][$file_key]['name'] = $file['name'];
			$links['links'][$file_key]['url'] = $file['file'];
		}

		return $links;
	}



	/**
	 * Get the download links that the user has access to for purchases in a category
	 * @param  string $email     The user's email address
	 * @param  string $category  The purchase category
	 * @return array             The course downloads
	 * @todo   Figure out a way to restrict files by access levels
	 */
	function gmt_edd_for_courses_get_purchase_links_by_category( $email = null, $category = null ) {

		if ( empty( $email ) || empty( $category ) ) return;

		// Get the purchases in this category
		$purchases = gmt_edd_for_courses_get_purchases_by_category( $email, $category );

		// Setup our links placeholder
		$links = array();

		// Get the links for each purchase
		foreach( $purchases as $purchase ) {

			// If a bundle, get individual bundled products
			if ( !empty($purchase['bundles']) ) {
				foreach( $purchase['bundles'] as $bundle ) {

					// Get the bundled product
					$product = gmt_edd_for_courses_get_from_api( 'products', array('product' => $bundle['id'] ) );
					if ( empty($product['products'][0]['files']) ) continue;

					// Create the links array
					$links[] = gmt_edd_for_courses_generate_links_array($product['products'][0]['info']['id'], $product['products'][0]['info']['title'], $product['products'][0]['files']);
				}
				continue;
			}

			// If not a bundle, just create the links array
			$links[] = gmt_edd_for_courses_generate_links_array($purchase['id'], $purchase['title'], $purchase['files']);

		}

		return $links;

		// @todo verify that user has right to access the file

		// Get the links for each download
		foreach($downloads as $download) {

			// Get the product
			$no_bundle = empty($download['bundle_id']);
			$product = $no_bundle ? gmt_edd_for_courses_get_from_api( 'products', array('product' => $download['download_id'] ) ) : gmt_edd_for_courses_get_from_api( 'products', array('product' => $download['bundle_id'] ) );

			// Make sure the product has files
			if ( !is_array($product) || !array_key_exists('products', $product) || !array_key_exists('files', $product['products'][0]) ) continue;

			// Get the link for each product file
			foreach ( $product['products'][0]['files'] as $file ) {

				// Check if file is available for purchased price
				if ( array_key_exists( 'condition', $file ) && !in_array( $file['condition'], array( 'all', $download['price'] ) ) && ( !$no_bundle && !in_array( $file['condition'], array( 'all', $download['bundle_price'] ) ) ) ) continue;

				// Push file to links
				$links[] = array(
					'name' => $file['name'],
					'url' => $file['file'],
				);
			}
		}

		return $links;

	}