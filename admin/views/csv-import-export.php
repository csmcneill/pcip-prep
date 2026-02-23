<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$export_nonce = wp_create_nonce( 'pcip_csv_export' );
$base_url     = admin_url( 'edit.php?post_type=pcip_question&page=pcip-prep-csv' );

// Get domain options for export filter.
$domains = get_terms( array(
	'taxonomy'   => 'pcip_domain',
	'hide_empty' => false,
	'parent'     => 0,
) );
?>
<div class="wrap">
	<h1><?php esc_html_e( 'PCIP Prep: Import / Export', 'pcip-prep' ); ?></h1>

	<?php if ( $import_result ) : ?>
		<div class="notice <?php echo empty( $import_result['errors'] ) ? 'notice-success' : 'notice-warning'; ?> is-dismissible">
			<?php if ( ! empty( $import_result['created'] ) || ! empty( $import_result['updated'] ) ) : ?>
				<p>
					<?php
					printf(
						/* translators: 1: created count, 2: updated count */
						esc_html__( 'Import complete: %1$d questions created, %2$d questions updated.', 'pcip-prep' ),
						intval( $import_result['created'] ?? 0 ),
						intval( $import_result['updated'] ?? 0 )
					);
					?>
				</p>
			<?php endif; ?>
			<?php if ( ! empty( $import_result['errors'] ) ) : ?>
				<p><strong><?php esc_html_e( 'Errors:', 'pcip-prep' ); ?></strong></p>
				<table class="widefat striped" style="max-width:700px">
					<thead>
						<tr>
							<th><?php esc_html_e( 'Row', 'pcip-prep' ); ?></th>
							<th><?php esc_html_e( 'Field', 'pcip-prep' ); ?></th>
							<th><?php esc_html_e( 'Error', 'pcip-prep' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $import_result['errors'] as $err ) : ?>
							<tr>
								<td><?php echo esc_html( $err['row'] ); ?></td>
								<td><?php echo esc_html( $err['field'] ); ?></td>
								<td><?php echo esc_html( $err['message'] ); ?></td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
	<?php endif; ?>

	<div style="display:flex; gap:24px; flex-wrap:wrap;">
		<!-- Import Section -->
		<div class="card" style="flex:1; min-width:340px; max-width:520px;">
			<h2><?php esc_html_e( 'Import Questions', 'pcip-prep' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Upload a CSV file to create or update questions in bulk. Questions with matching text will be updated; new questions will be created.', 'pcip-prep' ); ?>
			</p>

			<form method="post" enctype="multipart/form-data" style="margin-top:16px;">
				<?php wp_nonce_field( 'pcip_csv_import', 'pcip_csv_import_nonce' ); ?>

				<table class="form-table">
					<tr>
						<th scope="row">
							<label for="pcip_import_type"><?php esc_html_e( 'Question Type', 'pcip-prep' ); ?></label>
						</th>
						<td>
							<select name="pcip_import_type" id="pcip_import_type">
								<option value="mc"><?php esc_html_e( 'Multiple Choice', 'pcip-prep' ); ?></option>
								<option value="flashcard"><?php esc_html_e( 'Flashcard', 'pcip-prep' ); ?></option>
							</select>
						</td>
					</tr>
					<tr>
						<th scope="row">
							<label for="pcip_csv_file"><?php esc_html_e( 'CSV File', 'pcip-prep' ); ?></label>
						</th>
						<td>
							<input type="file" name="pcip_csv_file" id="pcip_csv_file" accept=".csv" required />
						</td>
					</tr>
				</table>

				<?php submit_button( __( 'Import Questions', 'pcip-prep' ), 'primary', 'pcip_import_submit' ); ?>
			</form>

			<hr />
			<h3><?php esc_html_e( 'Sample CSVs', 'pcip-prep' ); ?></h3>
			<p>
				<a href="<?php echo esc_url( add_query_arg( array( 'pcip_export' => 'sample_mc', '_wpnonce' => $export_nonce ), $base_url ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Download MC Sample', 'pcip-prep' ); ?>
				</a>
				<a href="<?php echo esc_url( add_query_arg( array( 'pcip_export' => 'sample_fc', '_wpnonce' => $export_nonce ), $base_url ) ); ?>" class="button button-secondary">
					<?php esc_html_e( 'Download Flashcard Sample', 'pcip-prep' ); ?>
				</a>
			</p>
		</div>

		<!-- Export Section -->
		<div class="card" style="flex:1; min-width:340px; max-width:520px;">
			<h2><?php esc_html_e( 'Export Questions', 'pcip-prep' ); ?></h2>
			<p class="description">
				<?php esc_html_e( 'Download all questions as a CSV file. You can filter by domain or export everything.', 'pcip-prep' ); ?>
			</p>

			<table class="form-table" style="margin-top:16px;">
				<tr>
					<th scope="row">
						<label for="pcip_export_domain"><?php esc_html_e( 'Filter by Domain', 'pcip-prep' ); ?></label>
					</th>
					<td>
						<select id="pcip_export_domain">
							<option value=""><?php esc_html_e( 'All Domains', 'pcip-prep' ); ?></option>
							<?php if ( ! is_wp_error( $domains ) ) : ?>
								<?php foreach ( $domains as $term ) : ?>
									<option value="<?php echo esc_attr( $term->slug ); ?>">
										<?php echo esc_html( $term->name ); ?>
									</option>
								<?php endforeach; ?>
							<?php endif; ?>
						</select>
					</td>
				</tr>
			</table>

			<p style="margin-top:16px;">
				<a id="pcip-export-mc" href="#" class="button button-primary">
					<?php esc_html_e( 'Export MC Questions (CSV)', 'pcip-prep' ); ?>
				</a>
				<a id="pcip-export-fc" href="#" class="button button-primary">
					<?php esc_html_e( 'Export Flashcards (CSV)', 'pcip-prep' ); ?>
				</a>
			</p>
		</div>
	</div>
</div>

<script>
(function() {
	var baseUrl   = <?php echo wp_json_encode( $base_url ); ?>;
	var nonce     = <?php echo wp_json_encode( $export_nonce ); ?>;
	var domainSel = document.getElementById('pcip_export_domain');

	function buildExportUrl(type) {
		var domain = domainSel.value;
		var url = baseUrl + '&pcip_export=' + type + '&_wpnonce=' + nonce;
		if (domain) {
			url += '&pcip_domain=' + encodeURIComponent(domain);
		}
		return url;
	}

	document.getElementById('pcip-export-mc').addEventListener('click', function(e) {
		e.preventDefault();
		window.location.href = buildExportUrl('mc');
	});

	document.getElementById('pcip-export-fc').addEventListener('click', function(e) {
		e.preventDefault();
		window.location.href = buildExportUrl('flashcards');
	});
})();
</script>
