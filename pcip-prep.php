<?php
/**
 * Plugin Name: PCI Professional Preparation Plugin
 * Description: PCIP certification prep tool (P4) with flashcards, multiple-choice quizzes, and a full exam simulator.
 * Version:     1.1.0
 * Author:      Chris McNeill
 * Author URI:  https://csmcneill.com
 * Text Domain: pcip-prep
 * License:     GPL-2.0-or-later
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PCIP_PREP_VERSION', '1.1.0' );
define( 'PCIP_PREP_PLUGIN_FILE', __FILE__ );
define( 'PCIP_PREP_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
define( 'PCIP_PREP_PLUGIN_URL', plugin_dir_url( __FILE__ ) );
define( 'PCIP_PREP_DB_VERSION', '1.0.0' );

// Load dependencies.
require_once PCIP_PREP_PLUGIN_DIR . 'includes/class-pcip-prep.php';
require_once PCIP_PREP_PLUGIN_DIR . 'includes/class-post-types.php';
require_once PCIP_PREP_PLUGIN_DIR . 'includes/class-database.php';
require_once PCIP_PREP_PLUGIN_DIR . 'includes/class-csv-handler.php';
require_once PCIP_PREP_PLUGIN_DIR . 'includes/class-rest-api.php';
require_once PCIP_PREP_PLUGIN_DIR . 'includes/class-issue-reporter.php';
require_once PCIP_PREP_PLUGIN_DIR . 'admin/class-admin.php';
require_once PCIP_PREP_PLUGIN_DIR . 'admin/class-meta-boxes.php';
require_once PCIP_PREP_PLUGIN_DIR . 'admin/class-csv-admin.php';
require_once PCIP_PREP_PLUGIN_DIR . 'public/class-shortcodes.php';
require_once PCIP_PREP_PLUGIN_DIR . 'public/class-flashcards.php';
require_once PCIP_PREP_PLUGIN_DIR . 'public/class-quiz.php';
require_once PCIP_PREP_PLUGIN_DIR . 'public/class-exam.php';
require_once PCIP_PREP_PLUGIN_DIR . 'public/class-dashboard.php';

// Activation / deactivation.
register_activation_hook( __FILE__, array( 'PCIP_Prep_Database', 'activate' ) );
register_deactivation_hook( __FILE__, array( 'PCIP_Prep_Database', 'deactivate' ) );

// Boot the plugin.
PCIP_Prep::instance();
