<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Issue_Reporter {

	public function init() {
		add_filter( 'manage_pcip_issue_report_posts_columns', array( $this, 'custom_columns' ) );
		add_action( 'manage_pcip_issue_report_posts_custom_column', array( $this, 'column_content' ), 10, 2 );
		add_action( 'restrict_manage_posts', array( $this, 'status_filter' ) );
		add_action( 'pre_get_posts', array( $this, 'filter_by_status' ) );
	}

	public function custom_columns( $columns ) {
		return array(
			'cb'                 => $columns['cb'],
			'title'              => __( 'Report', 'pcip-prep' ),
			'pcip_question'       => __( 'Question', 'pcip-prep' ),
			'pcip_reporter'       => __( 'Reporter', 'pcip-prep' ),
			'pcip_report_status'  => __( 'Status', 'pcip-prep' ),
			'date'               => $columns['date'],
		);
	}

	public function column_content( $column, $post_id ) {
		switch ( $column ) {
			case 'pcip_question':
				$question_id = get_post_meta( $post_id, '_pcip_reported_question_id', true );
				if ( $question_id ) {
					$title = get_the_title( $question_id );
					$url   = get_edit_post_link( $question_id );
					printf( '<a href="%s">%s</a>', esc_url( $url ), esc_html( $title ) );
				}
				break;

			case 'pcip_reporter':
				$email = get_post_meta( $post_id, '_pcip_reporter_email', true );
				echo esc_html( $email );
				break;

			case 'pcip_report_status':
				$status = get_post_meta( $post_id, '_pcip_report_status', true ) ?: 'open';
				$class  = 'open' === $status ? 'pcip-status-open' : 'pcip-status-resolved';
				printf(
					'<span class="pcip-status-badge %s">%s</span>',
					esc_attr( $class ),
					esc_html( ucfirst( $status ) )
				);
				break;
		}
	}

	public function status_filter( $post_type ) {
		if ( 'pcip_issue_report' !== $post_type ) {
			return;
		}

		$current = $_GET['pcip_report_status'] ?? '';
		echo '<select name="pcip_report_status">';
		echo '<option value="">' . esc_html__( 'All Statuses', 'pcip-prep' ) . '</option>';
		echo '<option value="open"' . selected( $current, 'open', false ) . '>' . esc_html__( 'Open', 'pcip-prep' ) . '</option>';
		echo '<option value="resolved"' . selected( $current, 'resolved', false ) . '>' . esc_html__( 'Resolved', 'pcip-prep' ) . '</option>';
		echo '</select>';
	}

	public function filter_by_status( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$screen = get_current_screen();
		if ( ! $screen || 'pcip_issue_report' !== $screen->post_type ) {
			return;
		}

		if ( ! empty( $_GET['pcip_report_status'] ) ) {
			$query->set( 'meta_query', array(
				array(
					'key'   => '_pcip_report_status',
					'value' => sanitize_text_field( $_GET['pcip_report_status'] ),
				),
			) );
		}
	}
}
