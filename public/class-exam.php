<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Exam {

	public static function render( $atts ) {
		if ( ! is_user_logged_in() ) {
			return PCIP_Prep::login_prompt( __( 'Log in to take the PCIP practice exam.', 'pcip-prep' ) );
		}

		wp_enqueue_style( 'pcip-prep-styles', PCIP_PREP_PLUGIN_URL . 'assets/css/quiz.css', array(), PCIP_PREP_VERSION );
		wp_enqueue_script( 'pcip-prep-exam-engine', PCIP_PREP_PLUGIN_URL . 'assets/js/exam-engine.js', array(), PCIP_PREP_VERSION, true );
		wp_enqueue_script( 'pcip-prep-issue-report', PCIP_PREP_PLUGIN_URL . 'assets/js/issue-report.js', array(), PCIP_PREP_VERSION, true );

		// Don't bake a question count into the page HTML — WordPress.com edge
		// caching would serve a stale value, and the $wpdb table prefix
		// doesn't match the actual data tables on this host.  Instead, the
		// JS fetches the live count from the REST API on page load.
		wp_localize_script( 'pcip-prep-exam-engine', 'pcipExamData', array(
			'restUrl'        => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'totalAvailable' => -1, // sentinel: JS must fetch live count
			'examSize'       => 75,
			'duration'       => 90,
			'passPercent'    => 75,
			'pluginVersion'  => PCIP_PREP_VERSION,
		) );

		wp_localize_script( 'pcip-prep-issue-report', 'pcipIssueData', array(
			'restUrl' => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );

		// Tell browsers and edge caches not to cache this page.
		if ( ! headers_sent() ) {
			header( 'Cache-Control: no-cache, no-store, must-revalidate' );
			header( 'Pragma: no-cache' );
		}

		ob_start();
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-splash.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-interface.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-review.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-results.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/issue-report-modal.php';
		return ob_get_clean();
	}
}
