<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Flashcards {

	public static function render( $atts ) {
		if ( ! is_user_logged_in() ) {
			return PCIP_Prep::login_prompt( __( 'Log in to access PCI flashcards.', 'pcip-prep' ) );
		}

		wp_enqueue_style( 'pcip-prep-styles', PCIP_PREP_PLUGIN_URL . 'assets/css/quiz.css', array(), PCIP_PREP_VERSION );
		wp_enqueue_script( 'pcip-prep-flashcards', PCIP_PREP_PLUGIN_URL . 'assets/js/flashcards.js', array(), PCIP_PREP_VERSION, true );
		wp_enqueue_script( 'pcip-prep-issue-report', PCIP_PREP_PLUGIN_URL . 'assets/js/issue-report.js', array(), PCIP_PREP_VERSION, true );

		wp_localize_script( 'pcip-prep-flashcards', 'pcipFlashcardData', array(
			'restUrl' => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );

		wp_localize_script( 'pcip-prep-issue-report', 'pcipIssueData', array(
			'restUrl' => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );

		ob_start();
		include PCIP_PREP_PLUGIN_DIR . 'templates/flashcards.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/issue-report-modal.php';
		return ob_get_clean();
	}
}
