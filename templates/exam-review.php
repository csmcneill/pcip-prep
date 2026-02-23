<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Exam Review (pre-submission) -->
<div id="pcip-exam-review" class="pcip-section" style="display:none">
	<div class="pcip-review-card">
		<h2><?php esc_html_e( 'Review Your Exam', 'pcip-prep' ); ?></h2>
		<p class="pcip-subtitle"><?php esc_html_e( 'Review your answers before submitting. Click any question number to go back and change your answer.', 'pcip-prep' ); ?></p>

		<div class="pcip-review-summary">
			<div class="pcip-review-stat">
				<span class="pcip-review-stat-value" id="pcip-review-answered">0</span>
				<span class="pcip-review-stat-label"><?php esc_html_e( 'Answered', 'pcip-prep' ); ?></span>
			</div>
			<div class="pcip-review-stat">
				<span class="pcip-review-stat-value" id="pcip-review-unanswered">0</span>
				<span class="pcip-review-stat-label"><?php esc_html_e( 'Unanswered', 'pcip-prep' ); ?></span>
			</div>
			<div class="pcip-review-stat">
				<span class="pcip-review-stat-value" id="pcip-review-flagged">0</span>
				<span class="pcip-review-stat-label"><?php esc_html_e( 'Flagged', 'pcip-prep' ); ?></span>
			</div>
		</div>

		<div id="pcip-review-grid" class="pcip-exam-question-grid pcip-review-grid">
			<!-- Grid injected by JS -->
		</div>

		<div class="pcip-review-actions">
			<button id="pcip-review-back" class="pcip-btn pcip-btn-secondary">
				&larr; <?php esc_html_e( 'Return to Exam', 'pcip-prep' ); ?>
			</button>
			<button id="pcip-review-submit" class="pcip-btn pcip-btn-primary pcip-btn-large">
				<?php esc_html_e( 'Submit Exam', 'pcip-prep' ); ?>
			</button>
		</div>
	</div>
</div>
