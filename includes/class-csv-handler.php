<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_CSV_Handler {

	/**
	 * Expected column headers for each question type.
	 */
	private static $mc_columns = array(
		'question_text', 'option_a', 'option_b', 'option_c', 'option_d',
		'correct_answer', 'explanation', 'domain', 'requirement', 'difficulty', 'pcip_reference',
	);

	private static $fc_columns = array(
		'question_text', 'answer', 'domain', 'requirement', 'pcip_reference',
	);

	// ------------------------------------------------------------------
	// Import
	// ------------------------------------------------------------------

	/**
	 * Import a CSV file of multiple-choice questions.
	 *
	 * @param  string $file_path Absolute path to the uploaded CSV.
	 * @return array  { created: int, updated: int, errors: array }
	 */
	public static function import_mc( $file_path ) {
		return self::import( $file_path, 'multiple_choice', self::$mc_columns );
	}

	/**
	 * Import a CSV file of flashcard questions.
	 */
	public static function import_flashcards( $file_path ) {
		return self::import( $file_path, 'flashcard', self::$fc_columns );
	}

	private static function import( $file_path, $type, $expected_columns ) {
		$result = array(
			'created' => 0,
			'updated' => 0,
			'errors'  => array(),
		);

		$handle = fopen( $file_path, 'r' );
		if ( ! $handle ) {
			$result['errors'][] = array( 'row' => 0, 'field' => '', 'message' => 'Could not open file.' );
			return $result;
		}

		// Strip UTF-8 BOM if present.
		$bom = fread( $handle, 3 );
		if ( "\xEF\xBB\xBF" !== $bom ) {
			rewind( $handle );
		}

		// Read and validate header row.
		$header = fgetcsv( $handle );
		if ( ! $header ) {
			$result['errors'][] = array( 'row' => 0, 'field' => '', 'message' => 'Empty CSV file.' );
			fclose( $handle );
			return $result;
		}

		$header = array_map( 'trim', array_map( 'strtolower', $header ) );
		$diff   = array_diff( $expected_columns, $header );
		if ( ! empty( $diff ) ) {
			$result['errors'][] = array(
				'row'     => 1,
				'field'   => '',
				'message' => 'Missing columns: ' . implode( ', ', $diff ),
			);
			fclose( $handle );
			return $result;
		}

		// Map column index to column name.
		$col_map = array_flip( $header );

		$row_num = 1;
		while ( ( $row = fgetcsv( $handle ) ) !== false ) {
			$row_num++;

			// Skip blank rows.
			if ( empty( array_filter( $row ) ) ) {
				continue;
			}

			$data = array();
			foreach ( $expected_columns as $col ) {
				$idx = $col_map[ $col ] ?? null;
				$data[ $col ] = ( null !== $idx && isset( $row[ $idx ] ) ) ? trim( $row[ $idx ] ) : '';
			}

			// Validate.
			$errors = self::validate_row( $data, $type, $row_num );
			if ( ! empty( $errors ) ) {
				$result['errors'] = array_merge( $result['errors'], $errors );
				continue;
			}

			// Upsert.
			$existing = self::find_existing_question( $data['question_text'], $type );

			if ( $existing ) {
				self::update_question( $existing->ID, $data, $type );
				$result['updated']++;
			} else {
				self::create_question( $data, $type );
				$result['created']++;
			}
		}

		fclose( $handle );
		return $result;
	}

	/**
	 * Validate a single CSV row.
	 */
	private static function validate_row( $data, $type, $row_num ) {
		$errors = array();

		if ( empty( $data['question_text'] ) ) {
			$errors[] = array( 'row' => $row_num, 'field' => 'question_text', 'message' => 'Question text is required.' );
		}

		$domain = intval( $data['domain'] );
		if ( $domain < 1 || $domain > 5 ) {
			$errors[] = array( 'row' => $row_num, 'field' => 'domain', 'message' => 'Domain must be 1-5.' );
		}

		if ( 3 === $domain ) {
			$req = intval( $data['requirement'] );
			if ( $req < 1 || $req > 12 ) {
				$errors[] = array( 'row' => $row_num, 'field' => 'requirement', 'message' => 'Requirement must be 1-12 for Domain 3.' );
			}
		}

		if ( 'multiple_choice' === $type ) {
			foreach ( array( 'option_a', 'option_b', 'option_c', 'option_d' ) as $opt ) {
				if ( empty( $data[ $opt ] ) ) {
					$errors[] = array( 'row' => $row_num, 'field' => $opt, 'message' => ucfirst( str_replace( '_', ' ', $opt ) ) . ' is required.' );
				}
			}
			$valid_answers = array( 'a', 'b', 'c', 'd' );
			if ( ! in_array( strtolower( $data['correct_answer'] ), $valid_answers, true ) ) {
				$errors[] = array( 'row' => $row_num, 'field' => 'correct_answer', 'message' => 'Correct answer must be a, b, c, or d.' );
			}
			if ( empty( $data['explanation'] ) ) {
				$errors[] = array( 'row' => $row_num, 'field' => 'explanation', 'message' => 'Explanation is required.' );
			}
			$valid_difficulties = array( 'easy', 'medium', 'hard' );
			if ( ! in_array( strtolower( $data['difficulty'] ), $valid_difficulties, true ) ) {
				$errors[] = array( 'row' => $row_num, 'field' => 'difficulty', 'message' => 'Difficulty must be easy, medium, or hard.' );
			}
		} elseif ( 'flashcard' === $type ) {
			if ( empty( $data['answer'] ) ) {
				$errors[] = array( 'row' => $row_num, 'field' => 'answer', 'message' => 'Answer is required.' );
			}
		}

		return $errors;
	}

	/**
	 * Find an existing question by text and type.
	 */
	private static function find_existing_question( $question_text, $type ) {
		$query = new WP_Query( array(
			'post_type'      => 'pcip_question',
			'post_status'    => 'publish',
			'posts_per_page' => 1,
			'meta_query'     => array(
				'relation' => 'AND',
				array(
					'key'   => '_pcip_question_text',
					'value' => $question_text,
				),
				array(
					'key'   => '_pcip_question_type',
					'value' => $type,
				),
			),
		) );

		return $query->have_posts() ? $query->posts[0] : null;
	}

	/**
	 * Create a new question post from CSV data.
	 */
	private static function create_question( $data, $type ) {
		$post_id = wp_insert_post( array(
			'post_type'   => 'pcip_question',
			'post_status' => 'publish',
			'post_title'  => wp_trim_words( $data['question_text'], 12, '...' ),
		) );

		if ( is_wp_error( $post_id ) ) {
			return;
		}

		self::save_question_meta( $post_id, $data, $type );
		self::assign_domain_terms( $post_id, $data );
	}

	/**
	 * Update an existing question post from CSV data.
	 */
	private static function update_question( $post_id, $data, $type ) {
		wp_update_post( array(
			'ID'         => $post_id,
			'post_title' => wp_trim_words( $data['question_text'], 12, '...' ),
		) );

		self::save_question_meta( $post_id, $data, $type );
		self::assign_domain_terms( $post_id, $data );
	}

	/**
	 * Save all post meta fields from CSV row data.
	 */
	private static function save_question_meta( $post_id, $data, $type ) {
		update_post_meta( $post_id, '_pcip_question_type', $type );
		update_post_meta( $post_id, '_pcip_question_text', $data['question_text'] );

		if ( 'flashcard' === $type ) {
			update_post_meta( $post_id, '_pcip_answer', $data['answer'] );
		} else {
			update_post_meta( $post_id, '_pcip_option_a', $data['option_a'] );
			update_post_meta( $post_id, '_pcip_option_b', $data['option_b'] );
			update_post_meta( $post_id, '_pcip_option_c', $data['option_c'] );
			update_post_meta( $post_id, '_pcip_option_d', $data['option_d'] );

			// Store correct answer as text.
			$letter       = strtolower( $data['correct_answer'] );
			$correct_text = $data[ 'option_' . $letter ] ?? '';
			update_post_meta( $post_id, '_pcip_correct_answer', $correct_text );

			update_post_meta( $post_id, '_pcip_explanation', $data['explanation'] );
			update_post_meta( $post_id, '_pcip_difficulty', strtolower( $data['difficulty'] ) );
		}

		update_post_meta( $post_id, '_pcip_reference', $data['pcip_reference'] ?? '' );
	}

	/**
	 * Assign the correct domain (and requirement) taxonomy terms.
	 */
	private static function assign_domain_terms( $post_id, $data ) {
		$domain_num = intval( $data['domain'] );
		$slugs      = array( 'domain-' . $domain_num );

		if ( 3 === $domain_num && ! empty( $data['requirement'] ) ) {
			$req_num = intval( $data['requirement'] );
			$slugs[] = 'requirement-' . $req_num;
		}

		$term_ids = array();
		foreach ( $slugs as $slug ) {
			$term = get_term_by( 'slug', $slug, 'pcip_domain' );
			if ( $term ) {
				$term_ids[] = $term->term_id;
			}
		}

		wp_set_object_terms( $post_id, $term_ids, 'pcip_domain' );
	}

	// ------------------------------------------------------------------
	// Export
	// ------------------------------------------------------------------

	/**
	 * Export MC questions as CSV string.
	 */
	public static function export_mc( $domain_slug = null ) {
		return self::export( 'multiple_choice', self::$mc_columns, $domain_slug );
	}

	/**
	 * Export flashcard questions as CSV string.
	 */
	public static function export_flashcards( $domain_slug = null ) {
		return self::export( 'flashcard', self::$fc_columns, $domain_slug );
	}

	private static function export( $type, $columns, $domain_slug = null ) {
		$args = array(
			'post_type'      => 'pcip_question',
			'post_status'    => 'publish',
			'posts_per_page' => -1,
			'meta_query'     => array(
				array(
					'key'   => '_pcip_question_type',
					'value' => $type,
				),
			),
			'orderby'        => 'ID',
			'order'          => 'ASC',
		);

		if ( $domain_slug ) {
			$args['tax_query'] = array(
				array(
					'taxonomy' => 'pcip_domain',
					'field'    => 'slug',
					'terms'    => $domain_slug,
				),
			);
		}

		$query = new WP_Query( $args );

		$output = fopen( 'php://temp', 'r+' );
		fputcsv( $output, $columns );

		foreach ( $query->posts as $post ) {
			$row = self::post_to_csv_row( $post, $type );
			fputcsv( $output, $row );
		}

		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );

		return $csv;
	}

	/**
	 * Convert a question post to a CSV row array.
	 */
	private static function post_to_csv_row( $post, $type ) {
		$id = $post->ID;

		// Determine domain and requirement from taxonomy.
		$terms      = wp_get_object_terms( $id, 'pcip_domain' );
		$domain_num = '';
		$req_num    = '';

		foreach ( $terms as $term ) {
			if ( preg_match( '/^domain-(\d+)$/', $term->slug, $m ) ) {
				$domain_num = $m[1];
			}
			if ( preg_match( '/^requirement-(\d+)$/', $term->slug, $m ) ) {
				$req_num = $m[1];
			}
		}

		if ( 'flashcard' === $type ) {
			return array(
				get_post_meta( $id, '_pcip_question_text', true ),
				get_post_meta( $id, '_pcip_answer', true ),
				$domain_num,
				$req_num,
				get_post_meta( $id, '_pcip_reference', true ),
			);
		}

		// Multiple choice: map correct answer text back to letter.
		$correct_text = get_post_meta( $id, '_pcip_correct_answer', true );
		$options      = array(
			'a' => get_post_meta( $id, '_pcip_option_a', true ),
			'b' => get_post_meta( $id, '_pcip_option_b', true ),
			'c' => get_post_meta( $id, '_pcip_option_c', true ),
			'd' => get_post_meta( $id, '_pcip_option_d', true ),
		);

		$correct_letter = 'a';
		foreach ( $options as $letter => $text ) {
			if ( $text === $correct_text ) {
				$correct_letter = $letter;
				break;
			}
		}

		return array(
			get_post_meta( $id, '_pcip_question_text', true ),
			$options['a'],
			$options['b'],
			$options['c'],
			$options['d'],
			$correct_letter,
			get_post_meta( $id, '_pcip_explanation', true ),
			$domain_num,
			$req_num,
			get_post_meta( $id, '_pcip_difficulty', true ),
			get_post_meta( $id, '_pcip_reference', true ),
		);
	}

	/**
	 * Generate a sample CSV string for download.
	 */
	public static function sample_mc_csv() {
		$output = fopen( 'php://temp', 'r+' );
		fputcsv( $output, self::$mc_columns );
		fputcsv( $output, array(
			'Which of the following is NOT one of the six goals of PCI DSS?',
			'Build and Maintain a Secure Network',
			'Protect Account Data',
			'Implement Data Loss Prevention',
			'Maintain a Vulnerability Management Program',
			'c',
			'PCI DSS has six goals. "Implement Data Loss Prevention" is not one of them.',
			'2',
			'',
			'medium',
			'PCI DSS v4.0.1 Overview',
		) );
		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );
		return $csv;
	}

	public static function sample_fc_csv() {
		$output = fopen( 'php://temp', 'r+' );
		fputcsv( $output, self::$fc_columns );
		fputcsv( $output, array(
			'What are the six goals of PCI DSS?',
			'1) Build and Maintain a Secure Network and Systems, 2) Protect Account Data, 3) Maintain a Vulnerability Management Program, 4) Implement Strong Access Control Measures, 5) Regularly Monitor and Test Networks, 6) Maintain an Information Security Policy',
			'2',
			'',
			'PCI DSS v4.0.1 Overview',
		) );
		rewind( $output );
		$csv = stream_get_contents( $output );
		fclose( $output );
		return $csv;
	}
}
