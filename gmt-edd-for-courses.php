<?php

/**
 * Plugin Name: GMT EDD for Courses
 * Plugin URI: https://github.com/cferdinandi/gmt-edd-for-courses/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-edd-for-courses/
 * Description: Adds EDD functionality to the <a href="https://github.com/cferdinandi/gmt-courses/">GMT Courses plugin</a>.
 * Version: 3.2.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * Text Domain: gmt_courses
 * License: GPLv3
 */

// Define constants
define( 'GMT_EDD_FOR_COURSES_VERSION', '3.2.0' );


// Load plugin files
require_once( plugin_dir_path( __FILE__ ) . 'includes/options.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/utilities.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/access.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/extend-api.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/extend-wp-rest-api.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/edd-action-hooks.php' );


/**
 * Check the plugin version and make updates if needed
 */
function gmt_edd_for_courses_check_version() {

	// Get plugin data
	$old_version = get_site_option( 'gmt_edd_for_courses_version' );

	// Update plugin to current version number
	if ( empty( $old_version ) || version_compare( $old_version, GMT_EDD_FOR_COURSES_VERSION, '<' ) ) {
		update_site_option( 'gmt_edd_for_courses_version', GMT_EDD_FOR_COURSES_VERSION );
	}

}
add_action( 'plugins_loaded', 'gmt_edd_for_courses_check_version' );