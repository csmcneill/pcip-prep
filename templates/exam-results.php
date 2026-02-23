<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Exam Results -->
<div id="pcip-exam-results" class="pcip-section" style="display:none">
	<div class="pcip-results-card">

		<!-- Pass/Fail Banner -->
		<div id="pcip-exam-result-banner" class="pcip-result-banner">
			<h2 id="pcip-exam-result-text"></h2>
		</div>

		<!-- Overall Score -->
		<div class="pcip-score-display">
			<div id="pcip-exam-score-circle" class="pcip-score-circle">
				<span id="pcip-exam-score-percent" class="pcip-score-percent"></span>
			</div>
			<p id="pcip-exam-score-detail" class="pcip-score-detail"></p>
			<p id="pcip-exam-time-spent" class="pcip-time-spent"></p>
		</div>

		<!-- Domain Breakdown -->
		<div class="pcip-breakdown-section">
			<h3><?php esc_html_e( 'Performance by Domain', 'pcip-prep' ); ?></h3>
			<table id="pcip-exam-domain-table" class="pcip-breakdown-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Domain', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Correct', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Total', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Score', 'pcip-prep' ); ?></th>
					</tr>
				</thead>
				<tbody id="pcip-exam-domain-rows">
					<!-- Injected by JS -->
				</tbody>
			</table>
		</div>

		<!-- Requirement Breakdown (Domain 3) -->
		<div id="pcip-exam-req-section" class="pcip-breakdown-section" style="display:none">
			<h3><?php esc_html_e( 'Domain 3: Requirement Breakdown', 'pcip-prep' ); ?></h3>
			<table class="pcip-breakdown-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Requirement', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Correct', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Total', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Score', 'pcip-prep' ); ?></th>
					</tr>
				</thead>
				<tbody id="pcip-exam-req-rows">
					<!-- Injected by JS -->
				</tbody>
			</table>
		</div>

		<!-- Review Answers -->
		<div id="pcip-exam-answer-review" class="pcip-answer-review-section" style="display:none">
			<h3><?php esc_html_e( 'Answer Review', 'pcip-prep' ); ?></h3>
			<div id="pcip-exam-answer-list">
				<!-- Injected by JS -->
			</div>
		</div>

		<!-- Actions -->
		<div class="pcip-results-actions">
			<button id="pcip-exam-review-answers" class="pcip-btn pcip-btn-secondary">
				<?php esc_html_e( 'Review Answers', 'pcip-prep' ); ?>
			</button>
			<button id="pcip-exam-take-another" class="pcip-btn pcip-btn-primary">
				<?php esc_html_e( 'Take Another Exam', 'pcip-prep' ); ?>
			</button>
		</div>
	</div>
</div>
