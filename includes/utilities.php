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
	 * Format product data
	 * @param  Array $products The products
	 * @return Array           The formatted product data
	 */
	function gmt_edd_for_courses_format_products( $products ) {

		// Make sure products exist
		if (empty($products)) return;

		// Setup formatted products array
		$formatted = array(
			'products' => array(),
			'bundles'  => array(),
		);

		// Loop through each product
		foreach ($products['products'] as $product) {

			// Setup bundles
			if (!empty($product['bundled_products'])) {
				$formatted['bundles'][$product['info']['id']] = array(
					'id' => $product['info']['id'],
					'products' => $product['bundled_products'],
				);
				continue;
			}

			// Setup product data
			$formatted['products'][$product['info']['id']] = array(
				'id'         => $product['info']['id'],
				'title'      => $product['info']['title'],
				'thumbnail'  => $product['info']['thumbnail'],
				'pricing'    => $product['pricing_extended'],
				'files'      => ( array_key_exists('files', $product) ? $product['files'] : array() ),
				'categories' => ( array_key_exists('category', $product['info']) ? $product['info']['category'] : array() ),
			);

		}

		return $formatted;

	}



	/**
	 * Get product data
	 * @return Array The formatted product data
	 */
	function gmt_edd_for_courses_get_products( $flush = false ) {

		// Get products
		$products = get_transient('gmt_edd_for_courses_products');
		if (empty($products) || !empty($flush)) {
			$products = gmt_edd_for_courses_get_from_api('products');
			set_transient('gmt_edd_for_courses_products', $products, 60 * 60);
		}

		// Format products
		$products = gmt_edd_for_courses_format_products($products);

		return $products;

	}



	/**
	 * Format purchase data
	 * @param  Array $purchases The purchases to format
	 * @return Array            The formatted purchase data
	 */
	function gmt_edd_for_courses_format_purchases( $purchases = null ) {

		// Make sure purchases provided
		if (empty($purchases)) return;

		// Setup formatted purchases array
		$formatted = array();
		$products = gmt_edd_for_courses_get_products();

		// Loop through each purchased product
		foreach ($purchases['sales'] as $purchase) {
			foreach ($purchase['products'] as $product) {

				// If it's a bundle
				if ( array_key_exists($product['id'], $products['bundles']) ) {
					foreach ($products['bundles'][$product['id']]['products'] as $bundle) {
						$formatted[$bundle['id']] = array(
							'id'    => $bundle['id'],
							'price' => $bundle['price_id'],
						);
					}
					continue;
				}

				// Otherwise
				$formatted[$product['id']] = array(
					'id'    => $product['id'],
					'price' => $product['price'],
				);
			}
		}

		return $formatted;
	}



	/**
	 * Get purchase data
	 * @param  String $email The purchasee email address.
	 * @return Array         The formatted purchase data.
	 */
	function gmt_edd_for_courses_get_purchases( $email = null, $flush = false ) {

		// Make sure an email address is provided
		if (empty($email)) return;

		// Get purchases
		$purchases = get_transient('gmt_edd_for_courses_purchases_' . md5($email));
		if (empty($purchases) || !empty($flush)) {
			$purchases = gmt_edd_for_courses_get_from_api($type = 'sales', array('email' => $email));
			set_transient('gmt_edd_for_courses_purchases_' . md5($email), $purchases, 60 * 60);
		}

		// Format purchases
		$purchases = gmt_edd_for_courses_format_purchases($purchases);

		return $purchases;

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
	 * Get the courses that the user has access to
	 * @param  string $email The user's email address
	 * @return array         The courses that the user has access to
	 */
	function gmt_edd_for_courses_get_purchased_courses( $email = null ) {

		if ( empty( $email ) ) return;

		// Variables
		$purchases = gmt_edd_for_courses_get_purchases( $email );
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
				if ( !array_key_exists( $purchases[$download_id]['price'], $prices ) ) continue;

				// Add purchased course data
				$purchased[$course->ID] = array(
					'course_id' => $course->ID,
					'download_id' => $download_id,
					'price' => $purchases[$download_id]['price'],
				);

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
		$purchases = gmt_edd_for_courses_get_purchases( $email );
		$products = gmt_edd_for_courses_get_products();
		$purchases_in_category = array();

		foreach ($purchases as $purchase) {

			// If the product doesn't exist or doesn't have a category, skip to the next one
			if ( !array_key_exists($purchase['id'], $products['products']) || !array_key_exists('categories', $products['products'][$purchase['id']]) ) continue;

			// Check if the category matches
			foreach ($products['products'][$purchase['id']]['categories'] as $purchase_category) {
				if ($purchase_category['name'] === $category) {
					$purchases_in_category[$purchase['id']] = $purchase;
					break;
				}
			}

		}

		return $purchases_in_category;

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
		$courses = gmt_edd_for_courses_get_purchased_courses( $email );
		$downloads = (array) get_post_meta( $course_id, 'gmt_edd_for_courses_downloads', true );

		// If user has purchased an included download at an included price, grant access
		if ( array_key_exists($course_id, $courses) && array_key_exists($courses[$course_id]['price'], $downloads[$courses[$course_id]['download_id']]) ) return true;

		// Otherwise, deny it
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

		// Variables
		$downloads = gmt_edd_for_courses_get_purchased_courses( $email );
		$products = gmt_edd_for_courses_get_products();
		$links = array();

		// If user doesn't have access to the course OR matching product doesn't exist OR there are no files for the product, bail
		if ( !array_key_exists($course_id, $downloads) || !array_key_exists($downloads[$course_id]['download_id'], $products['products']) || empty($products['products'][$downloads[$course_id]['download_id']]['files']) ) return;

		// Get the link for each file
		foreach ($products['products'][$downloads[$course_id]['download_id']]['files'] as $file) {

			// Make sure file access matches purchase price
			if ($file['condition'] !== 'all' && intval($file['condition']) !== intval($downloads[$course_id]['price'])) continue;

			$links[] = array(
				'name' => $file['name'],
				'url'  => $file['file'],
			);

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

		if ( empty( $email ) ) return;

		// Variables
		$purchases_in_category = ( empty($category) ? gmt_edd_for_courses_get_purchases( $email ) : gmt_edd_for_courses_get_purchases_by_category( $email, $category ) );
		$products = gmt_edd_for_courses_get_products();
		$links = array();

		// Get the links for each purchase
		foreach ($purchases_in_category as $purchase) {

			// If a matching product doesn't exist or there are no files for the product, skip to the next one
			if ( !array_key_exists($purchase['id'], $products['products']) || empty($products['products'][$purchase['id']]['files']) ) continue;

			// Setup the array
			$links[$purchase['id']] = array(
				'id'        => $products['products'][$purchase['id']]['id'],
				'title'     => $products['products'][$purchase['id']]['title'],
				'thumbnail' => $products['products'][$purchase['id']]['thumbnail'],
				'files'     => array(),
			);

			// Get the link for each file
			foreach ($products['products'][$purchase['id']]['files'] as $file) {

				// Make sure file access matches purchase price
				if ($file['condition'] !== 'all' && intval($file['condition']) !== intval($purchase['price'])) continue;

				// Add the files
				$links[$purchase['id']]['files'][] = array(
					'name' => $file['name'],
					'url'  => $file['file'],
				);

			}

		}

		return $links;

	}