<?php 

if(!defined('ABSPATH')) {
    exit;
}

class SCF_Form_Handler{
    public function __construct() {

        // Register AJAX handlers

        // wp_ajax_... → for logged-in users
        add_action('wp_ajax_scf_submit_form', array($this, 'handle_submission'));

        // wp_ajax_nopriv_... → for logged-out visitors.
        add_action('wp_ajax_nopriv_scf_submit_form', array($this, 'handle_submission'));

    }


    public function handle_submission() {

        // Verify nonce for security
        if(!isset($_POST['nonce']) || !wp_verify_nonce( $_POST['nonce'], 'scf_form_nonce' )){
            wp_send_json_error(array('message' => 'Security check failed.'));
        }


        // Honeypot check for spam
        if (!empty($_POST['honeypot'])) {
            
            // Silently reject - don't tell the bot it failed
            wp_send_json_error(array('message' => 'Thank you for your submission bot.'));
        }


        // Get and sanitize input
        $name = sanitize_text_field( $_POST['name'] );
        $email = sanitize_email( $_POST['email'] );
        $subject = sanitize_text_field( $_POST['subject'] );
        $message = sanitize_textarea_field( $_POST['message'] );

        // Basic validation
        if(empty($name) || empty($email) || empty($subject) || empty($message)) {
            wp_send_json_error( array('message' => 'All field are required.'));
        }

        if(!is_email( $email )) {
            wp_send_json_error( array('message' => 'Invalid email address.'));
        }

        // Save to database
        $saved = SCF_Database::save_submission($name, $email, $subject, $message);

        if (!$saved) {
            wp_send_json_error(array('message' => 'Failed to save submission.'));
        }

        // Send email
        $email_sent = SCF_Email_Handler::send_email($name, $email, $subject, $message);

        if($email_sent) {
            wp_send_json_success(array('message' => 'Your message has been sent successfully!'));
        } else {
            wp_send_json_error(array('message' => 'Failed to send email. Please try again.'));
        }
    }
}



