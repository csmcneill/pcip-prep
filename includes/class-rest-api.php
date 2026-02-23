<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_REST_API {

	const NAMESPACE = 'pcip-prep/v1';

	public function init() {
		add_action( 'rest_api_init', array( $this, 'register_routes' ) );
	}

	public function register_routes() {
		// Fetch questions (flashcards or MC).
		register_rest_route( self::NAMESPACE, '/questions', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_questions' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
			'args'                => array(
				'type'        => array( 'required' => true, 'type' => 'string' ),
				'domain'      => array( 'type' => 'string' ),
				'requirement' => array( 'type' => 'string' ),
			),
		) );

		// Start a domain quiz.
		register_rest_route( self::NAMESPACE, '/quiz/start', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'start_quiz' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Answer a domain quiz question (immediate feedback).
		register_rest_route( self::NAMESPACE, '/quiz/answer', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'answer_quiz_question' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Submit a completed domain quiz.
		register_rest_route( self::NAMESPACE, '/quiz/submit', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'submit_quiz' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Check available question count (public, cache-safe).
		register_rest_route( self::NAMESPACE, '/exam/available', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_exam_available' ),
			'permission_callback' => '__return_true',
		) );

		// Start a full PCIP prep exam.
		register_rest_route( self::NAMESPACE, '/exam/start', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'start_exam' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Autosave exam state.
		register_rest_route( self::NAMESPACE, '/exam/autosave', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'autosave_exam' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Submit completed exam.
		register_rest_route( self::NAMESPACE, '/exam/submit', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'submit_exam' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Dashboard stats.
		register_rest_route( self::NAMESPACE, '/dashboard', array(
			'methods'             => 'GET',
			'callback'            => array( $this, 'get_dashboard' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );

		// Report an issue.
		register_rest_route( self::NAMESPACE, '/report-issue', array(
			'methods'             => 'POST',
			'callback'            => array( $this, 'report_issue' ),
			'permission_callback' => array( $this, 'check_logged_in' ),
		) );
	}

	public function check_logged_in() {
		return is_user_logged_in();
	}

	// ------------------------------------------------------------------
	// Questions
	// ------------------------------------------------------------------

	public function get_questions( $request ) {
		$type        = $request->get_param( 'type' );
		$domain      = $request->get_param( 'domain' );
		$requirement = $request->get_param( 'requirement' );

		self::swap_tables();

		$args = array(
			'post_type'        => 'pcip_question',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'suppress_filters' => true,
			'meta_query'       => array(
				array(
					'key'   => '_pcip_question_type',
					'value' => $type,
				),
			),
		);

		if ( $domain || $requirement ) {
			$tax_terms = array();
			if ( $requirement ) {
				$tax_terms[] = $requirement;
			} elseif ( $domain ) {
				$tax_terms[] = $domain;
			}
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'pcip_domain',
					'field'    => 'slug',
					'terms'    => $tax_terms,
				),
			);
		}

		$query     = new WP_Query( $args );
		$questions = array();

		foreach ( $query->posts as $post ) {
			$q = array(
				'id'   => $post->ID,
				'text' => get_post_meta( $post->ID, '_pcip_question_text', true ),
			);

			if ( 'flashcard' === $type ) {
				$q['answer']    = get_post_meta( $post->ID, '_pcip_answer', true );
				$q['reference'] = get_post_meta( $post->ID, '_pcip_reference', true );
			}

			$questions[] = $q;
		}

		self::restore_tables();

		// Shuffle for flashcards.
		shuffle( $questions );

		return rest_ensure_response( $questions );
	}

	// ------------------------------------------------------------------
	// Domain quiz
	// ------------------------------------------------------------------

	public function start_quiz( $request ) {
		$domain      = sanitize_text_field( $request->get_param( 'domain' ) );
		$requirement = $request->get_param( 'requirement' ) ? sanitize_text_field( $request->get_param( 'requirement' ) ) : null;
		$count       = absint( $request->get_param( 'count' ) );
		$user_id     = get_current_user_id();

		self::swap_tables();

		// Build query.
		$tax_term = $requirement ?: $domain;
		$args     = array(
			'post_type'        => 'pcip_question',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'suppress_filters' => true,
			'meta_query'       => array(
				array(
					'key'   => '_pcip_question_type',
					'value' => 'multiple_choice',
				),
			),
			'tax_query'        => array(
				array(
					'taxonomy' => 'pcip_domain',
					'field'    => 'slug',
					'terms'    => $tax_term,
				),
			),
		);

		$query = new WP_Query( $args );
		$posts = $query->posts;

		if ( empty( $posts ) ) {
			self::restore_tables();
			return new WP_Error( 'no_questions', 'No questions found for this domain.', array( 'status' => 404 ) );
		}

		// Shuffle and limit.
		shuffle( $posts );
		if ( $count > 0 && $count < count( $posts ) ) {
			$posts = array_slice( $posts, 0, $count );
		}

		$session_id = wp_generate_uuid4();
		$questions  = array();
		$answer_key = array();

		foreach ( $posts as $index => $post ) {
			$prepared    = self::prepare_mc_question( $post, $index + 1 );
			$questions[] = $prepared['client'];
			$answer_key[ $index + 1 ] = $prepared['server'];
		}

		self::restore_tables();

		// Store session in user meta.
		$session = array(
			'session_id' => $session_id,
			'type'       => 'domain',
			'domain'     => $domain,
			'requirement' => $requirement,
			'user_id'    => $user_id,
			'started_at' => time(),
			'answer_key' => $answer_key,
			'results'    => array(),
		);
		update_user_meta( $user_id, '_pcip_active_session', $session );

		return rest_ensure_response( array(
			'session_id' => $session_id,
			'total'      => count( $questions ),
			'questions'  => $questions,
		) );
	}

	public function answer_quiz_question( $request ) {
		$user_id         = get_current_user_id();
		$session_id      = sanitize_text_field( $request->get_param( 'session_id' ) );
		$question_number = absint( $request->get_param( 'question_number' ) );
		$selected_key    = sanitize_text_field( $request->get_param( 'selected_key' ) );

		$session = get_user_meta( $user_id, '_pcip_active_session', true );
		if ( ! $session || $session['session_id'] !== $session_id ) {
			return new WP_Error( 'invalid_session', 'Quiz session not found.', array( 'status' => 404 ) );
		}

		$key_data = $session['answer_key'][ $question_number ] ?? null;
		if ( ! $key_data ) {
			return new WP_Error( 'invalid_question', 'Question not found in session.', array( 'status' => 404 ) );
		}

		$is_correct  = ( $selected_key === $key_data['correct_key'] );
		$correct_key = $key_data['correct_key'];

		// Record the result.
		$session['results'][ $question_number ] = array(
			'selected'   => $selected_key,
			'is_correct' => $is_correct,
		);
		update_user_meta( $user_id, '_pcip_active_session', $session );

		// Save to results table.
		PCIP_Prep_Database::record_result( array(
			'user_id'     => $user_id,
			'session_id'  => $session_id,
			'quiz_type'   => 'domain',
			'question_id' => $key_data['question_id'],
			'domain'      => $key_data['domain'],
			'requirement' => $key_data['requirement'],
			'is_correct'  => $is_correct ? 1 : 0,
		) );

		return rest_ensure_response( array(
			'is_correct'  => $is_correct,
			'correct_key' => $correct_key,
			'explanation' => $key_data['explanation'],
		) );
	}

	public function submit_quiz( $request ) {
		$user_id    = get_current_user_id();
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );

		$session = get_user_meta( $user_id, '_pcip_active_session', true );
		if ( ! $session || $session['session_id'] !== $session_id ) {
			return new WP_Error( 'invalid_session', 'Quiz session not found.', array( 'status' => 404 ) );
		}

		$total   = count( $session['answer_key'] );
		$correct = 0;
		foreach ( $session['results'] as $r ) {
			if ( $r['is_correct'] ) {
				$correct++;
			}
		}

		$score_percent = $total > 0 ? round( ( $correct / $total ) * 100, 1 ) : 0;
		$time_spent    = time() - $session['started_at'];

		PCIP_Prep_Database::record_session( array(
			'session_id'       => $session_id,
			'user_id'          => $user_id,
			'quiz_type'        => 'domain',
			'domain'           => $session['domain'],
			'total_questions'  => $total,
			'correct_answers'  => $correct,
			'score_percent'    => $score_percent,
			'time_spent_seconds' => $time_spent,
		) );

		// Clear session.
		delete_user_meta( $user_id, '_pcip_active_session' );

		return rest_ensure_response( array(
			'session_id'    => $session_id,
			'total'         => $total,
			'correct'       => $correct,
			'score_percent' => $score_percent,
			'time_spent'    => $time_spent,
		) );
	}

	// ------------------------------------------------------------------
	// Full PCIP exam
	// ------------------------------------------------------------------

	public function get_exam_available() {
		global $wpdb;

		// Use the detected prefix for a direct SQL count — bypasses all
		// REST filters and mu-plugin interference.
		$prefix = self::detect_prefix();
		$posts_table = $prefix . 'posts';
		$meta_table  = $prefix . 'postmeta';

		$count = (int) $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(DISTINCT p.ID)
			 FROM `{$posts_table}` p
			 INNER JOIN `{$meta_table}` pm ON p.ID = pm.post_id
			 WHERE p.post_type = %s
			   AND p.post_status = %s
			   AND pm.meta_key = %s
			   AND pm.meta_value = %s",
			'pcip_question',
			'publish',
			'_pcip_question_type',
			'multiple_choice'
		) );

		return rest_ensure_response( array(
			'available'      => $count,
			'prefix_used'    => $prefix,
			'plugin_version' => PCIP_PREP_VERSION,
		) );
	}

	public function start_exam( $request ) {
		$user_id = get_current_user_id();

		// Check for existing active exam.
		$existing = get_user_meta( $user_id, '_pcip_active_exam', true );
		if ( $existing && 'exam' === ( $existing['type'] ?? '' ) ) {
			$force = $request->get_param( 'force' );
			if ( ! $force ) {
				return rest_ensure_response( array(
					'has_active_exam' => true,
					'session_id'      => $existing['session_id'],
					'started_at'      => $existing['started_at'],
					'message'         => 'You have an active exam in progress. Resume or start a new one.',
				) );
			}
		}

		// Get all MC questions (with table prefix fix).
		self::swap_tables();

		$args = array(
			'post_type'        => 'pcip_question',
			'post_status'      => 'publish',
			'posts_per_page'   => -1,
			'suppress_filters' => true,
			'meta_query'       => array(
				array(
					'key'   => '_pcip_question_type',
					'value' => 'multiple_choice',
				),
			),
		);

		$query = new WP_Query( $args );
		$posts = $query->posts;

		$exam_size = 75;
		if ( count( $posts ) < $exam_size ) {
			self::restore_tables();
			return new WP_Error(
				'insufficient_questions',
				sprintf( 'Need at least %d questions for a full exam. Only %d available.', $exam_size, count( $posts ) ),
				array( 'status' => 400 )
			);
		}

		shuffle( $posts );
		$posts = array_slice( $posts, 0, $exam_size );

		$session_id = wp_generate_uuid4();
		$questions  = array();
		$answer_key = array();

		foreach ( $posts as $index => $post ) {
			$prepared    = self::prepare_mc_question( $post, $index + 1 );
			$questions[] = $prepared['client'];
			$answer_key[ $index + 1 ] = $prepared['server'];
		}

		self::restore_tables();

		$session = array(
			'session_id' => $session_id,
			'type'       => 'exam',
			'user_id'    => $user_id,
			'started_at' => time(),
			'answer_key' => $answer_key,
		);
		update_user_meta( $user_id, '_pcip_active_exam', $session );

		return rest_ensure_response( array(
			'session_id' => $session_id,
			'total'      => count( $questions ),
			'questions'  => $questions,
			'duration'   => 90, // minutes
		) );
	}

	public function autosave_exam( $request ) {
		$user_id    = get_current_user_id();
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );
		$answers    = $request->get_param( 'answers' ) ?: array();
		$flags      = $request->get_param( 'flags' ) ?: array();

		$session = get_user_meta( $user_id, '_pcip_active_exam', true );
		if ( ! $session || $session['session_id'] !== $session_id ) {
			return new WP_Error( 'invalid_session', 'Exam session not found.', array( 'status' => 404 ) );
		}

		$session['saved_answers'] = $answers;
		$session['saved_flags']   = $flags;
		$session['last_saved']    = time();
		update_user_meta( $user_id, '_pcip_active_exam', $session );

		return rest_ensure_response( array( 'saved_at' => $session['last_saved'] ) );
	}

	public function submit_exam( $request ) {
		$user_id    = get_current_user_id();
		$session_id = sanitize_text_field( $request->get_param( 'session_id' ) );
		$answers    = $request->get_param( 'answers' ) ?: array();

		$session = get_user_meta( $user_id, '_pcip_active_exam', true );
		if ( ! $session || $session['session_id'] !== $session_id ) {
			return new WP_Error( 'invalid_session', 'Exam session not found.', array( 'status' => 404 ) );
		}

		$answer_key = $session['answer_key'];
		$total      = count( $answer_key );
		$correct    = 0;
		$details    = array();

		foreach ( $answer_key as $q_num => $key_data ) {
			$selected   = isset( $answers[ $q_num ] ) ? sanitize_text_field( $answers[ $q_num ] ) : '';
			$is_correct = ( $selected === $key_data['correct_key'] );

			if ( $is_correct ) {
				$correct++;
			}

			// Record individual result.
			PCIP_Prep_Database::record_result( array(
				'user_id'     => $user_id,
				'session_id'  => $session_id,
				'quiz_type'   => 'exam',
				'question_id' => $key_data['question_id'],
				'domain'      => $key_data['domain'],
				'requirement' => $key_data['requirement'],
				'is_correct'  => $is_correct ? 1 : 0,
			) );

			$details[ $q_num ] = array(
				'question_id'  => $key_data['question_id'],
				'question_text' => $key_data['question_text'],
				'selected_key' => $selected,
				'correct_key'  => $key_data['correct_key'],
				'is_correct'   => $is_correct,
				'explanation'  => $key_data['explanation'],
				'options'      => $key_data['options'],
			);
		}

		$score_percent = $total > 0 ? round( ( $correct / $total ) * 100, 1 ) : 0;
		$passed        = $score_percent >= 75;
		$time_spent    = time() - $session['started_at'];

		PCIP_Prep_Database::record_session( array(
			'session_id'       => $session_id,
			'user_id'          => $user_id,
			'quiz_type'        => 'exam',
			'total_questions'  => $total,
			'correct_answers'  => $correct,
			'score_percent'    => $score_percent,
			'passed'           => $passed ? 1 : 0,
			'time_spent_seconds' => $time_spent,
		) );

		// Clear active exam.
		delete_user_meta( $user_id, '_pcip_active_exam' );

		// Get breakdowns.
		$domain_breakdown      = PCIP_Prep_Database::get_session_domain_breakdown( $session_id );
		$requirement_breakdown = PCIP_Prep_Database::get_session_requirement_breakdown( $session_id );

		return rest_ensure_response( array(
			'session_id'             => $session_id,
			'total'                  => $total,
			'correct'                => $correct,
			'score_percent'          => $score_percent,
			'passed'                 => $passed,
			'time_spent'             => $time_spent,
			'domain_breakdown'       => $domain_breakdown,
			'requirement_breakdown'  => $requirement_breakdown,
			'details'                => $details,
		) );
	}

	// ------------------------------------------------------------------
	// Dashboard
	// ------------------------------------------------------------------

	public function get_dashboard( $request ) {
		$user_id = get_current_user_id();

		return rest_ensure_response( array(
			'stats'             => PCIP_Prep_Database::get_user_stats( $user_id ),
			'domain_stats'      => PCIP_Prep_Database::get_user_domain_stats( $user_id ),
			'requirement_stats' => PCIP_Prep_Database::get_user_requirement_stats( $user_id ),
			'exam_history'      => PCIP_Prep_Database::get_user_sessions( $user_id, 'exam', 20 ),
			'quiz_history'      => PCIP_Prep_Database::get_user_sessions( $user_id, 'domain', 20 ),
		) );
	}

	// ------------------------------------------------------------------
	// Issue reporting
	// ------------------------------------------------------------------

	public function report_issue( $request ) {
		$user        = wp_get_current_user();
		$question_id = absint( $request->get_param( 'question_id' ) );
		$description = sanitize_textarea_field( $request->get_param( 'description' ) );
		$suggestion  = sanitize_textarea_field( $request->get_param( 'suggestion' ) );

		if ( ! $question_id || ! $description ) {
			return new WP_Error( 'missing_data', 'Question ID and description are required.', array( 'status' => 400 ) );
		}

		$question_title = get_the_title( $question_id );
		$post_id = wp_insert_post( array(
			'post_type'   => 'pcip_issue_report',
			'post_status' => 'publish',
			'post_title'  => sprintf( 'Issue: %s', $question_title ),
		) );

		if ( is_wp_error( $post_id ) ) {
			return new WP_Error( 'create_failed', 'Could not create issue report.', array( 'status' => 500 ) );
		}

		update_post_meta( $post_id, '_pcip_reported_question_id', $question_id );
		update_post_meta( $post_id, '_pcip_reporter_email', $user->user_email );
		update_post_meta( $post_id, '_pcip_reporter_user_id', $user->ID );
		update_post_meta( $post_id, '_pcip_issue_description', $description );
		update_post_meta( $post_id, '_pcip_remediation_suggestion', $suggestion );
		update_post_meta( $post_id, '_pcip_report_status', 'open' );

		return rest_ensure_response( array(
			'success'   => true,
			'report_id' => $post_id,
		) );
	}

	// ------------------------------------------------------------------
	// Table prefix fix (WordPress.com)
	// ------------------------------------------------------------------

	/**
	 * On WordPress.com hosted sites the $wpdb->prefix visible in
	 * REST / frontend contexts can differ from the prefix that actually
	 * holds plugin data.  detect_prefix() resolves the correct one by
	 * trying candidates in order and verifying each with a COUNT query.
	 *
	 * swap_tables() / restore_tables() temporarily redirect $wpdb so
	 * that WP_Query, get_post_meta(), wp_get_object_terms() etc. all
	 * hit the right tables.
	 */
	private static $real_prefix  = null;
	private static $saved_tables = null;

	private static function detect_prefix() {
		if ( self::$real_prefix !== null ) {
			return self::$real_prefix;
		}

		global $wpdb;

		// Build an ordered list of candidate prefixes.
		$candidates = array( $wpdb->prefix );

		// get_blog_prefix() may differ on multisite.
		if ( function_exists( 'get_blog_prefix' ) ) {
			$blog_prefix = get_blog_prefix();
			if ( $blog_prefix !== $wpdb->prefix ) {
				$candidates[] = $blog_prefix;
			}
		}

		// WordPress.com sometimes uses a double-underscore prefix.
		if ( ! in_array( '__wp_', $candidates, true ) ) {
			$candidates[] = '__wp_';
		}

		foreach ( $candidates as $prefix ) {
			$table = $prefix . 'posts';
			// Suppress errors in case the table doesn't exist.
			$count = $wpdb->get_var(
				"SELECT COUNT(*) FROM `{$table}` WHERE post_type = 'pcip_question' LIMIT 1"
			);
			if ( $count && (int) $count > 0 ) {
				self::$real_prefix = $prefix;
				return self::$real_prefix;
			}
		}

		// No candidate had rows — do not guess.  Use the default prefix
		// and let WP_Query (with suppress_filters) handle it normally.
		self::$real_prefix = $wpdb->prefix;
		return self::$real_prefix;
	}

	private static function swap_tables() {
		global $wpdb;
		$real = self::detect_prefix();

		if ( $real === $wpdb->prefix ) {
			self::$saved_tables = null;
			return;
		}

		$tables = array( 'posts', 'postmeta', 'terms', 'term_taxonomy', 'term_relationships', 'termmeta' );
		self::$saved_tables = array( 'prefix' => $wpdb->prefix );
		foreach ( $tables as $t ) {
			self::$saved_tables[ $t ] = $wpdb->$t;
			$wpdb->$t = $real . $t;
		}
		$wpdb->prefix = $real;
	}

	private static function restore_tables() {
		if ( self::$saved_tables === null ) {
			return;
		}
		global $wpdb;
		$wpdb->prefix = self::$saved_tables['prefix'];
		unset( self::$saved_tables['prefix'] );
		foreach ( self::$saved_tables as $t => $orig ) {
			$wpdb->$t = $orig;
		}
		self::$saved_tables = null;
	}

	// ------------------------------------------------------------------
	// Helpers
	// ------------------------------------------------------------------

	/**
	 * Prepare a MC question post for quiz/exam use.
	 * Returns both client-safe data (no correct answer) and server-side key data.
	 */
	private static function prepare_mc_question( $post, $number ) {
		$id = $post->ID;

		$options = array(
			array( 'text' => get_post_meta( $id, '_pcip_option_a', true ) ),
			array( 'text' => get_post_meta( $id, '_pcip_option_b', true ) ),
			array( 'text' => get_post_meta( $id, '_pcip_option_c', true ) ),
			array( 'text' => get_post_meta( $id, '_pcip_option_d', true ) ),
		);

		$correct_text = get_post_meta( $id, '_pcip_correct_answer', true );

		// Shuffle options and assign keys.
		shuffle( $options );
		$correct_key    = '';
		$client_options = array();
		$server_options = array();

		foreach ( $options as $i => $opt ) {
			$key              = 'opt_' . $i;
			$client_options[] = array( 'key' => $key, 'text' => $opt['text'] );
			$server_options[ $key ] = $opt['text'];

			if ( $opt['text'] === $correct_text ) {
				$correct_key = $key;
			}
		}

		// Determine domain and requirement.
		$terms      = wp_get_object_terms( $id, 'pcip_domain' );
		$domain     = '';
		$requirement = null;
		foreach ( $terms as $term ) {
			if ( preg_match( '/^domain-\d+$/', $term->slug ) ) {
				$domain = $term->slug;
			}
			if ( preg_match( '/^requirement-\d+$/', $term->slug ) ) {
				$requirement = $term->slug;
			}
		}

		return array(
			'client' => array(
				'number'  => $number,
				'id'      => $id,
				'text'    => get_post_meta( $id, '_pcip_question_text', true ),
				'options' => $client_options,
			),
			'server' => array(
				'question_id'   => $id,
				'question_text' => get_post_meta( $id, '_pcip_question_text', true ),
				'correct_key'   => $correct_key,
				'explanation'   => get_post_meta( $id, '_pcip_explanation', true ),
				'domain'        => $domain,
				'requirement'   => $requirement,
				'options'       => $server_options,
			),
		);
	}
}
