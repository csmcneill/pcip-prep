<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Post_Types {

	public function init() {
		add_action( 'init', array( $this, 'register_post_types' ) );
		add_action( 'init', array( $this, 'register_taxonomies' ) );
		add_action( 'init', array( $this, 'register_post_meta' ) );
	}

	public function register_post_types() {
		// PCI Question.
		register_post_type( 'pcip_question', array(
			'labels'              => array(
				'name'               => __( 'PCI Questions', 'pcip-prep' ),
				'singular_name'      => __( 'PCI Question', 'pcip-prep' ),
				'add_new'            => __( 'Add New Question', 'pcip-prep' ),
				'add_new_item'       => __( 'Add New PCI Question', 'pcip-prep' ),
				'edit_item'          => __( 'Edit PCI Question', 'pcip-prep' ),
				'new_item'           => __( 'New PCI Question', 'pcip-prep' ),
				'view_item'          => __( 'View PCI Question', 'pcip-prep' ),
				'search_items'       => __( 'Search PCI Questions', 'pcip-prep' ),
				'not_found'          => __( 'No questions found.', 'pcip-prep' ),
				'not_found_in_trash' => __( 'No questions found in Trash.', 'pcip-prep' ),
				'all_items'          => __( 'All Questions', 'pcip-prep' ),
				'menu_name'          => __( 'PCIP Prep', 'pcip-prep' ),
			),
			'public'              => true,
			'exclude_from_search' => true,
			'show_ui'             => true,
			'show_in_menu'        => true,
			'show_in_rest'        => true,
			'supports'            => array( 'title' ),
			'menu_icon'           => 'dashicons-welcome-learn-more',
			'menu_position'       => 30,
			'has_archive'         => false,
			'rewrite'             => false,
			'capability_type'     => 'post',
		) );

		// PCI Issue Report.
		register_post_type( 'pcip_issue_report', array(
			'labels'              => array(
				'name'               => __( 'Issue Reports', 'pcip-prep' ),
				'singular_name'      => __( 'Issue Report', 'pcip-prep' ),
				'add_new'            => __( 'Add New Report', 'pcip-prep' ),
				'add_new_item'       => __( 'Add New Issue Report', 'pcip-prep' ),
				'edit_item'          => __( 'Edit Issue Report', 'pcip-prep' ),
				'new_item'           => __( 'New Issue Report', 'pcip-prep' ),
				'view_item'          => __( 'View Issue Report', 'pcip-prep' ),
				'search_items'       => __( 'Search Issue Reports', 'pcip-prep' ),
				'not_found'          => __( 'No issue reports found.', 'pcip-prep' ),
				'not_found_in_trash' => __( 'No issue reports found in Trash.', 'pcip-prep' ),
				'all_items'          => __( 'Issue Reports', 'pcip-prep' ),
			),
			'public'              => false,
			'show_ui'             => true,
			'show_in_menu'        => 'edit.php?post_type=pcip_question',
			'show_in_rest'        => false,
			'supports'            => array( 'title' ),
			'menu_icon'           => 'dashicons-flag',
			'capability_type'     => 'post',
		) );
	}

	public function register_taxonomies() {
		register_taxonomy( 'pcip_domain', 'pcip_question', array(
			'labels'            => array(
				'name'          => __( 'PCI Domains', 'pcip-prep' ),
				'singular_name' => __( 'PCI Domain', 'pcip-prep' ),
				'all_items'     => __( 'All Domains', 'pcip-prep' ),
				'edit_item'     => __( 'Edit Domain', 'pcip-prep' ),
				'update_item'   => __( 'Update Domain', 'pcip-prep' ),
				'add_new_item'  => __( 'Add New Domain', 'pcip-prep' ),
				'new_item_name' => __( 'New Domain Name', 'pcip-prep' ),
				'search_items'  => __( 'Search Domains', 'pcip-prep' ),
			),
			'hierarchical'      => true,
			'public'            => false,
			'show_ui'           => true,
			'show_in_rest'      => true,
			'show_admin_column' => true,
			'rewrite'           => false,
		) );
	}

	public function register_post_meta() {
		$meta_fields = array(
			'_pcip_question_type'   => 'string',
			'_pcip_question_text'   => 'string',
			'_pcip_answer'          => 'string',
			'_pcip_option_a'        => 'string',
			'_pcip_option_b'        => 'string',
			'_pcip_option_c'        => 'string',
			'_pcip_option_d'        => 'string',
			'_pcip_correct_answer'  => 'string',
			'_pcip_explanation'     => 'string',
			'_pcip_difficulty'      => 'string',
			'_pcip_reference'   => 'string',
		);

		foreach ( $meta_fields as $key => $type ) {
			register_post_meta( 'pcip_question', $key, array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => true,
				'sanitize_callback' => 'sanitize_text_field',
			) );
		}

		// Issue report meta fields.
		$report_meta = array(
			'_pcip_reported_question_id'   => 'integer',
			'_pcip_reporter_email'         => 'string',
			'_pcip_reporter_user_id'       => 'integer',
			'_pcip_issue_description'      => 'string',
			'_pcip_remediation_suggestion' => 'string',
			'_pcip_report_status'          => 'string',
		);

		foreach ( $report_meta as $key => $type ) {
			register_post_meta( 'pcip_issue_report', $key, array(
				'type'              => $type,
				'single'            => true,
				'show_in_rest'      => false,
				'sanitize_callback' => 'integer' === $type ? 'absint' : 'sanitize_text_field',
			) );
		}
	}

	/**
	 * Populate default PCI domain taxonomy terms.
	 * Called during plugin activation.
	 */
	public static function populate_default_terms() {
		$domains = array(
			'domain-1' => 'Domain 1: PCI Essentials',
			'domain-2' => 'Domain 2: PCI DSS Overview',
			'domain-3' => 'Domain 3: PCI DSS Requirements',
			'domain-4' => 'Domain 4: Reporting Fundamentals',
			'domain-5' => 'Domain 5: SAQ Reporting',
		);

		foreach ( $domains as $slug => $name ) {
			if ( ! term_exists( $slug, 'pcip_domain' ) ) {
				wp_insert_term( $name, 'pcip_domain', array( 'slug' => $slug ) );
			}
		}

		// Requirements as children of Domain 3.
		$parent = get_term_by( 'slug', 'domain-3', 'pcip_domain' );
		if ( $parent ) {
			for ( $i = 1; $i <= 12; $i++ ) {
				$slug = 'requirement-' . $i;
				if ( ! term_exists( $slug, 'pcip_domain' ) ) {
					wp_insert_term(
						'Requirement ' . $i,
						'pcip_domain',
						array(
							'slug'   => $slug,
							'parent' => $parent->term_id,
						)
					);
				}
			}
		}
	}
}
