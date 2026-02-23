<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="pcip-exam-app" class="pcip-container">

	<!-- Splash Screen -->
	<div id="pcip-exam-splash" class="pcip-section">
		<div class="pcip-exam-splash-card">
			<h2><?php esc_html_e( 'PCIP Practice Exam', 'pcip-prep' ); ?></h2>
			<p class="pcip-subtitle"><?php esc_html_e( 'Simulate the real PCI Professional certification exam.', 'pcip-prep' ); ?></p>

			<div class="pcip-exam-details">
				<div class="pcip-exam-detail">
					<span class="pcip-detail-label"><?php esc_html_e( 'Questions', 'pcip-prep' ); ?></span>
					<span class="pcip-detail-value">75</span>
				</div>
				<div class="pcip-exam-detail">
					<span class="pcip-detail-label"><?php esc_html_e( 'Time Limit', 'pcip-prep' ); ?></span>
					<span class="pcip-detail-value"><?php esc_html_e( '90 minutes', 'pcip-prep' ); ?></span>
				</div>
				<div class="pcip-exam-detail">
					<span class="pcip-detail-label"><?php esc_html_e( 'Passing Score', 'pcip-prep' ); ?></span>
					<span class="pcip-detail-value">75%</span>
				</div>
				<div class="pcip-exam-detail">
					<span class="pcip-detail-label"><?php esc_html_e( 'Format', 'pcip-prep' ); ?></span>
					<span class="pcip-detail-value"><?php esc_html_e( 'Multiple Choice', 'pcip-prep' ); ?></span>
				</div>
			</div>

			<div class="pcip-exam-notice">
				<p><?php esc_html_e( 'This is a closed-note practice exam. The actual PCIP exam is proctored at a PearsonVue testing center. You can flag questions for review and navigate freely between questions before submitting.', 'pcip-prep' ); ?></p>
			</div>

			<div id="pcip-exam-insufficient" class="pcip-exam-notice pcip-notice-warning" style="display:none">
				<p><?php esc_html_e( 'Not enough questions available to run a full 75-question exam. Please check back once more questions have been added.', 'pcip-prep' ); ?></p>
			</div>

			<button id="pcip-exam-begin" class="pcip-btn pcip-btn-primary pcip-btn-large">
				<?php esc_html_e( 'Begin Exam', 'pcip-prep' ); ?>
			</button>
		</div>
	</div>
</div>
