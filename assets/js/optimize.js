jQuery(document).ready(function ($) {
    $('.optimize-now').on('click', function(e) {
        e.preventDefault();
        var attach_id = $(this).data('attach-id');
        $(this).parent().html('<div class="loader"></div>');
        var data = {
            action: 'manual_optimize',
            attach_id: attach_id
        };
        $.post( ajaxurl, data, function( response ) {
            $('.loader').parent().html('<p>' + response.saving_percent + '% saved (' + response.saving_size + ')</p>' +
                '<p>disk usage: ' + response.total_size + ' (' + response.count_images + ' images)</p>');
        });
    });
});
jQuery(document).ready(function ($) {
    $('.optimize-now-meta').on('click', function(e) {
        e.preventDefault();
        var attach_id = $(this).data('attach-id');
        $(this).parent().append('<div class="loader"></div>');
        $('td .optimize-now-meta').remove();
        var data = {
            action: 'manual_optimize',
            attach_id: attach_id
        };
        $.post( ajaxurl, data, function( response ) {
            $(".loader").remove();
            $('.optimize-stats').html('<td><strong>Image Optimization</strong></td>' +
                '<td><p>' + response.saving_percent + ' saved (' + response.saving_size + ')</p>' +
                '<p>disk usage: ' + response.total_size + ' (' + response.count_images + ' images)</p></td>');
        });
    });
});