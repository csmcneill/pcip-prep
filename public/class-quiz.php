<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Quiz {

	public static function render( $atts ) {
		if ( ! is_user_logged_in() ) {
			return PCIP_Prep::login_prompt( __( 'Log in to take PCI quizzes.', 'pcip-prep' ) );
		}

		wp_enqueue_style( 'pcip-prep-styles', PCIP_PREP_PLUGIN_URL . 'assets/css/quiz.css', array(), PCIP_PREP_VERSION );
		wp_enqueue_script( 'pcip-prep-quiz', PCIP_PREP_PLUGIN_URL . 'assets/js/quiz-engine.js', array(), PCIP_PREP_VERSION, true );
		wp_enqueue_script( 'pcip-prep-issue-report', PCIP_PREP_PLUGIN_URL . 'assets/js/issue-report.js', array(), PCIP_PREP_VERSION, true );

		// Get question counts per domain for the length selector.
		$domain_counts = self::get_domain_question_counts();

		wp_localize_script( 'pcip-prep-quiz', 'pcipPrepData', array(
			'restUrl'      => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'        => wp_create_nonce( 'wp_rest' ),
			'domainCounts' => $domain_counts,
		) );

		wp_localize_script( 'pcip-prep-issue-report', 'pcipIssueData', array(
			'restUrl' => esc_url_raw( rest_url( 'pcip-prep/v1' ) ),
			'nonce'   => wp_create_nonce( 'wp_rest' ),
		) );

		ob_start();
		include PCIP_PREP_PLUGIN_DIR . 'templates/quiz-select.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/quiz-interface.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/quiz-results.php';
		include PCIP_PREP_PLUGIN_DIR . 'templates/issue-report-modal.php';
		return ob_get_clean();
	}

	/**
	 * Count MC questions available per domain and requirement.
	 */
	private static function get_domain_question_counts() {
		$domains = get_terms( array(
			'taxonomy'   => 'pcip_domain',
			'hide_empty' => false,
		) );

		if ( is_wp_error( $domains ) ) {
			return array();
		}

		$counts = array();
		foreach ( $domains as $term ) {
			$query = new WP_Query( array(
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
				'tax_query'      => array(
					array(
						'taxonomy' => 'pcip_domain',
						'field'    => 'slug',
						'terms'    => $term->slug,
					),
				),
			) );
			$counts[ $term->slug ] = array(
				'name'  => $term->name,
				'count' => $query->found_posts,
				'parent' => $term->parent,
			);
		}

		return $counts;
	}
}
