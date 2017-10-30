jQuery(document).ready(function ($) {
    $('.optimize-now').on('click', function(e) {
        e.preventDefault();
        var attach_id = $(this).data('attach-id');
        var data = {
            action: 'ajax_manual_optimize',
            attach_id: attach_id
        };
        $.post( ajaxurl, data, function( response ) {
            console.log(response);
        });
    });
});