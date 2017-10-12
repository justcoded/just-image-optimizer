jQuery(document).ready(function ($) {
   checkboxChecker();
   $('input:checkbox').on('click', function() {
      var $box = $(this);
      var $selector_block = '#' + $box.val();
      if ($box.is(":checked")) {
         var group = "input:checkbox[name='" + $box.attr("name") + "']";
         $(group).prop("checked", false);
         $box.prop("checked", true);
         $($selector_block).css('display', 'block');
      } else {
         $box.prop("checked", false);
         $($selector_block).css('display', 'none');
      }
   });
   $('#api_connect').on('click', function(e) {
      e.preventDefault();
      var $api_key = $('#_just_img_opt_api_key').val();
      var data = {
         action: 'ajax_check_api',
         api_key: $api_key
      };
      $.post( ajaxurl, data, function( response ) {
         console.log(response);
         if( response == '1') {
            $('.api_status').html('<p>OK</p>');
            $('#submit-connect').prop('disabled', false);
         } else {
            $('.notice-error').html('API key is invalid');
         }
      });

   });
   function checkboxChecker(){
      var $box = $('input:checkbox');
      var $selector_block = '#' + $box.val();
      if($box.is(":checked")) {
         $($selector_block).css('display', 'block');
      }
   }
});