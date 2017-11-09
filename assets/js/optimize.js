jQuery(document).ready(function ($) {
    $('.optimize-now').on('click', function(e) {
        e.preventDefault();
        var attach_id = $(this).data('attach-id');
        $(this).parent().html('<div class="loader"></div>');
        var data = {
            action: 'ajax_manual_optimize',
            attach_id: attach_id
        };
        $.post( ajaxurl, data, function( response ) {
            $('.loader').parent().html('<p>' + response.saving_percent + ' (' + response.saving_size + ')</p>' +
                '<p>disk usage: ' + response.total_size + ' (' + response.count_images + ' images)</p>');
        });
    });
});