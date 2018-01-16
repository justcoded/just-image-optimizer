jQuery(document).ready(function ($) {
    checkboxChecker();
    $("#check_all_size").click(function(){
        var $box = $('input:checkbox[name="image_sizes_all"]');
        $('.image_sizes_set input:checkbox').not(this).prop('checked', this.checked);
        if ($box.is(":checked")) {
            $('.size_checked').css('display', 'none');
        } else {
            $('.size_checked').css('display', 'block');
        }
    });
    function checkboxChecker(){
        var $box = $('input:checkbox[name="image_sizes_all"]');
        if ($box.is(":checked")) {
            $('.size_checked').css('display', 'none');
            $('input:checkbox[name="image_sizes[]"]').attr('checked','checked');
        } else {
            $('.size_checked').css('display', 'block');
        }
    }
});