
<?php 
if(!defined('ABSPATH')){
    exit;
}

// Get style class
$style_class = get_query_var('scf_style_class', 'scf-style-classic');
?>

<div class="scf-form-wrapper <?php echo esc_attr( $style_class ); ?>">
    <form id="scf-contact-form" class="scf-contact-form">
        
        <!-- MESSAGE CONTAINER -->
        <div class="scf-form-response"></div>

        <!-- Form Stucture -->
        <div class="scf-form-group">
            <label for="scf-name">Name *</label>
            <input type="text" id="scf-name" name="name" required>
        </div>
        
        <div class="scf-form-group">
            <label for="scf-email">Email *</label>
            <input type="email" id="scf-email" name="email" required>
        </div>
        
        <!-- HONEYPOT FIELD -->
        <div class="scf-honeypot">
            <label for="scf-website">Website (leave blank)</label>
            <input type="text" id="scf-website" name="website" tabindex="-1" autocomplete="off">
        </div>

        <div class="scf-form-group">
            <label for="scf-subject">Subject *</label>
            <input type="text" id="scf-subject" name="subject" required>
        </div>
        
        <div class="scf-form-group">
            <label for="scf-message">Message *</label>
            <textarea id="scf-message" name="message" rows="6" required></textarea>
        </div>
        
        <div class="scf-form-group">
            <button type="submit" class="scf-submit-btn">Send Message</button>
        </div>
        
    </form>
</div>

