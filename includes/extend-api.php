<?php

	function gmt_edd_for_courses_get_product_data( $product ) {
		if ( edd_has_variable_prices( $product['info']['id'] ) ) {
			foreach ( edd_get_variable_prices( $product['info']['id'] ) as $price ) {
				$product['pricing_extended'][ sanitize_key( $price['name'] ) ] = array(
					'index' => ( empty( $price['index'] ) ? 0 : $price['index'] ),
					'amount' => $price['amount'],
				);
			}
		} else {
			$product['pricing_extended']['amount'] = array(
				'index' => 0,
				'amount' => edd_get_download_price( $product['info']['id'] ),
			);
		}

		if ( edd_is_bundled_product( $product['info']['id'] ) ) {
			$bundles = edd_get_bundled_products( $product['info']['id'], ( empty( $price['index'] ) ? 0 : $price['index'] ) );
			$bundle_data = array();
			foreach( $bundles as $bundle ) {
				$bundle_id = explode('_', $bundle);
				$price_id = (array_key_exists(1, $bundle_id) ? $bundle_id[1] : 0);
				$bundle_data[] = array(
					'id' => $bundle_id[0],
					'name' => get_the_title($bundle_id[0]),
					'price_id' => $price_id,
					'price_name' => edd_get_price_option_name($bundle_id[0], $price_id),
				);
			}
			$product['bundled_products'] = $bundle_data;
		}

		return $product;
	}
	add_filter( 'edd_api_products_product_v2', 'gmt_edd_for_courses_get_product_data' );


	function gmt_edd_for_courses_get_recent_sales( $sales ) {
		foreach ( $sales['sales'] as $sales_key => $sale ) {
			foreach ( $sale['products'] as $product_key => $product ) {
				if ( edd_has_variable_prices( $product['id'] ) ) {
					foreach ( edd_get_variable_prices( $product['id'] ) as $price ) {
						if ( $price['name'] === $product['price_name'] ) {
							$sales['sales'][$sales_key]['products'][$product_key]['price_id'] = ( empty( $price['index'] ) ? 0 : $price['index'] );
							break;
						}
					}
				} else {
					$sales['sales'][$sales_key]['products'][$product_key]['price_id'] = 0;
				}
			}
		}
		return $sales;
	}
	add_filter( 'edd_api_sales', 'gmt_edd_for_courses_get_recent_sales' );