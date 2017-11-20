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
      var $api_key = $('#api_key').val();
      var $service = $('#service').val();
      var data = {
         action: 'connect_api',
         api_key: $api_key,
         service: $service
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
   $('#find_api').on('click', function(e) {
      e.preventDefault();
      var x = document.getElementById("instructions-api");
      if (x.style.display === "none") {
         x.style.display = "block";
      } else {
         x.style.display = "none";
      }

   });
});