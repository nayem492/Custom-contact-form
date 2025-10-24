jQuery(document).ready(function ($) {

    $('#scf-contact-form').on('submit', function (e) {
        e.preventDefault();

        var form = $(this);
        var submitBtn = form.find('.scf-submit-btn');
        var responseDiv = form.find('.scf-form-response');

        // Clear previous messages
        responseDiv.removeClass('success error loading').hide();

        // Show loading state
        submitBtn.prop('disabled', true).addClass('loading');
        var originalBtnText = submitBtn.text();
        submitBtn.text('Sending...');

        // Send AJAX request
        $.ajax({
            url: scfAjax.ajaxurl,
            type: 'POST',
            data: {
                action: 'scf_submit_form',
                nonce: scfAjax.nonce,
                name: $('#scf-name').val(),
                email: $('#scf-email').val(),
                subject: $('#scf-subject').val(),
                message: $('#scf-message').val(),
                honeypot: $('#scf-website').val()       // For spam protection
            },
            success: function (response) {
                if (response.success) {
                    // Show success message
                    responseDiv
                        .removeClass('error loading')
                        .addClass('success')
                        .html(response.data.message)
                        .show();

                    // Reset form
                    form[0].reset();

                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: responseDiv.offset().top - 100
                    }, 500);

                } else {
                    // Show error message
                    responseDiv
                        .removeClass('success loading')
                        .addClass('error')
                        .html(response.data.message)
                        .show();
                }

                // Reset button
                submitBtn.prop('disabled', false).removeClass('loading').text(originalBtnText);
            },
            error: function () {
                // Show error message
                responseDiv
                    .removeClass('success loading')
                    .addClass('error')
                    .html('An unexpected error occurred. Please try again later.')
                    .show();

                // Reset button
                submitBtn.prop('disabled', false).removeClass('loading').text(originalBtnText);
            }
        });
    });

});