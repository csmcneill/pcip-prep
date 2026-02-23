<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Quiz Results -->
<div id="pcip-prep-results" class="pcip-section" style="display:none">
	<div class="pcip-results-card">
		<h2><?php esc_html_e( 'Quiz Complete', 'pcip-prep' ); ?></h2>

		<div class="pcip-score-display">
			<div id="pcip-prep-score-circle" class="pcip-score-circle">
				<span id="pcip-prep-score-percent" class="pcip-score-percent"></span>
			</div>
			<p id="pcip-prep-score-detail" class="pcip-score-detail"></p>
		</div>

		<div class="pcip-results-actions">
			<button id="pcip-prep-try-again" class="pcip-btn pcip-btn-primary">
				<?php esc_html_e( 'Try Again', 'pcip-prep' ); ?>
			</button>
			<button id="pcip-prep-back-to-select" class="pcip-btn pcip-btn-secondary">
				<?php esc_html_e( 'Back to Domains', 'pcip-prep' ); ?>
			</button>
		</div>
	</div>
</div>
