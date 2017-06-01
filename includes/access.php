<?php


	/**
	 * Redirect users who don't have access to a lesson
	 */
	function gmt_edd_for_courses_redirect_users() {

		// Don't run in the Dashboard
		if ( is_admin() ) return;

		// Only run on lessons
		if ( get_post_type() !== 'gmt_lessons' ) return;

		// Get redirect page
		global $post;
		$course_id = get_post_meta( $post->ID, 'gmt_courses_course', true );
		$redirect = get_post_meta( $course_id, 'gmt_edd_for_courses_redirects', true );

		// Get redirect URL
		if ( $redirect === '0' ) {
			$url = site_url();
		} elseif ( empty( $redirect ) ) {
			return;
		} else {
			$url = get_permalink( $redirect );
		}

		// If user is logged out, redirect them
		if ( !is_user_logged_in() ) {
			wp_safe_redirect( $url );
			exit;
		}

		// If user has access to this page, bail
		$current_user = wp_get_current_user();
		if ( gmt_edd_for_courses_user_has_access( $course_id, $current_user->user_email ) ) return;

		// Redirect users without access
		wp_safe_redirect( $url );
		exit;

	}
	add_action( 'wp', 'gmt_edd_for_courses_redirect_users' );