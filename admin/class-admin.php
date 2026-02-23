<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Admin {

	public function init() {
		add_action( 'admin_menu', array( $this, 'add_submenu_pages' ) );
		add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_admin_assets' ) );
		add_filter( 'manage_pcip_question_posts_columns', array( $this, 'question_columns' ) );
		add_action( 'manage_pcip_question_posts_custom_column', array( $this, 'question_column_content' ), 10, 2 );
		add_filter( 'manage_edit-pcip_question_sortable_columns', array( $this, 'question_sortable_columns' ) );
		add_action( 'restrict_manage_posts', array( $this, 'question_filters' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_questions_query' ) );
	}

	public function add_submenu_pages() {
		add_submenu_page(
			'edit.php?post_type=pcip_question',
			__( 'Import / Export', 'pcip-prep' ),
			__( 'Import / Export', 'pcip-prep' ),
			'manage_options',
			'pcip-prep-csv',
			array( 'PCIP_Prep_CSV_Admin', 'render_page' )
		);
	}

	public function enqueue_admin_assets( $hook ) {
		$screen = get_current_screen();
		if ( ! $screen || ! in_array( $screen->post_type, array( 'pcip_question', 'pcip_issue_report' ), true ) ) {
			return;
		}

		wp_enqueue_style(
			'pcip-prep-admin',
			PCIP_PREP_PLUGIN_URL . 'assets/css/admin.css',
			array(),
			PCIP_PREP_VERSION
		);
	}

	// ------------------------------------------------------------------
	// Question list table customization
	// ------------------------------------------------------------------

	public function question_columns( $columns ) {
		$new = array();
		$new['cb']         = $columns['cb'];
		$new['title']      = $columns['title'];
		$new['pcip_type']   = __( 'Type', 'pcip-prep' );
		$new['taxonomy-pcip_domain'] = __( 'Domain', 'pcip-prep' );
		$new['pcip_difficulty'] = __( 'Difficulty', 'pcip-prep' );
		$new['date']       = $columns['date'];
		return $new;
	}

	public function question_column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'pcip_type':
				$type = get_post_meta( $post_id, '_pcip_question_type', true );
				echo esc_html( 'flashcard' === $type ? 'Flashcard' : 'Multiple Choice' );
				break;
			case 'pcip_difficulty':
				$diff = get_post_meta( $post_id, '_pcip_difficulty', true );
				echo esc_html( ucfirst( $diff ) );
				break;
		}
	}

	public function question_sortable_columns( $columns ) {
		$columns['pcip_type']       = 'pcip_type';
		$columns['pcip_difficulty'] = 'pcip_difficulty';
		return $columns;
	}

	/**
	 * Add dropdown filters above the question list table.
	 */
	public function question_filters( $post_type ) {
		if ( 'pcip_question' !== $post_type ) {
			return;
		}

		// Type filter.
		$current_type = $_GET['pcip_type_filter'] ?? '';
		echo '<select name="pcip_type_filter">';
		echo '<option value="">' . esc_html__( 'All Types', 'pcip-prep' ) . '</option>';
		echo '<option value="multiple_choice"' . selected( $current_type, 'multiple_choice', false ) . '>' . esc_html__( 'Multiple Choice', 'pcip-prep' ) . '</option>';
		echo '<option value="flashcard"' . selected( $current_type, 'flashcard', false ) . '>' . esc_html__( 'Flashcard', 'pcip-prep' ) . '</option>';
		echo '</select>';

		// Difficulty filter.
		$current_diff = $_GET['pcip_diff_filter'] ?? '';
		echo '<select name="pcip_diff_filter">';
		echo '<option value="">' . esc_html__( 'All Difficulties', 'pcip-prep' ) . '</option>';
		foreach ( array( 'easy', 'medium', 'hard' ) as $d ) {
			echo '<option value="' . esc_attr( $d ) . '"' . selected( $current_diff, $d, false ) . '>' . esc_html( ucfirst( $d ) ) . '</option>';
		}
		echo '</select>';
	}

	/**
	 * Modify the query based on our custom filters.
	 */
	public function filter_questions_query( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'pcip_question' !== $screen->post_type ) {
			return;
		}

		$meta_query = $query->get( 'meta_query' ) ?: array();

		if ( ! empty( $_GET['pcip_type_filter'] ) ) {
			$meta_query[] = array(
				'key'   => '_pcip_question_type',
				'value' => sanitize_text_field( $_GET['pcip_type_filter'] ),
			);
		}

		if ( ! empty( $_GET['pcip_diff_filter'] ) ) {
			$meta_query[] = array(
				'key'   => '_pcip_difficulty',
				'value' => sanitize_text_field( $_GET['pcip_diff_filter'] ),
			);
		}

		if ( ! empty( $meta_query ) ) {
			$query->set( 'meta_query', $meta_query );
		}
	}
}
