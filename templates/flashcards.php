<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="pcip-flashcards" class="pcip-container">

	<!-- Domain Selection -->
	<div id="pcip-fc-domain-select" class="pcip-section">
		<h2><?php esc_html_e( 'Flashcard Study', 'pcip-prep' ); ?></h2>
		<p class="pcip-subtitle"><?php esc_html_e( 'Select a domain to begin studying. Cards are ungraded and untracked.', 'pcip-prep' ); ?></p>

		<div class="pcip-domain-grid">
			<button class="pcip-domain-card" data-domain="domain-1">
				<span class="pcip-domain-number">1</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'PCI Essentials', 'pcip-prep' ); ?></span>
			</button>
			<button class="pcip-domain-card" data-domain="domain-2">
				<span class="pcip-domain-number">2</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'PCI DSS Overview', 'pcip-prep' ); ?></span>
			</button>
			<button class="pcip-domain-card pcip-domain-expandable" data-domain="domain-3">
				<span class="pcip-domain-number">3</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'PCI DSS Requirements', 'pcip-prep' ); ?></span>
				<span class="pcip-domain-expand-hint"><?php esc_html_e( 'Click to see requirements', 'pcip-prep' ); ?></span>
			</button>
			<div id="pcip-fc-requirements" class="pcip-requirements-grid" style="display:none">
				<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
					<button class="pcip-req-card" data-requirement="requirement-<?php echo esc_attr( $i ); ?>" data-domain="domain-3">
						<span class="pcip-req-number"><?php echo esc_html( $i ); ?></span>
						<span class="pcip-req-name"><?php printf( esc_html__( 'Requirement %d', 'pcip-prep' ), $i ); ?></span>
					</button>
				<?php endfor; ?>
				<button class="pcip-req-card pcip-req-all" data-domain="domain-3">
					<span class="pcip-req-name"><?php esc_html_e( 'All Requirements', 'pcip-prep' ); ?></span>
				</button>
			</div>
			<button class="pcip-domain-card" data-domain="domain-4">
				<span class="pcip-domain-number">4</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'Reporting Fundamentals', 'pcip-prep' ); ?></span>
			</button>
			<button class="pcip-domain-card" data-domain="domain-5">
				<span class="pcip-domain-number">5</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'SAQ Reporting', 'pcip-prep' ); ?></span>
			</button>
		</div>
	</div>

	<!-- Flashcard Deck -->
	<div id="pcip-fc-deck" class="pcip-section" style="display:none">
		<div class="pcip-fc-header">
			<button id="pcip-fc-back" class="pcip-btn pcip-btn-secondary">&larr; <?php esc_html_e( 'Back', 'pcip-prep' ); ?></button>
			<span id="pcip-fc-counter" class="pcip-counter"></span>
		</div>

		<div class="pcip-flashcard">
			<div class="pcip-fc-question">
				<p id="pcip-fc-question-text"></p>
				<span id="pcip-fc-reference" class="pcip-reference"></span>
			</div>
			<div id="pcip-fc-answer-area" class="pcip-fc-answer" style="display:none">
				<p id="pcip-fc-answer-text"></p>
			</div>
			<button id="pcip-fc-reveal" class="pcip-btn pcip-btn-primary pcip-btn-full">
				<?php esc_html_e( 'Reveal Answer', 'pcip-prep' ); ?>
			</button>
		</div>

		<div class="pcip-fc-nav">
			<button id="pcip-fc-prev" class="pcip-btn pcip-btn-secondary">&larr; <?php esc_html_e( 'Previous', 'pcip-prep' ); ?></button>
			<button id="pcip-fc-report" class="pcip-btn pcip-btn-link pcip-report-issue-btn" data-question-id="">
				<?php esc_html_e( 'Report Issue', 'pcip-prep' ); ?>
			</button>
			<button id="pcip-fc-next" class="pcip-btn pcip-btn-primary"><?php esc_html_e( 'Next', 'pcip-prep' ); ?> &rarr;</button>
		</div>
	</div>

	<!-- Empty state -->
	<div id="pcip-fc-empty" class="pcip-section pcip-empty-state" style="display:none">
		<p><?php esc_html_e( 'No flashcards found for this domain. Check back once an admin has uploaded questions.', 'pcip-prep' ); ?></p>
		<button id="pcip-fc-empty-back" class="pcip-btn pcip-btn-secondary">&larr; <?php esc_html_e( 'Back to Domains', 'pcip-prep' ); ?></button>
	</div>
</div>
