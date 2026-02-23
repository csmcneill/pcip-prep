<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

global $post;

wp_nonce_field( 'pcip_report_meta', 'pcip_report_meta_nonce' );

$question_id = get_post_meta( $post->ID, '_pcip_reported_question_id', true );
$email       = get_post_meta( $post->ID, '_pcip_reporter_email', true );
$user_id     = get_post_meta( $post->ID, '_pcip_reporter_user_id', true );
$description = get_post_meta( $post->ID, '_pcip_issue_description', true );
$suggestion  = get_post_meta( $post->ID, '_pcip_remediation_suggestion', true );
$status      = get_post_meta( $post->ID, '_pcip_report_status', true ) ?: 'open';
?>

<table class="form-table">
	<tr>
		<th scope="row"><?php esc_html_e( 'Reported Question', 'pcip-prep' ); ?></th>
		<td>
			<?php if ( $question_id ) : ?>
				<a href="<?php echo esc_url( get_edit_post_link( $question_id ) ); ?>">
					<?php echo esc_html( get_the_title( $question_id ) ); ?> (#<?php echo esc_html( $question_id ); ?>)
				</a>
			<?php else : ?>
				<em><?php esc_html_e( 'Unknown', 'pcip-prep' ); ?></em>
			<?php endif; ?>
		</td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Reporter', 'pcip-prep' ); ?></th>
		<td><?php echo esc_html( $email ); ?></td>
	</tr>
	<tr>
		<th scope="row"><?php esc_html_e( 'Issue Description', 'pcip-prep' ); ?></th>
		<td>
			<div style="background:#f9f9f9; padding:12px; border:1px solid #ddd; border-radius:4px;">
				<?php echo nl2br( esc_html( $description ) ); ?>
			</div>
		</td>
	</tr>
	<?php if ( $suggestion ) : ?>
	<tr>
		<th scope="row"><?php esc_html_e( 'Suggested Fix', 'pcip-prep' ); ?></th>
		<td>
			<div style="background:#f0f6fc; padding:12px; border:1px solid #c3d9ed; border-radius:4px;">
				<?php echo nl2br( esc_html( $suggestion ) ); ?>
			</div>
		</td>
	</tr>
	<?php endif; ?>
	<tr>
		<th scope="row">
			<label for="pcip_report_status"><?php esc_html_e( 'Status', 'pcip-prep' ); ?></label>
		</th>
		<td>
			<select name="_pcip_report_status" id="pcip_report_status">
				<option value="open" <?php selected( $status, 'open' ); ?>><?php esc_html_e( 'Open', 'pcip-prep' ); ?></option>
				<option value="resolved" <?php selected( $status, 'resolved' ); ?>><?php esc_html_e( 'Resolved', 'pcip-prep' ); ?></option>
			</select>
		</td>
	</tr>
</table>
