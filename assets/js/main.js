jQuery(document).ready(function ($) {
   checkboxChecker();
   $('input:checkbox').on('click', function() {
      // in the handler, 'this' refers to the box clicked on
      var $box = $(this);
      var $selector_block = '#' + $box.val();
      if ($box.is(":checked")) {
         // the name of the box is retrieved using the .attr() method
         // as it is assumed and expected to be immutable
         var group = "input:checkbox[name='" + $box.attr("name") + "']";
         // the checked state of the group/box on the other hand will change
         // and the current value is retrieved using .prop() method
         $(group).prop("checked", false);
         $box.prop("checked", true);
         $($selector_block).css('display', 'block');
      } else {
         $box.prop("checked", false);
         $($selector_block).css('display', 'none');
      }
   });
   function checkboxChecker(){
      var $box = $('input:checkbox');
      var $selector_block = '#' + $box.val();
      if($box.is(":checked")) {
         $($selector_block).css('display', 'block');
      }
   }
});