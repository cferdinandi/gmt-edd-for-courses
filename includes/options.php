<?php

/**
 * Theme Options v1.1.0
 * Adjust theme settings from the admin dashboard.
 * Find and replace `YourTheme` with your own namepspacing.
 *
 * Created by Michael Fields.
 * https://gist.github.com/mfields/4678999
 *
 * Forked by Chris Ferdinandi
 * http://gomakethings.com
 *
 * Free to use under the MIT License.
 * http://gomakethings.com/mit/
 */


	/**
	 * Theme Options Fields
	 * Each option field requires its own uniquely named function. Select options and radio buttons also require an additional uniquely named function with an array of option choices.
	 */

	function edd_for_courses_settings_field_url() {
		$options = edd_for_courses_get_theme_options();
		?>
		<input type="url" name="edd_for_courses_theme_options[url]" class="regular-text" id="edd_for_courses_url" value="<?php echo esc_attr( $options['url'] ); ?>" />
		<label class="description" for="edd_for_courses_url"><?php _e( 'The URL of the API website', 'edd_for_courses' ); ?></label>
		<?php
	}

	function edd_for_courses_settings_field_public_key() {
		$options = edd_for_courses_get_theme_options();
		?>
		<input type="text" name="edd_for_courses_theme_options[public_key]" class="regular-text" id="edd_for_courses_public_key" value="<?php echo esc_attr( $options['public_key'] ); ?>" />
		<label class="description" for="edd_for_courses_public_key"><?php _e( 'Public API key', 'edd_for_courses' ); ?></label>
		<?php
	}

	function edd_for_courses_settings_field_token() {
		$options = edd_for_courses_get_theme_options();
		?>
		<input type="text" name="edd_for_courses_theme_options[token]" class="regular-text" id="edd_for_courses_token" value="<?php echo esc_attr( $options['token'] ); ?>" />
		<label class="description" for="edd_for_courses_token"><?php _e( 'Token', 'edd_for_courses' ); ?></label>
		<?php
	}

	function edd_for_courses_settings_field_wp_api_url() {
		$options = edd_for_courses_get_theme_options();
		?>
		<input type="url" name="edd_for_courses_theme_options[wp_api_url]" class="regular-text" id="edd_for_courses_wp_api_url" value="<?php echo esc_attr( $options['wp_api_url'] ); ?>" />
		<label class="description" for="edd_for_courses_wp_api_url"><?php _e( 'The URL of the course website', 'edd_for_courses' ); ?></label>
		<?php
	}

	function edd_for_courses_settings_field_wp_api_username() {
		$options = edd_for_courses_get_theme_options();
		?>
		<input type="text" name="edd_for_courses_theme_options[wp_api_username]" class="regular-text" id="edd_for_courses_wp_api_username" value="<?php echo esc_attr( $options['wp_api_username'] ); ?>" />
		<label class="description" for="edd_for_courses_wp_api_username"><?php _e( 'WP REST API Username', 'edd_for_courses' ); ?></label>
		<?php
	}

	function edd_for_courses_settings_field_wp_api_password() {
		$options = edd_for_courses_get_theme_options();
		?>
		<input type="text" name="edd_for_courses_theme_options[wp_api_password]" class="regular-text" id="edd_for_courses_wp_api_password" value="<?php echo esc_attr( $options['wp_api_password'] ); ?>" />
		<label class="description" for="edd_for_courses_wp_api_password"><?php _e( 'WP REST API Password (note: this is NOT your normal password)', 'edd_for_courses' ); ?></label>
		<?php
	}



	/**
	 * Theme Option Defaults & Sanitization
	 * Each option field requires a default value under edd_for_courses_get_theme_options(), and an if statement under edd_for_courses_theme_options_validate();
	 */

	// Get the current options from the database.
	// If none are specified, use these defaults.
	function edd_for_courses_get_theme_options() {
		$saved = (array) get_option( 'edd_for_courses_theme_options' );
		$defaults = array(
			'url' => '',
			'public_key' => '',
			'token' => '',
			'wp_api_url' => '',
			'wp_api_username' => '',
			'wp_api_password' => '',
		);

		$defaults = apply_filters( 'edd_for_courses_default_theme_options', $defaults );

		$options = wp_parse_args( $saved, $defaults );
		$options = array_intersect_key( $options, $defaults );

		return $options;
	}

	// Sanitize and validate updated theme options
	function edd_for_courses_theme_options_validate( $input ) {
		$output = array();

		if ( isset( $input['url'] ) && ! empty( $input['url'] ) )
			$output['url'] = wp_filter_nohtml_kses( $input['url'] );

		if ( isset( $input['public_key'] ) && ! empty( $input['public_key'] ) )
			$output['public_key'] = wp_filter_nohtml_kses( $input['public_key'] );

		if ( isset( $input['token'] ) && ! empty( $input['token'] ) )
			$output['token'] = wp_filter_nohtml_kses( $input['token'] );

		if ( isset( $input['wp_api_url'] ) && ! empty( $input['wp_api_url'] ) )
			$output['wp_api_url'] = wp_filter_nohtml_kses( $input['wp_api_url'] );

		if ( isset( $input['wp_api_username'] ) && ! empty( $input['wp_api_username'] ) )
			$output['wp_api_username'] = wp_filter_nohtml_kses( $input['wp_api_username'] );

		if ( isset( $input['wp_api_password'] ) && ! empty( $input['wp_api_password'] ) )
			$output['wp_api_password'] = wp_filter_nohtml_kses( $input['wp_api_password'] );

		return apply_filters( 'edd_for_courses_theme_options_validate', $output, $input );
	}



	/**
	 * Theme Options Menu
	 * Each option field requires its own add_settings_field function.
	 */

	// Create theme options menu
	// The content that's rendered on the menu page.
	function edd_for_courses_theme_options_render_page() {
		?>
		<div class="wrap">
			<h2><?php _e( 'EDD for Courses Settings', 'edd_for_courses' ); ?></h2>
			<?php settings_errors(); ?>

			<form method="post" action="options.php">
				<?php
					settings_fields( 'edd_for_courses_options' );
					do_settings_sections( 'edd_for_courses_options' );
					submit_button();
				?>
			</form>
		</div>
		<?php
	}

	// Register the theme options page and its fields
	function edd_for_courses_theme_options_init() {

		// Register a setting and its sanitization callback
		// register_setting( $option_group, $option_name, $sanitize_callback );
		// $option_group - A settings group name.
		// $option_name - The name of an option to sanitize and save.
		// $sanitize_callback - A callback function that sanitizes the option's value.
		register_setting( 'edd_for_courses_options', 'edd_for_courses_theme_options', 'edd_for_courses_theme_options_validate' );


		// Register our settings field group
		// add_settings_section( $id, $title, $callback, $page );
		// $id - Unique identifier for the settings section
		// $title - Section title
		// $callback - // Section callback (we don't want anything)
		// $page - // Menu slug, used to uniquely identify the page. See edd_for_courses_theme_options_add_page().
		add_settings_section( 'edd_api', __('EDD API', 'edd_for_courses'), '__return_false', 'edd_for_courses_options' );
		add_settings_section( 'wp_rest_api', __('WP REST API', 'edd_for_courses'), '__return_false', 'edd_for_courses_options' );


		// Register our individual settings fields
		// add_settings_field( $id, $title, $callback, $page, $section );
		// $id - Unique identifier for the field.
		// $title - Setting field title.
		// $callback - Function that creates the field (from the Theme Option Fields section).
		// $page - The menu page on which to display this field.
		// $section - The section of the settings page in which to show the field.
		add_settings_field( 'url', __( 'URL', 'edd_for_courses' ), 'edd_for_courses_settings_field_url', 'edd_for_courses_options', 'edd_api' );
		add_settings_field( 'public_key', __( 'Public Key', 'edd_for_courses' ), 'edd_for_courses_settings_field_public_key', 'edd_for_courses_options', 'edd_api' );
		add_settings_field( 'token', __( 'Token', 'edd_for_courses' ), 'edd_for_courses_settings_field_token', 'edd_for_courses_options', 'edd_api' );

		add_settings_field( 'wp_api_url', __( 'URL', 'edd_for_courses' ), 'edd_for_courses_settings_field_wp_api_url', 'edd_for_courses_options', 'wp_rest_api' );
		add_settings_field( 'wp_api_username', __( 'Username', 'edd_for_courses' ), 'edd_for_courses_settings_field_wp_api_username', 'edd_for_courses_options', 'wp_rest_api' );
		add_settings_field( 'wp_api_password', __( 'Password', 'edd_for_courses' ), 'edd_for_courses_settings_field_wp_api_password', 'edd_for_courses_options', 'wp_rest_api' );

	}
	add_action( 'admin_init', 'edd_for_courses_theme_options_init' );

	// Add the theme options page to the admin menu
	// Use add_theme_page() to add under Appearance tab (default).
	// Use add_menu_page() to add as it's own tab.
	// Use add_submenu_page() to add to another tab.
	function edd_for_courses_theme_options_add_page() {

		// add_theme_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_menu_page( $page_title, $menu_title, $capability, $menu_slug, $function );
		// add_submenu_page( $parent_slug, $page_title, $menu_title, $capability, $menu_slug, $function );
		// $page_title - Name of page
		// $menu_title - Label in menu
		// $capability - Capability required
		// $menu_slug - Used to uniquely identify the page
		// $function - Function that renders the options page
		// $theme_page = add_theme_page( __( 'Theme Options', 'edd_for_courses' ), __( 'Theme Options', 'edd_for_courses' ), 'edit_theme_options', 'theme_options', 'edd_for_courses_theme_options_render_page' );

		// $theme_page = add_menu_page( __( 'Theme Options', 'edd_for_courses' ), __( 'Theme Options', 'edd_for_courses' ), 'edit_theme_options', 'theme_options', 'edd_for_courses_theme_options_render_page' );
		$theme_page = add_submenu_page( 'options-general.php', __( 'Courses for EDD', 'edd_for_courses' ), __( 'Courses for EDD', 'edd_for_courses' ), 'edit_theme_options', 'edd_for_courses_options', 'edd_for_courses_theme_options_render_page' );
	}
	add_action( 'admin_menu', 'edd_for_courses_theme_options_add_page' );



	// Restrict access to the theme options page to admins
	function edd_for_courses_option_page_capability( $capability ) {
		return 'edit_theme_options';
	}
	add_filter( 'option_page_capability_edd_for_courses_options', 'edd_for_courses_option_page_capability' );
