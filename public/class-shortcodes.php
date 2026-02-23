<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class PCIP_Prep_Shortcodes {

	public function init() {
		add_action( 'init', array( $this, 'register_shortcodes' ) );
	}

	public function register_shortcodes() {
		add_shortcode( 'pcip_flashcards', array( 'PCIP_Prep_Flashcards', 'render' ) );
		add_shortcode( 'pcip_prep', array( 'PCIP_Prep_Quiz', 'render' ) );
		add_shortcode( 'pcip_exam', array( 'PCIP_Prep_Exam', 'render' ) );
		add_shortcode( 'pcip_dashboard', array( 'PCIP_Prep_Dashboard', 'render' ) );
	}
}
