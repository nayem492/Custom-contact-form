<?php
if (!defined('ABSPATH')) {
    exit;
}

class SCF_Database {
    
    public static function create_table() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'scf_submissions';
        $charset_collate = $wpdb->get_charset_collate();
        
        $sql = "CREATE TABLE $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            name varchar(100) NOT NULL,
            email varchar(100) NOT NULL,
            subject varchar(200) NOT NULL,
            message text NOT NULL,
            created_at datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
            PRIMARY KEY  (id)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);
    }
    
    public static function save_submission($name, $email, $subject, $message) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'scf_submissions';
        
        return $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'subject' => $subject,
                'message' => $message
            ),
            array('%s', '%s', '%s', '%s')
        );
    }
}