<?php
if (!defined('ABSPATH')) {
    exit;
}


class SCF_Admin_Settings {
    public function __construct() {

        add_action('admin_menu', array($this, 'add_admin_menu'));

        // Hook to handle actions early
        add_action('admin_init', array($this, 'handle_admin_actions'));

        add_action('admin_init', array($this, 'register_settings'));
    }
    

    // Register plugin settings
    public function register_settings() {
       register_setting('scf_settings_group', 'scf_form_style', array(
            'type' => 'string',
            'default' => 'classic',
            'sanitize_callback' => array($this, 'sanitize_form_style')
        ));
    
        register_setting('scf_settings_group', 'scf_recipient_email', array(
            'type' => 'string',
            'default' => get_option('admin_email'),
            'sanitize_callback' => 'sanitize_email'
        ));
    }

    // Sanitize form style input
    public function sanitize_form_style($value) {

        $valid_styles = array('classic', 'modern', 'minimal');
        return in_array($value, $valid_styles) ? $value : 'classic';
    }



    // Handle deletions before any output
    public function handle_admin_actions() {

        // Only run on our submissions page
        if (!isset($_GET['page']) || $_GET['page'] !== 'scf-submissions') {
            return;
        }
        
        global $wpdb;
        $table_name = $wpdb->prefix . 'scf_submissions';
        

        // Handle CSV Export
        if (isset($_GET['action']) && $_GET['action'] === 'export_csv' && isset($_GET['_wpnonce'])) {
        
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'scf_export_csv')) {
                wp_die('Security check failed.');
            }
            
            // Get all submissions
            $submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC", ARRAY_A);
            
            if (empty($submissions)) {
                set_transient('scf_admin_notice', 'error|No submissions to export.', 5);
                wp_redirect(admin_url('admin.php?page=scf-submissions'));
                exit;
            }
            
            // Set headers for CSV download
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=contact-form-submissions-' . date('Y-m-d') . '.csv');
            header('Pragma: no-cache');
            header('Expires: 0');
            
            // Open output stream
            $output = fopen('php://output', 'w');
            
            // Add BOM to fix UTF-8 in Excel
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Add column headers
            fputcsv($output, array('ID', 'Name', 'Email', 'Subject', 'Message', 'Date'));
            
            // Add data rows
            foreach ($submissions as $submission) {
                fputcsv($output, array(
                    $submission['id'],
                    $submission['name'],
                    $submission['email'],
                    $submission['subject'],
                    $submission['message'],
                    $submission['created_at']
                ));
            }
            
