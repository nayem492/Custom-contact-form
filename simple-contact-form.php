<?php 

/**
 * Plugin Name: Simple Contact Form
 * Plugin URI: https://example.com/simple-contact-form
 * Description: A simple and elegant contact form plugin. Note: Uninstalling will permanently delete all form submissions and settings.
 * Version: 1.0.1
 * Author: Nayem Hossain
 * Author URI: https://example.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-contact-form
 * Domain Path: /languages
 */

// Security: Exit if accessed directly
if(!defined('ABSPATH')) {
    exit;
}


// Define constants for paths
define('SCF_VERSION', '1.0.1');
define('SCF_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('SCF_PLUGIN_URL', plugin_dir_url( __FILE__ ));


// Include required classes
require_once SCF_PLUGIN_DIR . 'public/class-shortcode.php';
require_once SCF_PLUGIN_DIR . 'includes/class-form-handler.php';
require_once SCF_PLUGIN_DIR . 'includes/class-email-handler.php';
require_once SCF_PLUGIN_DIR . 'includes/class-database.php';
require_once SCF_PLUGIN_DIR . 'includes/class-admin-settings.php';

// Run when plugin is activated
register_activation_hook(__FILE__, array('SCF_Database', 'create_table'));



// Enqueue styles and scripts
function scf_enqueue_assets() {
    wp_enqueue_style( 
        'scf-public-style',                                 // Handle (unique name)
        SCF_PLUGIN_URL . 'assets/css/public-style.css', 
        array(),                                            // Dependencies
        SCF_VERSION 
    );

    // Enqueue JavaScript
    wp_enqueue_script(
        'scf-public-script',
        SCF_PLUGIN_URL . 'assets/js/public-script.js',
        array('jquery'),              // Depends on jQuery
        SCF_VERSION,
        true                          // Load in footer
    );
    
    // Pass data to JavaScript
    wp_localize_script('scf-public-script', 'scfAjax', array(
        'ajaxurl' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('scf_form_nonce')
    ));
}
add_action( 'wp_enqueue_scripts', 'scf_enqueue_assets');




// Initialize shortcode
function scf_init_plugin() {
    new SCF_Shortcode();
    new SCF_Form_Handler();
    new SCF_Admin_Settings();
}
add_action('plugins_loaded', 'scf_init_plugin');

