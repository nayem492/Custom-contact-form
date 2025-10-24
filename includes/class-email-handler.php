<?php
if (!defined('ABSPATH')) {
    exit;
}

class SCF_Email_Handler{
    public static function send_email($name, $email, $subject, $message) {

        // Use saved recipient email(by plugin setting page) or fallback to admin email
        $admin_email = get_option('scf_recipient_email', get_option( 'admin_email' ));

        // Set email headers
        $headers = array(
            'Content-Type: text/html; charset=UTF-8',
            'From: ' . get_bloginfo('name') . ' <' . $admin_email . '>',
            'Reply-To: ' . $name . ' <' . $email . '>'
        );

        // Build email subject
        $email_subject = '[' . get_bloginfo('name') . '] ' . $subject;

        // Build email body
        $email_body = '<html><body>';
        $email_body .= '<h2>New Contact Form Submission</h2>';
        $email_body .= '<p><strong>Name:</strong> ' . esc_html($name) . '</p>';
        $email_body .= '<p><strong>Email:</strong> ' . esc_html($email) . '</p>';
        $email_body .= '<p><strong>Subject:</strong> ' . esc_html($subject) . '</p>';
        $email_body .= '<p><strong>Message:</strong></p>';
        $email_body .= '<p>' . nl2br(esc_html($message)) . '</p>';
        $email_body .= '</body></html>';

        // Send email
        return wp_mail($admin_email, $email_subject, $email_body, $headers);
    }
}