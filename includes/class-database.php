<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Database {

	public static function results_table() {
		global $wpdb;
		return $wpdb->prefix . 'pcip_prep_results';
	}

	public static function sessions_table() {
		global $wpdb;
		return $wpdb->prefix . 'pcip_prep_sessions';
	}

	/**
	 * Plugin activation: create tables and populate terms.
	 */
	public static function activate() {
		self::create_tables();

		// Register taxonomy before populating terms.
		$post_types = new PCIP_Prep_Post_Types();
		$post_types->register_post_types();
		$post_types->register_taxonomies();
		PCIP_Prep_Post_Types::populate_default_terms();

		flush_rewrite_rules();
		update_option( 'pcip_prep_db_version', PCIP_PREP_DB_VERSION );
	}

	/**
	 * Plugin deactivation.
	 */
	public static function deactivate() {
		flush_rewrite_rules();
	}

	/**
	 * Create custom database tables.
	 */
	public static function create_tables() {
		global $wpdb;

		$charset_collate = $wpdb->get_charset_collate();
		$results_table   = self::results_table();
		$sessions_table  = self::sessions_table();

		$sql = "CREATE TABLE {$results_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			user_id bigint(20) NOT NULL,
			quiz_session_id varchar(36) NOT NULL,
			quiz_type varchar(20) NOT NULL,
			question_id bigint(20) NOT NULL,
			domain varchar(20) NOT NULL,
			requirement varchar(20) DEFAULT NULL,
			is_correct tinyint(1) NOT NULL,
			created_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			KEY user_id (user_id),
			KEY quiz_session_id (quiz_session_id),
			KEY domain (domain),
			KEY user_domain (user_id, domain),
			KEY user_requirement (user_id, requirement)
		) {$charset_collate};

		CREATE TABLE {$sessions_table} (
			id bigint(20) NOT NULL AUTO_INCREMENT,
			session_id varchar(36) NOT NULL,
			user_id bigint(20) NOT NULL,
			quiz_type varchar(20) NOT NULL,
			domain varchar(20) DEFAULT NULL,
			total_questions int(11) NOT NULL,
			correct_answers int(11) NOT NULL,
			score_percent decimal(5,2) NOT NULL,
			passed tinyint(1) DEFAULT NULL,
			time_spent_seconds int(11) DEFAULT NULL,
			completed_at datetime DEFAULT CURRENT_TIMESTAMP,
			PRIMARY KEY  (id),
			UNIQUE KEY session_id (session_id),
			KEY user_id (user_id),
			KEY user_quiz_type (user_id, quiz_type)
		) {$charset_collate};";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		dbDelta( $sql );
	}

	// ------------------------------------------------------------------
	// Result recording
	// ------------------------------------------------------------------

	/**
	 * Record a single question result.
	 */
	public static function record_result( $data ) {
		global $wpdb;

		return $wpdb->insert(
			self::results_table(),
			array(
				'user_id'         => absint( $data['user_id'] ),
				'quiz_session_id' => sanitize_text_field( $data['session_id'] ),
				'quiz_type'       => sanitize_text_field( $data['quiz_type'] ),
				'question_id'     => absint( $data['question_id'] ),
				'domain'          => sanitize_text_field( $data['domain'] ),
				'requirement'     => isset( $data['requirement'] ) ? sanitize_text_field( $data['requirement'] ) : null,
				'is_correct'      => absint( $data['is_correct'] ),
			),
			array( '%d', '%s', '%s', '%d', '%s', '%s', '%d' )
		);
	}

	/**
	 * Record a completed quiz session.
	 */
	public static function record_session( $data ) {
		global $wpdb;

		return $wpdb->insert(
			self::sessions_table(),
			array(
				'session_id'       => sanitize_text_field( $data['session_id'] ),
				'user_id'          => absint( $data['user_id'] ),
				'quiz_type'        => sanitize_text_field( $data['quiz_type'] ),
				'domain'           => isset( $data['domain'] ) ? sanitize_text_field( $data['domain'] ) : null,
				'total_questions'  => absint( $data['total_questions'] ),
				'correct_answers'  => absint( $data['correct_answers'] ),
				'score_percent'    => floatval( $data['score_percent'] ),
				'passed'           => isset( $data['passed'] ) ? absint( $data['passed'] ) : null,
				'time_spent_seconds' => isset( $data['time_spent_seconds'] ) ? absint( $data['time_spent_seconds'] ) : null,
			),
			array( '%s', '%d', '%s', '%s', '%d', '%d', '%f', '%d', '%d' )
		);
	}

	// ------------------------------------------------------------------
	// Dashboard queries
	// ------------------------------------------------------------------

	/**
	 * Get the best quiz session ID for each domain.
	 *
	 * "Best" = most correct answers (favors comprehensive quizzes over
	 * short perfect ones), then highest score %, then most recent.
	 *
	 * @return array<string, string> Domain slug => session_id.
	 */
	private static function get_best_domain_session_ids( $user_id ) {
		global $wpdb;
		$table = self::sessions_table();

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT domain, session_id
			 FROM {$table}
			 WHERE user_id = %d AND quiz_type = 'domain' AND domain IS NOT NULL
			 ORDER BY domain, correct_answers DESC, score_percent DESC, completed_at DESC",
			$user_id
		) );

		// First row per domain wins (already sorted by best).
		$ids = array();
		foreach ( $rows as $row ) {
			if ( ! isset( $ids[ $row->domain ] ) ) {
				$ids[ $row->domain ] = $row->session_id;
			}
		}
		return $ids;
	}

	/**
	 * Get a user's overall stats.
	 *
	 * Activity metrics (quizzes taken, questions answered) are cumulative.
	 * Accuracy is derived from each domain's best quiz session.
	 */
	public static function get_user_stats( $user_id ) {
		global $wpdb;

		$results_table  = self::results_table();
		$sessions_table = self::sessions_table();

		// Cumulative activity counts.
		$totals = $wpdb->get_row( $wpdb->prepare(
			"SELECT COUNT(*) AS total_answered,
					SUM(is_correct) AS total_correct
			 FROM {$results_table}
			 WHERE user_id = %d",
			$user_id
		) );

		$quiz_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$sessions_table} WHERE user_id = %d AND quiz_type = 'domain'",
			$user_id
		) );

		$exam_count = $wpdb->get_var( $wpdb->prepare(
			"SELECT COUNT(*) FROM {$sessions_table} WHERE user_id = %d AND quiz_type = 'exam'",
			$user_id
		) );

		$best_exam = $wpdb->get_var( $wpdb->prepare(
			"SELECT MAX(score_percent) FROM {$sessions_table} WHERE user_id = %d AND quiz_type = 'exam'",
			$user_id
		) );

		// Best-session accuracy: use only each domain's best quiz.
		$accuracy = 0;
		$best_sessions = self::get_best_domain_session_ids( $user_id );
		if ( ! empty( $best_sessions ) ) {
			$session_ids   = array_values( $best_sessions );
			$placeholders  = implode( ',', array_fill( 0, count( $session_ids ), '%s' ) );
			$best_totals   = $wpdb->get_row( $wpdb->prepare(
				"SELECT COUNT(*) AS total_answered,
						SUM(is_correct) AS total_correct
				 FROM {$results_table}
				 WHERE user_id = %d AND quiz_session_id IN ({$placeholders})",
				array_merge( array( $user_id ), $session_ids )
			) );
			if ( $best_totals && $best_totals->total_answered > 0 ) {
				$accuracy = round( ( $best_totals->total_correct / $best_totals->total_answered ) * 100, 1 );
			}
		}

		return array(
			'total_quizzes'    => (int) $quiz_count,
			'total_answered'   => (int) ( $totals ? $totals->total_answered : 0 ),
			'total_correct'    => (int) ( $totals ? $totals->total_correct : 0 ),
			'accuracy'         => $accuracy,
			'exam_attempts'    => (int) $exam_count,
			'best_exam_score'  => $best_exam ? round( (float) $best_exam, 1 ) : null,
		);
	}

	/**
	 * Get a user's performance broken down by domain.
	 * Uses each domain's best quiz session.
	 */
	public static function get_user_domain_stats( $user_id ) {
		global $wpdb;
		$table = self::results_table();

		$best_sessions = self::get_best_domain_session_ids( $user_id );
		if ( empty( $best_sessions ) ) {
			return array();
		}

		$session_ids  = array_values( $best_sessions );
		$placeholders = implode( ',', array_fill( 0, count( $session_ids ), '%s' ) );

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT domain,
					COUNT(*) AS total,
					SUM(is_correct) AS correct
			 FROM {$table}
			 WHERE user_id = %d AND quiz_session_id IN ({$placeholders})
			 GROUP BY domain
			 ORDER BY domain",
			array_merge( array( $user_id ), $session_ids )
		) );

		$stats = array();
		foreach ( $rows as $row ) {
			$stats[ $row->domain ] = array(
				'total'    => (int) $row->total,
				'correct'  => (int) $row->correct,
				'accuracy' => round( ( $row->correct / $row->total ) * 100, 1 ),
			);
		}
		return $stats;
	}

	/**
	 * Get a user's performance broken down by requirement (Domain 3 only).
	 *
	 * Each requirement is evaluated independently: the session where the
	 * user answered the most questions correctly for that requirement wins.
	 * This means a focused Requirement 11 quiz can beat the Requirement 11
	 * slice of a larger Domain 3 quiz.
	 */
	public static function get_user_requirement_stats( $user_id ) {
		global $wpdb;
		$table = self::results_table();

		// Get per-session, per-requirement breakdowns in one query.
		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT quiz_session_id, requirement,
					COUNT(*) AS total,
					SUM(is_correct) AS correct
			 FROM {$table}
			 WHERE user_id = %d AND requirement IS NOT NULL
			 GROUP BY quiz_session_id, requirement
			 ORDER BY requirement, correct DESC, total DESC",
			$user_id
		) );

		// First row per requirement wins (most correct, then most questions).
		$stats = array();
		foreach ( $rows as $row ) {
			if ( isset( $stats[ $row->requirement ] ) ) {
				continue;
			}
			$stats[ $row->requirement ] = array(
				'total'    => (int) $row->total,
				'correct'  => (int) $row->correct,
				'accuracy' => round( ( $row->correct / $row->total ) * 100, 1 ),
			);
		}
		return $stats;
	}

	/**
	 * Get a user's quiz session history.
	 */
	public static function get_user_sessions( $user_id, $type = null, $limit = 20 ) {
		global $wpdb;
		$table = self::sessions_table();

		$sql = $wpdb->prepare(
			"SELECT * FROM {$table} WHERE user_id = %d",
			$user_id
		);

		if ( $type ) {
			$sql .= $wpdb->prepare( ' AND quiz_type = %s', $type );
		}

		$sql .= $wpdb->prepare( ' ORDER BY completed_at DESC LIMIT %d', $limit );

		return $wpdb->get_results( $sql );
	}

	/**
	 * Get the detailed results for a specific quiz session.
	 */
	public static function get_session_results( $session_id ) {
		global $wpdb;
		$table = self::results_table();

		return $wpdb->get_results( $wpdb->prepare(
			"SELECT * FROM {$table} WHERE quiz_session_id = %s ORDER BY id",
			$session_id
		) );
	}

	/**
	 * Get domain breakdown for a specific session.
	 */
	public static function get_session_domain_breakdown( $session_id ) {
		global $wpdb;
		$table = self::results_table();

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT domain,
					COUNT(*) AS total,
					SUM(is_correct) AS correct
			 FROM {$table}
			 WHERE quiz_session_id = %s
			 GROUP BY domain
			 ORDER BY domain",
			$session_id
		) );

		$stats = array();
		foreach ( $rows as $row ) {
			$stats[ $row->domain ] = array(
				'total'    => (int) $row->total,
				'correct'  => (int) $row->correct,
				'accuracy' => round( ( $row->correct / $row->total ) * 100, 1 ),
			);
		}
		return $stats;
	}

	/**
	 * Get requirement breakdown for a specific session.
	 */
	public static function get_session_requirement_breakdown( $session_id ) {
		global $wpdb;
		$table = self::results_table();

		$rows = $wpdb->get_results( $wpdb->prepare(
			"SELECT requirement,
					COUNT(*) AS total,
					SUM(is_correct) AS correct
			 FROM {$table}
			 WHERE quiz_session_id = %s AND requirement IS NOT NULL
			 GROUP BY requirement
			 ORDER BY requirement",
			$session_id
		) );

		$stats = array();
		foreach ( $rows as $row ) {
			$stats[ $row->requirement ] = array(
				'total'    => (int) $row->total,
				'correct'  => (int) $row->correct,
				'accuracy' => round( ( $row->correct / $row->total ) * 100, 1 ),
			);
		}
		return $stats;
	}

	/**
	 * Delete orphaned results whose quiz_session_id has no matching session.
	 * These accumulate when a user abandons a quiz before submitting.
	 *
	 * @return int Number of rows deleted.
	 */
	public static function cleanup_orphaned_results() {
		global $wpdb;

		$results_table  = self::results_table();
		$sessions_table = self::sessions_table();

		return (int) $wpdb->query(
			"DELETE r FROM {$results_table} r
			 LEFT JOIN {$sessions_table} s ON r.quiz_session_id = s.session_id
			 WHERE s.session_id IS NULL"
		);
	}
}
