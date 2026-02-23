<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<!-- Exam Interface -->
<div id="pcip-exam-interface" class="pcip-section" style="display:none">
	<div class="pcip-exam-layout">

		<!-- Main content area -->
		<div class="pcip-exam-main">
			<!-- Sticky header with timer -->
			<div class="pcip-exam-header">
				<span id="pcip-exam-progress"><?php esc_html_e( 'Question', 'pcip-prep' ); ?> <span id="pcip-exam-current-num">1</span> / <span id="pcip-exam-total-num">75</span></span>
				<span id="pcip-exam-timer" class="pcip-exam-timer">90:00</span>
			</div>

			<!-- Question area -->
			<div class="pcip-exam-question-area">
				<p id="pcip-exam-question-text" class="pcip-question-text"></p>

				<div id="pcip-exam-options" class="pcip-options-list">
					<!-- Options injected by JS -->
				</div>

				<div class="pcip-exam-question-actions">
					<button id="pcip-exam-flag" class="pcip-btn pcip-btn-secondary pcip-flag-btn">
						<span class="pcip-flag-icon">&#9873;</span>
						<span id="pcip-exam-flag-text"><?php esc_html_e( 'Flag for Review', 'pcip-prep' ); ?></span>
					</button>
					<button id="pcip-exam-report-btn" class="pcip-btn pcip-btn-link pcip-report-issue-btn" data-question-id="">
						<?php esc_html_e( 'Report Issue', 'pcip-prep' ); ?>
					</button>
				</div>
			</div>

			<!-- Navigation -->
			<div class="pcip-exam-nav">
				<button id="pcip-exam-prev" class="pcip-btn pcip-btn-secondary">&larr; <?php esc_html_e( 'Previous', 'pcip-prep' ); ?></button>
				<button id="pcip-exam-next" class="pcip-btn pcip-btn-primary"><?php esc_html_e( 'Next', 'pcip-prep' ); ?> &rarr;</button>
				<button id="pcip-exam-review-btn" class="pcip-btn pcip-btn-accent"><?php esc_html_e( 'Review & Submit', 'pcip-prep' ); ?></button>
			</div>
		</div>

		<!-- Sidebar: question grid -->
		<div class="pcip-exam-sidebar">
			<div class="pcip-exam-sidebar-header">
				<h4><?php esc_html_e( 'Questions', 'pcip-prep' ); ?></h4>
				<span id="pcip-exam-answered-count">0 / 75 <?php esc_html_e( 'answered', 'pcip-prep' ); ?></span>
			</div>
			<div id="pcip-exam-grid" class="pcip-exam-question-grid">
				<!-- Grid buttons injected by JS -->
			</div>
			<div class="pcip-exam-grid-legend">
				<span class="pcip-legend-item"><span class="pcip-legend-dot pcip-status-answered"></span> <?php esc_html_e( 'Answered', 'pcip-prep' ); ?></span>
				<span class="pcip-legend-item"><span class="pcip-legend-dot pcip-status-flagged"></span> <?php esc_html_e( 'Flagged', 'pcip-prep' ); ?></span>
				<span class="pcip-legend-item"><span class="pcip-legend-dot pcip-status-unanswered"></span> <?php esc_html_e( 'Unanswered', 'pcip-prep' ); ?></span>
			</div>
			<button id="pcip-exam-sidebar-toggle" class="pcip-btn pcip-btn-secondary pcip-exam-sidebar-toggle-btn">
				<?php esc_html_e( 'Toggle Questions', 'pcip-prep' ); ?>
			</button>
		</div>
	</div>
</div>
