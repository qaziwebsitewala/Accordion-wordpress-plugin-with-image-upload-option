jQuery(document).ready(function ($) {
    // Accordion functionality
    $('.accordion-header').click(function () {
        // Toggle the 'accordion-open' class on the accordion item
        $(this).parent('.accordion-item').toggleClass('accordion-open');

        // Toggle the plus and minus icons
        var icon = $(this).find('.accordion-toggle');
        icon.text(icon.text() == '+' ? '-' : '+');
    });

    // Image upload functionality
    $(document).on('click', '.upload-image-button', function (e) {
        e.preventDefault();

        var fieldId = $(this).prev('input').attr('id');

        // Open the media uploader
        var customUploader = wp.media({
            title: 'Choose Image',
            button: { text: 'Choose Image' },
            multiple: false
        });

        customUploader.on('select', function () {
            var attachment = customUploader.state().get('selection').first().toJSON();
            $('#' + fieldId).val(attachment.url);
            $('#' + fieldId).next('.image-preview').html('<img src="' + attachment.url + '" alt="Image Preview">');
        });

        customUploader.open();
    });
});
