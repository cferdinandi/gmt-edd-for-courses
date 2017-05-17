<?php

/**
 * Plugin Name: GMT EDD for Courses
 * Plugin URI: https://github.com/cferdinandi/gmt-edd-for-courses/
 * GitHub Plugin URI: https://github.com/cferdinandi/gmt-edd-for-courses/
 * Description: Adds EDD functionality to the <a href="https://github.com/cferdinandi/gmt-courses/">GMT Courses plugin</a>.
 * Version: 2.0.0
 * Author: Chris Ferdinandi
 * Author URI: http://gomakethings.com
 * Text Domain: gmt_courses
 * License: GPLv3
 */


// Load plugin files
require_once( plugin_dir_path( __FILE__ ) . 'includes/options.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/utilities.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/metabox.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/access.php' );