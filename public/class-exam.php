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

		// Count total MC questions to check if exam is possible.
		$mc_count = new WP_Query( array(
			'post_type'      => 'pcip_question',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'fields'         => 'ids',
			'meta_query'     => array(
				array(
					'key'   => '_pcip_question_type',
					'value' => 'multiple_choice',
				),
			),
		) );

		wp_localize_script( 'pcip-prep-exam-engine', 'pcipExamData', array(
			'restUrl'        => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'          => wp_create_nonce( 'wp_rest' ),
			'totalAvailable' => $mc_count->found_posts,
			'examSize'       => 75,
			'duration'       => 90,
			'passPercent'    => 75,
		) );

		wp_localize_script( 'pcip-prep-issue-report', 'pcipIssueData', array(
			'restUrl' => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );

		ob_start();
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-splash.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-interface.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-review.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/exam-results.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/issue-report-modal.php';
		return ob_get_clean();
	}
}
