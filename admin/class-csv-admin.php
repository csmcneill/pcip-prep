<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_CSV_Admin {

	public function init() {
		add_action( 'admin_init', array( $this, 'handle_csv_actions' ) );
	}

	/**
	 * Handle CSV import uploads and export downloads.
	 */
	public function handle_csv_actions() {
		if ( ! current_user_can( 'manage_options' ) ) {
			return;
		}

		// Handle exports (before any output).
		if ( isset( $_GET['pcip_export'] ) && wp_verify_nonce( $_GET['_wpnonce'] ?? '', 'pcip_csv_export' ) ) {
			$export_type = sanitize_text_field( $_GET['pcip_export'] );
			$domain      = ! empty( $_GET['pcip_domain'] ) ? sanitize_text_field( $_GET['pcip_domain'] ) : null;

			if ( 'mc' === $export_type ) {
				$csv      = PCIP_Prep_CSV_Handler::export_mc( $domain );
				$filename = 'pcip-prep-mc-questions.csv';
			} elseif ( 'flashcards' === $export_type ) {
				$csv      = PCIP_Prep_CSV_Handler::export_flashcards( $domain );
				$filename = 'pcip-prep-flashcards.csv';
			} elseif ( 'sample_mc' === $export_type ) {
				$csv      = PCIP_Prep_CSV_Handler::sample_mc_csv();
				$filename = 'pcip-prep-mc-sample.csv';
			} elseif ( 'sample_fc' === $export_type ) {
				$csv      = PCIP_Prep_CSV_Handler::sample_fc_csv();
				$filename = 'pcip-prep-flashcard-sample.csv';
			} else {
				return;
			}

			header( 'Content-Type: text/csv; charset=utf-8' );
			header( 'Content-Disposition: attachment; filename="' . $filename . '"' );
			header( 'Pragma: no-cache' );
			header( 'Expires: 0' );
			echo $csv; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
			exit;
		}
	}

	/**
	 * Process an import form submission.
	 * Returns result array or null if no import was submitted.
	 */
	public static function process_import() {
		if ( ! isset( $_POST['pcip_csv_import_nonce'] )
			|| ! wp_verify_nonce( $_POST['pcip_csv_import_nonce'], 'pcip_csv_import' ) ) {
			return null;
		}

		if ( ! current_user_can( 'manage_options' ) ) {
			return null;
		}

		$import_type = sanitize_text_field( $_POST['pcip_import_type'] ?? '' );
		$file_key    = 'pcip_csv_file';

		if ( empty( $_FILES[ $file_key ]['tmp_name'] ) ) {
			return array( 'errors' => array( array( 'row' => 0, 'field' => '', 'message' => 'No file uploaded.' ) ) );
		}

		$file_path = $_FILES[ $file_key ]['tmp_name'];

		if ( 'mc' === $import_type ) {
			return PCIP_Prep_CSV_Handler::import_mc( $file_path );
		} elseif ( 'flashcard' === $import_type ) {
			return PCIP_Prep_CSV_Handler::import_flashcards( $file_path );
		}

		return array( 'errors' => array( array( 'row' => 0, 'field' => '', 'message' => 'Invalid import type.' ) ) );
	}

	/**
	 * Render the Import / Export admin page.
	 */
	public static function render_page() {
		$import_result = self::process_import();
		include PCIP_PREP_PLUGIN_DIR . 'admin/views/csv-import-export.php';
	}
}
