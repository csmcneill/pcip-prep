<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Issue Report Modal -->
<div id="pcip-issue-modal" class="pcip-modal" style="display:none">
	<div class="pcip-modal-backdrop"></div>
	<div class="pcip-modal-content">
		<div class="pcip-modal-header">
			<h3><?php esc_html_e( 'Report an Issue', 'pcip-prep' ); ?></h3>
			<button id="pcip-issue-close" class="pcip-modal-close">&times;</button>
		</div>
		<form id="pcip-issue-form" class="pcip-modal-body">
			<input type="hidden" id="pcip-issue-question-id" name="question_id" value="" />

			<div class="pcip-form-field">
				<label for="pcip-issue-description"><?php esc_html_e( 'What is the issue?', 'pcip-prep' ); ?></label>
				<textarea id="pcip-issue-description" name="description" rows="4" required
					placeholder="<?php esc_attr_e( 'Describe the problem with this question...', 'pcip-prep' ); ?>"></textarea>
			</div>

			<div class="pcip-form-field">
				<label for="pcip-issue-suggestion"><?php esc_html_e( 'Suggested fix (optional)', 'pcip-prep' ); ?></label>
				<textarea id="pcip-issue-suggestion" name="suggestion" rows="3"
					placeholder="<?php esc_attr_e( 'How would you fix this?', 'pcip-prep' ); ?>"></textarea>
			</div>

			<div class="pcip-modal-footer">
				<button type="button" id="pcip-issue-cancel" class="pcip-btn pcip-btn-secondary">
					<?php esc_html_e( 'Cancel', 'pcip-prep' ); ?>
				</button>
				<button type="submit" class="pcip-btn pcip-btn-primary">
					<?php esc_html_e( 'Submit Report', 'pcip-prep' ); ?>
				</button>
			</div>
		</form>

		<div id="pcip-issue-success" class="pcip-modal-body" style="display:none">
			<p class="pcip-success-message"><?php esc_html_e( 'Issue reported successfully. Thank you for helping improve this content.', 'pcip-prep' ); ?></p>
			<button id="pcip-issue-done" class="pcip-btn pcip-btn-primary">
				<?php esc_html_e( 'Done', 'pcip-prep' ); ?>
			</button>
		</div>
	</div>
</div>
