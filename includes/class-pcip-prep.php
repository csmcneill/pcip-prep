<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep {

	private static $instance = null;

	public static function instance() {
		if ( null === self::$instance ) {
			self::$instance = new self();
		}
		return self::$instance;
	}

	private function __construct() {
		$this->init_hooks();
	}

	/**
	 * Render a login prompt with a link that triggers WordPress.com SSO.
	 * Used by all shortcodes when the user isn't logged in.
	 */
	public static function login_prompt( $message = '' ) {
		if ( empty( $message ) ) {
			$message = __( 'You need to be logged in to access this content.', 'pcip-prep' );
		}

		$login_url = wp_login_url( get_permalink() );

		wp_enqueue_style( 'pcip-prep-styles', PCIP_PREP_PLUGIN_URL . 'assets/css/quiz.css', array(), PCIP_PREP_VERSION );

		return sprintf(
			'<div class="pcip-login-prompt">'
			. '<p>%s</p>'
			. '<a href="%s" class="pcip-btn pcip-btn-primary">%s</a>'
			. '</div>',
			esc_html( $message ),
			esc_url( $login_url ),
			esc_html__( 'Log in with WordPress.com', 'pcip-prep' )
		);
	}

	private function init_hooks() {
		// Core registrations.
		$post_types = new PCIP_Prep_Post_Types();
		$post_types->init();

		// Admin.
		if ( is_admin() ) {
			$admin = new PCIP_Prep_Admin();
			$admin->init();

			$meta_boxes = new PCIP_Prep_Meta_Boxes();
			$meta_boxes->init();

			$csv_admin = new PCIP_Prep_CSV_Admin();
			$csv_admin->init();
		}

		// REST API.
		$rest_api = new PCIP_Prep_REST_API();
		$rest_api->init();

		// Frontend shortcodes.
		$shortcodes = new PCIP_Prep_Shortcodes();
		$shortcodes->init();

		// Issue reporter (admin columns + frontend handling).
		$issue_reporter = new PCIP_Prep_Issue_Reporter();
		$issue_reporter->init();
	}
}
