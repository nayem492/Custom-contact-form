<?php 

/**
 * Plugin Name: Contact Form
 * Plugin URI: https://abc.com/simple-contact-form
 * Description: contact form plugin. [Uninstalling will permanently delete all form submissions history and saved settings].
 * Version: 1.0.2
 * Author: Nayem Hossain
 * Author URI: https://abc.com
 * License: GPL v2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: simple-contact-form
 * Domain Path: /languages
 */

// Security check
if(!defined('ABSPATH')) {
    exit;
}


// paths defining
define('SCF_VERSION', '1.0.1');
define('SCF_PLUGIN_DIR', plugin_dir_path( __FILE__ ));
define('SCF_PLUGIN_URL', plugin_dir_url( __FILE__ ));


// Include required classes file
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
        'scf-public-style',                                 
        SCF_PLUGIN_URL . 'assets/css/public-style.css', 
        array(),                                            
        SCF_VERSION 
    );

    wp_enqueue_script(
        'scf-public-script',
        SCF_PLUGIN_URL . 'assets/js/public-script.js',
        array('jquery'),              
        SCF_VERSION,
        true                          
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

