jQuery(document).ready(function($) {
    // Color pickers
    $('.chatbot-color-picker').wpColorPicker();

    // Media uploader for logo
    var mediaUploader = null;

    $('.upload-logo-button').on('click', function(e) {
        e.preventDefault();

        if (mediaUploader) {
            mediaUploader.open();
            return;
        }

        mediaUploader = wp.media({
            title: 'Choose Chatbot Logo',
            button: { text: 'Choose Logo' },
            multiple: false
        });

        mediaUploader.on('select', function() {
            var attachment = mediaUploader.state().get('selection').first().toJSON();
            $('#chatbot_logo').val(attachment.url);
            $('.chatbot-logo-preview').html('<img src="'+attachment.url+'" style="max-height:60px;width:auto;">');
            $('.remove-logo-button').show();
        });

        mediaUploader.open();
    });

    $('.remove-logo-button').on('click', function(e) {
        e.preventDefault();
        $('#chatbot_logo').val('');
        $('.chatbot-logo-preview').empty();
        $(this).hide();
    });
});
