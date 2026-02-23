<?php
/**
 * PCIP Prep uninstall script.
 *
 * Removes all plugin data: posts, meta, taxonomy terms, custom tables, and options.
 */

if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}

global $wpdb;

// Delete all pcip_question posts.
$wpdb->query( "DELETE FROM {$wpdb->posts} WHERE post_type = 'pcip_question'" );
$wpdb->query( "DELETE FROM {$wpdb->postmeta} WHERE post_id NOT IN (SELECT ID FROM {$wpdb->posts})" );

// Delete all pcip_issue_report posts.
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

// Clean up options.
delete_option( 'pcip_prep_db_version' );

// Clean up user meta for active sessions.
$wpdb->query( "DELETE FROM {$wpdb->usermeta} WHERE meta_key LIKE '_pcip_%'" );
