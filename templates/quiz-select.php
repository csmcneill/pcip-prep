<?php if ( ! defined( 'ABSPATH' ) ) exit; ?>

<div id="pcip-prep-app" class="pcip-container">

	<!-- Domain Selection -->
	<div id="pcip-prep-domain-select" class="pcip-section">
		<h2><?php esc_html_e( 'Domain Quiz', 'pcip-prep' ); ?></h2>
		<p class="pcip-subtitle"><?php esc_html_e( 'Select a domain and quiz length. Questions are graded with immediate feedback.', 'pcip-prep' ); ?></p>

		<div class="pcip-domain-grid">
			<button class="pcip-domain-card" data-domain="domain-1">
				<span class="pcip-domain-number">1</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'PCI Essentials', 'pcip-prep' ); ?></span>
				<span class="pcip-domain-count" data-domain-count="domain-1"></span>
			</button>
			<button class="pcip-domain-card" data-domain="domain-2">
				<span class="pcip-domain-number">2</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'PCI DSS Overview', 'pcip-prep' ); ?></span>
				<span class="pcip-domain-count" data-domain-count="domain-2"></span>
			</button>
			<button class="pcip-domain-card pcip-domain-expandable" data-domain="domain-3">
				<span class="pcip-domain-number">3</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'PCI DSS Requirements', 'pcip-prep' ); ?></span>
				<span class="pcip-domain-count" data-domain-count="domain-3"></span>
				<span class="pcip-domain-expand-hint"><?php esc_html_e( 'Click to see requirements', 'pcip-prep' ); ?></span>
			</button>
			<div id="pcip-prep-requirements" class="pcip-requirements-grid" style="display:none">
				<?php for ( $i = 1; $i <= 12; $i++ ) : ?>
					<button class="pcip-req-card" data-requirement="requirement-<?php echo esc_attr( $i ); ?>" data-domain="domain-3">
						<span class="pcip-req-number"><?php echo esc_html( $i ); ?></span>
						<span class="pcip-req-name"><?php printf( esc_html__( 'Req %d', 'pcip-prep' ), $i ); ?></span>
						<span class="pcip-domain-count" data-domain-count="requirement-<?php echo esc_attr( $i ); ?>"></span>
					</button>
				<?php endfor; ?>
				<button class="pcip-req-card pcip-req-all" data-domain="domain-3">
					<span class="pcip-req-name"><?php esc_html_e( 'All Requirements', 'pcip-prep' ); ?></span>
					<span class="pcip-domain-count" data-domain-count="domain-3"></span>
				</button>
			</div>
			<button class="pcip-domain-card" data-domain="domain-4">
				<span class="pcip-domain-number">4</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'Reporting Fundamentals', 'pcip-prep' ); ?></span>
				<span class="pcip-domain-count" data-domain-count="domain-4"></span>
			</button>
			<button class="pcip-domain-card" data-domain="domain-5">
				<span class="pcip-domain-number">5</span>
				<span class="pcip-domain-name"><?php esc_html_e( 'SAQ Reporting', 'pcip-prep' ); ?></span>
				<span class="pcip-domain-count" data-domain-count="domain-5"></span>
			</button>
		</div>
	</div>

	<!-- Length Selection -->
	<div id="pcip-prep-length-select" class="pcip-section" style="display:none">
		<button id="pcip-prep-back-to-domains" class="pcip-btn pcip-btn-secondary">&larr; <?php esc_html_e( 'Back', 'pcip-prep' ); ?></button>
		<h3 id="pcip-prep-domain-title"></h3>
		<p><?php esc_html_e( 'How many questions?', 'pcip-prep' ); ?></p>
		<div id="pcip-prep-length-options" class="pcip-length-options"></div>
	</div>
</div>
