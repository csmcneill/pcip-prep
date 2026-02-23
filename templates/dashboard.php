<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="pcip-dashboard" class="pcip-container">
	<h2><?php esc_html_e( 'My Performance', 'pcip-prep' ); ?></h2>

	<!-- Loading state -->
	<div id="pcip-dash-loading" class="pcip-loading">
		<p><?php esc_html_e( 'Loading your stats...', 'pcip-prep' ); ?></p>
	</div>

	<!-- Empty state -->
	<div id="pcip-dash-empty" class="pcip-empty-state" style="display:none">
		<p><?php esc_html_e( 'No quiz data yet. Take a quiz or practice exam to see your stats here.', 'pcip-prep' ); ?></p>
	</div>

	<!-- Dashboard content -->
	<div id="pcip-dash-content" style="display:none">

		<!-- Overview cards -->
		<div class="pcip-dash-overview">
			<div class="pcip-dash-stat-card">
				<span class="pcip-dash-stat-value" id="pcip-dash-total-quizzes">0</span>
				<span class="pcip-dash-stat-label"><?php esc_html_e( 'Quizzes Taken', 'pcip-prep' ); ?></span>
			</div>
			<div class="pcip-dash-stat-card">
				<span class="pcip-dash-stat-value" id="pcip-dash-total-answered">0</span>
				<span class="pcip-dash-stat-label"><?php esc_html_e( 'Questions Answered', 'pcip-prep' ); ?></span>
			</div>
			<div class="pcip-dash-stat-card">
				<span class="pcip-dash-stat-value" id="pcip-dash-accuracy">0%</span>
				<span class="pcip-dash-stat-label"><?php esc_html_e( 'Overall Accuracy', 'pcip-prep' ); ?></span>
			</div>
			<div class="pcip-dash-stat-card">
				<span class="pcip-dash-stat-value" id="pcip-dash-exam-attempts">0</span>
				<span class="pcip-dash-stat-label"><?php esc_html_e( 'Exam Attempts', 'pcip-prep' ); ?></span>
			</div>
			<div class="pcip-dash-stat-card">
				<span class="pcip-dash-stat-value" id="pcip-dash-best-exam">--</span>
				<span class="pcip-dash-stat-label"><?php esc_html_e( 'Best Exam Score', 'pcip-prep' ); ?></span>
			</div>
		</div>

		<!-- Domain Performance -->
		<div class="pcip-dash-section">
			<h3><?php esc_html_e( 'Performance by Domain', 'pcip-prep' ); ?></h3>
			<div id="pcip-dash-domain-bars" class="pcip-dash-domain-bars">
				<!-- Injected by JS -->
			</div>
		</div>

		<!-- Requirement Performance (Domain 3) -->
		<div id="pcip-dash-req-section" class="pcip-dash-section" style="display:none">
			<h3><?php esc_html_e( 'Domain 3: Performance by Requirement', 'pcip-prep' ); ?></h3>
			<div id="pcip-dash-req-bars" class="pcip-dash-domain-bars">
				<!-- Injected by JS -->
			</div>
		</div>

		<!-- Weak Areas -->
		<div id="pcip-dash-weak-section" class="pcip-dash-section" style="display:none">
			<h3><?php esc_html_e( 'Areas to Improve', 'pcip-prep' ); ?></h3>
			<p class="pcip-subtitle"><?php esc_html_e( 'Domains and requirements where your accuracy is below 75%.', 'pcip-prep' ); ?></p>
			<div id="pcip-dash-weak-list" class="pcip-dash-weak-list">
				<!-- Injected by JS -->
			</div>
		</div>

		<!-- Exam History -->
		<div id="pcip-dash-exam-history" class="pcip-dash-section" style="display:none">
			<h3><?php esc_html_e( 'Exam History', 'pcip-prep' ); ?></h3>
			<table class="pcip-dash-history-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Score', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Result', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Time', 'pcip-prep' ); ?></th>
					</tr>
				</thead>
				<tbody id="pcip-dash-exam-rows">
					<!-- Injected by JS -->
				</tbody>
			</table>
		</div>

		<!-- Quiz History -->
		<div id="pcip-dash-quiz-history" class="pcip-dash-section" style="display:none">
			<h3><?php esc_html_e( 'Quiz History', 'pcip-prep' ); ?></h3>
			<table class="pcip-dash-history-table">
				<thead>
					<tr>
						<th><?php esc_html_e( 'Date', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Domain', 'pcip-prep' ); ?></th>
						<th><?php esc_html_e( 'Score', 'pcip-prep' ); ?></th>
					</tr>
				</thead>
				<tbody id="pcip-dash-quiz-rows">
					<!-- Injected by JS -->
				</tbody>
			</table>
		</div>
	</div>
</div>
