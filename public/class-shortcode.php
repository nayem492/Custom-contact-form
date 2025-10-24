<?php

if(!defined('ABSPATH')) {
    exit;
}

class SCF_Shortcode{
    public function __construct() {

        // Register shortcode when class is instantiated
        add_shortcode( 'simple_contact_form', array($this, 'render_form') );
    }


    public function render_form( $atts ) {

        // Get selected style
        $style = get_option('scf_form_style', 'classic');

        // Set style class
        set_query_var('scf_style_class', 'scf-style-' . $style);

        ob_start();
        // Include the template file
        include SCF_PLUGIN_DIR . 'public/form-template.php';
        // Return the buffered content
        return ob_get_clean();
    }
}