            fclose($output);
            exit;
        }


        // Handle single delete
        if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id']) && isset($_GET['_wpnonce'])) {
            
            // Verify nonce
            if (!wp_verify_nonce($_GET['_wpnonce'], 'scf_delete_submission_' . $_GET['id'])) {
                wp_die('Security check failed.');
            }
            
            // Delete the submission
            $id = intval($_GET['id']);
            $deleted = $wpdb->delete($table_name, array('id' => $id), array('%d'));
            
            // Redirect with message
            if ($deleted) {
                set_transient('scf_admin_notice', 'success|Submission deleted successfully.', 5);
            } else {
                set_transient('scf_admin_notice', 'error|Failed to delete submission.', 5);
            }
            
            wp_redirect(admin_url('admin.php?page=scf-submissions'));
            exit;
        }
    }
    

    public function add_admin_menu() {
        
        // Add main menu
        add_menu_page(
            'Simple Contact Form',          // Page title
            'Contact Form',                 // Menu title
            'manage_options',               // Capability
            'simple-contact-form',          // Menu slug
            array($this, 'settings_page'),  // Callback
            'dashicons-email',              // Icon
            30                              // Position
        );
        
        // Add submenu for submissions
        add_submenu_page(
            'simple-contact-form',
            'Submissions',
            'Submissions History',
            'manage_options',
            'scf-submissions',
            array($this, 'submissions_page')
        );
    }
    
    public function settings_page() {
        
        // Handle form submission
        if (isset($_POST['scf_save_settings'])) {
            check_admin_referer('scf_settings_nonce');
            
            update_option('scf_form_style', sanitize_text_field($_POST['scf_form_style']));
            update_option('scf_recipient_email', sanitize_email($_POST['scf_recipient_email']));
            
            echo '<div class="notice notice-success is-dismissible"><p>Settings saved successfully!</p></div>';
        }
        
        $current_style = get_option('scf_form_style', 'classic');
        $recipient_email = get_option('scf_recipient_email', get_option('admin_email'));
        ?>
        <div class="wrap">
            <h1>Simple Contact Form Settings</h1>
            
            <div style="background: #fff; padding: 20px; margin: 10px 0; border: 1px solid #ccd0d4; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
                <h2>Shortcode</h2>
                <p>Use this shortcode to display the contact form on any page or post:</p>
                <code style="font-size: 14px; padding: 10px; background: #f0f0f1; display: inline-block;">[simple_contact_form]</code>
            </div>
            
            <form method="post" action="">
                <?php wp_nonce_field('scf_settings_nonce'); ?>
                
                <table class="form-table">
                    <tr>
                        <th scope="row">
                            <label>Form Style</label>
                        </th>
                        <td>
                            <fieldset>
                                <legend class="screen-reader-text"><span>Form Style</span></legend>
                                
                                <label style="display: block; margin-bottom: 15px;">
                                    <input type="radio" name="scf_form_style" value="classic" <?php checked($current_style, 'classic'); ?>>
                                    <strong>Classic Style</strong>
                                    <p class="description" style="margin-left: 25px;">Traditional form with clean lines and subtle shadows</p>
                                </label>
                                
                                <label style="display: block; margin-bottom: 15px;">
                                    <input type="radio" name="scf_form_style" value="modern" <?php checked($current_style, 'modern'); ?>>
                                    <strong>Modern Style</strong>
                                    <p class="description" style="margin-left: 25px;">Bold colors with floating labels and smooth animations</p>
                                </label>
                                
                                <label style="display: block; margin-bottom: 15px;">
                                    <input type="radio" name="scf_form_style" value="minimal" <?php checked($current_style, 'minimal'); ?>>
                                    <strong>Minimal Style</strong>
                                    <p class="description" style="margin-left: 25px;">Clean and simple with bottom borders only</p>
                                </label>
                            </fieldset>
                        </td>
                    </tr>
                    
                    <tr>
                        <th scope="row">
                            <label for="scf_recipient_email">Recipient Email</label>
                        </th>
                        <td>
                            <input type="email" 
                                id="scf_recipient_email" 
                                name="scf_recipient_email" 
                                value="<?php echo esc_attr($recipient_email); ?>" 
                                class="regular-text">
                            <p class="description">Email address where form submissions will be sent.</p>
                        </td>
                    </tr>
                </table>
                
                <?php submit_button('Save Settings', 'primary', 'scf_save_settings'); ?>
            </form>
            
            <!-- Style Preview -->
            <div style="margin-top: 40px;">
                <h2>Style Preview</h2>
                <div style="background: #f9f9f9; padding: 20px; border: 1px solid #ddd;">
                    <p><strong>Current Style:</strong> <?php echo ucfirst($current_style); ?></p>
                    <p>Submit the form on your page to see the selected style in action.</p>
                </div>
            </div>
        </div>
        <?php
    }
    

    public function submissions_page() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'scf_submissions';
        
        // Handle bulk delete (this stays here because it's from POST)
        if (isset($_POST['action']) && $_POST['action'] === 'bulk_delete' && !empty($_POST['submissions'])) {
            
            // Verify nonce
            check_admin_referer('scf_bulk_delete');
            
            $count = 0;
            foreach ($_POST['submissions'] as $id) {
                $id = intval($id);
                if ($wpdb->delete($table_name, array('id' => $id), array('%d'))) {
                    $count++;
                }
            }
            
            if ($count > 0) {
                echo '<div class="notice notice-success is-dismissible"><p>' . $count . ' submission(s) deleted successfully.</p></div>';
            } else {
                echo '<div class="notice notice-error is-dismissible"><p>No submissions were deleted.</p></div>';
            }
        }
        
        // Show transient message for single delete
        if ($notice = get_transient('scf_admin_notice')) {
            delete_transient('scf_admin_notice');
            list($type, $message) = explode('|', $notice);
            echo '<div class="notice notice-' . esc_attr($type) . ' is-dismissible"><p>' . esc_html($message) . '</p></div>';
        }
        

        // Get all submissions
        $submissions = $wpdb->get_results("SELECT * FROM $table_name ORDER BY created_at DESC");
        
        ?>
        <div class="wrap">
            <h1>Form Submissions History</h1>
            
             <!-- CSV Export Button -->
            <div style="margin: 15px 0;">
                <a href="<?php echo wp_nonce_url(admin_url('admin.php?page=scf-submissions&action=export_csv'), 'scf_export_csv'); ?>" 
                class="button button-primary">
                    <span class="dashicons dashicons-download" style="vertical-align: middle;"></span>
                    Export to CSV
                </a>
                <span style="margin-left: 10px; color: #666;">
                    Total Submissions: <strong><?php echo count($submissions); ?></strong>
                </span>
            </div>


            <form method="post">
                <?php wp_nonce_field('scf_bulk_delete'); ?>
                
                <div class="tablenav top">
                    <div class="alignleft actions bulkactions">
                        <select name="action">
                            <option value="">Bulk Actions</option>
                            <option value="bulk_delete">Delete</option>
                        </select>
                        <input type="submit" class="button action" value="Apply">
                    </div>
                </div>
                
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <td class="check-column">
                                <input type="checkbox" id="cb-select-all">
                            </td>
                            <th>ID</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Subject</th>
                            <th>Message</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($submissions)): ?>
                            <tr>
                                <td colspan="8">No submissions yet.</td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($submissions as $submission): ?>
                                <tr>
                                    <th class="check-column">
                                        <input type="checkbox" name="submissions[]" value="<?php echo $submission->id; ?>">
                                    </th>
                                    <td><?php echo esc_html($submission->id); ?></td>
                                    <td><?php echo esc_html($submission->name); ?></td>
                                    <td>
                                        <a href="mailto:<?php echo esc_attr($submission->email); ?>">
                                            <?php echo esc_html($submission->email); ?>
                                        </a>
                                    </td>
                                    <td><?php echo esc_html($submission->subject); ?></td>
                                    <td>
                                        <details>
                                            <summary><?php echo esc_html(wp_trim_words($submission->message, 10)); ?></summary>
                                            <p style="margin-top: 10px;"><?php echo nl2br(esc_html($submission->message)); ?></p>
                                        </details>
                                    </td>
                                    <td><?php echo esc_html($submission->created_at); ?></td>
                                    <td>
                                        <a href="<?php echo wp_nonce_url(
                                            admin_url('admin.php?page=scf-submissions&action=delete&id=' . $submission->id),
                                            'scf_delete_submission_' . $submission->id
                                        ); ?>" 
                                           class="button button-small button-link-delete"
                                           onclick="return confirm('Are you sure you want to delete this submission?')">
                                            Delete
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </form>
        </div>
        
        <script>
        jQuery(document).ready(function($) {
            $('#cb-select-all').on('change', function() {
                $('input[name="submissions[]"]').prop('checked', this.checked);
            });
        });
        </script>
        <?php
    }
}