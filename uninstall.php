<?php
/**
 *
 * This file runs when the "Delete" button getting clicked on the wordpress plugin dashboard.
 * It removes all plugin data from the database.
 */

// security checked first
if (!defined('WP_UNINSTALL_PLUGIN')) {
    exit;
}

// Delete the database table
global $wpdb;
$table_name = $wpdb->prefix . 'scf_submissions';
$wpdb->query("DROP TABLE IF EXISTS $table_name");

// Delete all plugin options
delete_option('scf_form_style');
delete_option('scf_recipient_email');
delete_option('scf_success_message'); // If you added this

// Delete any transients (temporary data)
delete_transient('scf_admin_notice');

// Log the uninstall for debugging
if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Simple Contact Form plugin uninstalled and cleaned up.');
}
