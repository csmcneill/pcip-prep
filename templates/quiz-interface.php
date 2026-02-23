<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Quiz Interface (shown during active quiz) -->
<div id="pcip-prep-interface" class="pcip-section" style="display:none">
	<div class="pcip-prep-header">
		<span id="pcip-prep-progress-text" class="pcip-counter"></span>
		<div id="pcip-prep-progress-bar" class="pcip-progress-bar">
			<div id="pcip-prep-progress-fill" class="pcip-progress-fill"></div>
		</div>
	</div>

	<div class="pcip-question-card">
		<p id="pcip-prep-question-text" class="pcip-question-text"></p>

		<div id="pcip-prep-options" class="pcip-options-list">
			<!-- Options injected by JS -->
		</div>

		<button id="pcip-prep-submit-answer" class="pcip-btn pcip-btn-primary" disabled>
			<?php esc_html_e( 'Submit Answer', 'pcip-prep' ); ?>
		</button>

		<!-- Feedback area (shown after answer) -->
		<div id="pcip-prep-feedback" class="pcip-feedback" style="display:none">
			<div id="pcip-prep-feedback-result" class="pcip-feedback-result"></div>
			<div id="pcip-prep-explanation" class="pcip-explanation"></div>
			<div class="pcip-prep-feedback-actions">
				<button id="pcip-prep-report-btn" class="pcip-btn pcip-btn-link pcip-report-issue-btn" data-question-id="">
					<?php esc_html_e( 'Report Issue', 'pcip-prep' ); ?>
				</button>
				<button id="pcip-prep-next" class="pcip-btn pcip-btn-primary">
					<?php esc_html_e( 'Next Question', 'pcip-prep' ); ?> &rarr;
				</button>
			</div>
		</div>
	</div>
</div>
