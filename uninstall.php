<?php
/**
 * PCIP Prep uninstall script.
 *
 * Only removes data if the user has explicitly opted in via the
 * "Delete all data on uninstall" setting. This prevents data loss
 * when updating the plugin through a delete-and-reinstall cycle.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

// Bail unless the user has explicitly opted in to data removal.
if ( ! get_option( 'pcip_prep_delete_data_on_uninstall', false ) ) {
	return;
}

global $wpdb;

// Delete all pcip_question posts and orphaned meta.
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'pcip_question'" );
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})" );

// Delete all pcip_issue_report posts and orphaned meta.
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'pcip_issue_report'" );
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})" );

// Delete taxonomy terms.
$terms = get_terms( array(
	'taxonomy'   => 'pcip_domain',
	'hide_empty' => false,
	'fields'     => 'ids',
) );

if ( ! is_wp_error( $terms ) ) {
	foreach ( $terms as $term_id ) {
		wp_delete_term( $term_id, 'pcip_domain' );
	}
}

// Drop custom tables.
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pcip_prep_results" );
$wpdb->query( "DROP TABLE IF EXISTS {$wpdb->prefix}pcip_prep_sessions" );

// Clean up all options.
delete_option( 'pcip_prep_db_version' );
delete_option( 'pcip_prep_delete_data_on_uninstall' );

// Clean up user meta.
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '_pcip_%'" );
