<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Dashboard {

	public static function render( $atts ) {
		if ( ! is_user_logged_in() ) {
			return PCIP_Prep::login_prompt( __( 'Log in to view your performance dashboard.', 'pcip-prep' ) );
		}

		wp_enqueue_style( 'pcip-prep-dashboard', PCIP_PREP_PLUGIN_URL . 'assets/css/dashboard.css', array(), PCIP_PREP_VERSION );
		wp_enqueue_style( 'pcip-prep-styles', PCIP_PREP_PLUGIN_URL . 'assets/css/quiz.css', array(), PCIP_PREP_VERSION );
		wp_enqueue_script( 'pcip-prep-dashboard', PCIP_PREP_PLUGIN_URL . 'assets/js/dashboard.js', array(), PCIP_PREP_VERSION, true );

		wp_localize_script( 'pcip-prep-dashboard', 'pcipDashboardData', array(
			'restUrl' => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );

		ob_start();
		include PCIP_PREP_PLUGIN_DIR . 'templates/dashboard.php';
		return ob_get_clean();
	}
}
